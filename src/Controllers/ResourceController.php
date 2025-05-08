<?php

namespace App\Controllers;

use App\Models\Resource;
use App\Core\Response;
use App\Core\Request;
use App\Core\Auth;
use App\Core\ResourceManager;
use App\Core\MemoryManager;
use App\Core\SQLFileProcessor;
use App\Core\FileValidator;

class ResourceController extends BaseController {
    protected $resource;
    protected $resourceManager;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->resource = new Resource();
        $this->resourceManager = ResourceManager::getInstance();
    }

    public function index(Request $request) {
        error_log('ResourceController::index 진입');
        $keyword = $request->get('keyword', '');
        $selected_tags = $request->get('tags', []);
        $sort = $request->get('sort', 'created_desc');
        $type = $request->get('type', '');
        $is_public = $request->get('is_public', null);
        $page = max(1, (int)$request->get('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $params = [
            'keyword' => $keyword,
            'tag_ids' => $selected_tags,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'type' => $type,
            'is_public' => $is_public,
        ];

        try {
            $resourceModel = new \App\Models\Resource();
            $resources = $resourceModel->search($params);
            $total_count = $resourceModel->count($params);
            $total_pages = ceil($total_count / $limit);
            $all_tags = $resourceModel->getAllTags();
            return $this->view('resources/list', [
                'resources' => $resources,
                'all_tags' => $all_tags,
                'selected_tags' => $selected_tags,
                'keyword' => $keyword,
                'sort' => $sort,
                'type' => $type,
                'is_public' => $is_public,
                'current_page' => $page,
                'total_pages' => $total_pages,
                'user' => $this->user,
                'error' => null,
                'types' => \App\Models\Resource::getTypes(),
            ]);
        } catch (\Exception $e) {
            error_log("Error in ResourceController::index: " . $e->getMessage());
            error_log($e->getTraceAsString());
            return $this->view('resources/list', [
                'resources' => [],
                'all_tags' => [],
                'selected_tags' => $selected_tags,
                'keyword' => $keyword,
                'sort' => $sort,
                'type' => $type,
                'is_public' => $is_public,
                'current_page' => $page,
                'total_pages' => 1,
                'user' => $this->user,
                'error' => $e->getMessage(),
                'types' => \App\Models\Resource::getTypes(),
            ]);
        }
    }

    /**
     * 리소스 상세 보기
     */
    public function show(Request $request, $id)
    {
        try {
            $lang = $_SESSION['lang'] ?? 'ko';
            $resource = $this->resource->findById($id, $lang);
            if (!$resource) {
                throw new \Exception("리소스를 찾을 수 없습니다.", 404);
            }
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json($resource);
            }
            return $this->view('resources/show', [
                'resource' => $resource,
                'title' => $resource['title']
            ]);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json([
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }
            return $this->view('errors/500', [
                'error' => $e->getMessage(),
                'title' => '500 Internal Server Error'
            ], 500);
        }
    }

    public function store(Request $request) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }
            $data = $this->validateResourceData($request);
            if (isset($data['error'])) {
                return $this->response->json(['error' => $data['error']], 422);
            }
            $data['user_id'] = $user['id'];
            $resource = $this->resource->create($data);
            return $this->response->json([
                'message' => '리소스가 생성되었습니다.',
                'data' => ['id' => $resource['id'] ?? null]
            ], 201);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }
            $resource = $this->resource->findById($id);
            if (!$resource) {
                return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
            }
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                return $this->response->json(['error' => '수정 권한이 없습니다.'], 403);
            }
            $data = $this->validateResourceData($request, true);
            if (isset($data['error'])) {
                return $this->response->json(['error' => $data['error']], 422);
            }
            $this->resource->update($id, $data);
            return $this->response->json(['message' => '리소스가 수정되었습니다.']);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $resource = $this->resource->findById($id);
            if (!$resource) {
                return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
            }

            // 권한 확인
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                return $this->response->json(['error' => '삭제 권한이 없습니다.'], 403);
            }

            $this->resource->delete($id);

            return $this->response->json(['message' => '리소스가 삭제되었습니다.']);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function search() {
        // Request 객체가 없으므로 직접 $_GET 사용
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $tagIds = isset($_GET['tags']) ? (array)$_GET['tags'] : [];
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_desc';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $params = [
            'keyword' => $keyword,
            'tag_ids' => $tagIds,
            'sort' => $sort,
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset
        ];

        $resourceModel = new \App\Models\Resource();
        $resources = $resourceModel->search($params);
        $total_count = count($resources); // 실제로는 전체 개수 쿼리 필요
        $total_pages = ceil($total_count / $limit);
        $current_page = $page;

        // 태그 목록 준비
        $tagModel = new \App\Models\Tag();
        $all_tags = $tagModel->getAllTags();
        $selected_tag_ids = $tagIds;

        // 뷰에서 사용할 변수 추출
        extract([
            'resources' => $resources,
            'all_tags' => $all_tags,
            'keyword' => $keyword,
            'selected_tag_ids' => $selected_tag_ids,
            'sort' => $sort,
            'filter' => $filter,
            'total_count' => $total_count,
            'total_pages' => $total_pages,
            'current_page' => $current_page
        ]);

        require __DIR__ . '/../View/resource/search.php';
    }

    public function toggleVisibility(Request $request, $id) {
        // ... existing code ...
    }

    public function tags() {
        $tagModel = new \App\Models\Tag();
        $tags = $tagModel->getPopularTags(1000); // 모든 태그와 리소스 개수
        // 리소스가 1개 이상인 태그만 필터링
        $tags = array_filter($tags, function($tag) {
            return ($tag['resource_count'] ?? 0) > 0;
        });
        return $this->view('resources/tags', [
            'tags' => $tags
        ]);
    }

    private function validateResourceData(Request $request, $isUpdate = false) {
        $data = [];
        $errors = [];
        $lang = $_SESSION['lang'] ?? 'ko';
        $translations = [];

        // 다국어 입력 처리 (여기서는 기본적으로 'ko'만, 확장 가능)
            $title = trim($request->get('title'));
        $content = trim($request->get('content'));
        $description = trim($request->get('description'));
            if (empty($title)) {
                $errors[] = '제목은 필수입니다.';
        }
            if (empty($content)) {
                $errors[] = '내용은 필수입니다.';
        }
            if (empty($description)) {
                $errors[] = '설명은 필수입니다.';
            }
        $translations[$lang] = [
            'title' => $title,
            'content' => $content,
            'description' => $description
        ];
        $data['translations'] = $translations;

        if ($request->has('visibility')) {
            $visibility = $request->get('visibility');
            if (!in_array($visibility, ['public', 'private'])) {
                $errors[] = '공개 여부는 public 또는 private만 가능합니다.';
            }
            $data['visibility'] = $visibility;
        }
        if ($request->has('status')) {
            $status = $request->get('status');
            if (!in_array($status, ['draft', 'published'])) {
                $errors[] = '상태는 draft 또는 published만 가능합니다.';
            }
            $data['status'] = $status;
        }
        if ($request->has('tags')) {
            $tags = $request->get('tags');
            if (!is_array($tags)) {
                $errors[] = '태그는 배열 형식이어야 합니다.';
            }
            $data['tags'] = $tags;
        }
        if (!empty($errors)) {
            return ['error' => implode(' ', $errors)];
        }
        return $data;
    }
} 