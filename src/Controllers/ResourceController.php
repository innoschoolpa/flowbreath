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

    public function index() {
        try {
            $lang = $_SESSION['lang'] ?? 'ko';
            $keyword = $_GET['keyword'] ?? '';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $resourceModel = new \App\Models\Resource($this->pdo);
            $resources = $resourceModel->searchWithLang([
                'lang' => $lang,
                'keyword' => $keyword,
                'limit' => $limit,
                'offset' => $offset
            ]);

            // 뷰에 필요한 변수들
            $viewData = [
                'resources' => $resources,
                'keyword' => $keyword,
                'current_page' => $page,
                'total_pages' => ceil(count($resources) / $limit),
                'lang' => $lang
            ];

            // 뷰 렌더링
            $viewPath = dirname(__DIR__) . '/View/resources/index.php';
            if (!file_exists($viewPath)) {
                throw new \Exception('View file not found: ' . $viewPath);
            }
            
            extract($viewData);
            require $viewPath;
        } catch (\Exception $e) {
            error_log('Error in ResourceController@index: ' . $e->getMessage());
            // 에러 페이지 표시 또는 500 에러 반환
            http_response_code(500);
            echo 'Internal Server Error';
        }
    }

    /**
     * 리소스 상세 보기
     */
    public function show(Request $request, $id)
    {
        try {
            $resource = $this->resource->findById($id);
            if (!$resource) {
                throw new \Exception("리소스를 찾을 수 없습니다.", 404);
            }

            // JSON 요청인 경우 JSON 응답
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json($resource);
            }

            // 일반 요청인 경우 HTML 뷰 렌더링
            return $this->view('resources/show', [
                'resource' => $resource,
                'title' => $resource['title']
            ]);
        } catch (\Exception $e) {
            // JSON 요청인 경우 JSON 에러 응답
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json([
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }

            // 일반 요청인 경우 에러 페이지 렌더링
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

            // 입력 데이터 검증
            $data = $this->validateResourceData($request);
            if (isset($data['error'])) {
                return $this->response->json(['error' => $data['error']], 422);
            }

            $data['user_id'] = $user['id'];
            $resourceId = $this->resource->create($data);

            return $this->response->json([
                'message' => '리소스가 생성되었습니다.',
                'data' => ['id' => $resourceId]
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

            // 권한 확인
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                return $this->response->json(['error' => '수정 권한이 없습니다.'], 403);
            }

            // 입력 데이터 검증
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
        require dirname(__DIR__) . '/View/resources/tags.php';
    }

    private function validateResourceData(Request $request, $isUpdate = false) {
        $data = [];
        $errors = [];

        if (!$isUpdate || $request->has('title')) {
            $title = trim($request->get('title'));
            if (empty($title)) {
                $errors[] = '제목은 필수입니다.';
            }
            $data['title'] = $title;
        }

        if (!$isUpdate || $request->has('content')) {
            $content = trim($request->get('content'));
            if (empty($content)) {
                $errors[] = '내용은 필수입니다.';
            }
            $data['content'] = $content;
        }

        if (!$isUpdate || $request->has('description')) {
            $description = trim($request->get('description'));
            if (empty($description)) {
                $errors[] = '설명은 필수입니다.';
            }
            $data['description'] = $description;
        }

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