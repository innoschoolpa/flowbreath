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
        // 로그인 사용자 정보 세팅
        $user = null;
        if (isset($_SESSION['user_id'])) {
            $user = [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'profile_image' => $_SESSION['user_avatar'] ?? null,
                'is_admin' => $_SESSION['is_admin'] ?? false
            ];
        }
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
            'language_code' => (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en') ? 'en' : 'ko',
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
                'user' => $user,
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
                'user' => $user,
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
        error_log('[DEBUG] Entered ResourceController::store');
        try {
            error_log('[DEBUG] Start: 인증 체크');
            $user = $this->auth->user();
            error_log('[DEBUG] 인증 체크 완료');

            if (!$user) {
                $_SESSION['error_message'] = '로그인이 필요합니다.';
                return $this->response->redirect('/login');
            }
            $userId = is_array($user) ? $user['id'] : $user->id;

            // 입력값 검증
            error_log('[DEBUG] Start: 입력값 검증');
            $validator = new \App\Core\Validator();
            $validator->validate([
                'title' => [
                    'required' => true,
                    'min' => 2,
                    'max' => 100,
                    'message' => '제목은 2~100자 사이로 입력해주세요.'
                ],
                'content' => [
                    'required' => true,
                    'min' => 5,
                    'message' => '내용을 입력해주세요.'
                ],
                'description' => [
                    'required' => false,
                    'min' => 10,
                    'max' => 500,
                    'message' => '설명은 10~500자 사이로 입력해주세요.'
                ]
            ]);
            error_log('[DEBUG] 입력값 검증 완료');

            if ($validator->hasErrors()) {
                error_log('[DEBUG] 입력값 검증 에러: ' . json_encode($validator->getErrors()));
                error_log('[DEBUG] wantsJson: ' . ($request->wantsJson() ? 'true' : 'false'));
                error_log('[DEBUG] isAjax: ' . ($request->isAjax() ? 'true' : 'false'));
                if ($request->wantsJson() || $request->isAjax()) {
                    error_log('[DEBUG] Returning JSON response');
                    return $this->response->json(['error' => $validator->getErrors()], 422);
                }
                error_log('[DEBUG] Setting error message and redirecting');
                $_SESSION['error_message'] = $validator->getFirstError();
                return $this->response->redirect('/resources/create');
            }

            // 파일 업로드 처리
            error_log('[DEBUG] Start: 파일 업로드 처리');
            $filePath = null;
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if (!in_array($_FILES['file']['type'], $allowedTypes)) {
                    error_log('[DEBUG] 파일 타입 에러: ' . $_FILES['file']['type']);
                    if ($request->wantsJson() || $request->isAjax()) {
                        return $this->response->json(['error' => '지원하지 않는 파일 형식입니다.'], 422);
                    }
                    $_SESSION['error_message'] = '지원하지 않는 파일 형식입니다.';
                    return $this->response->redirect('/resources/create');
                }

                if ($_FILES['file']['size'] > $maxSize) {
                    error_log('[DEBUG] 파일 크기 에러: ' . $_FILES['file']['size']);
                    if ($request->wantsJson() || $request->isAjax()) {
                        return $this->response->json(['error' => '파일 크기는 5MB를 초과할 수 없습니다.'], 422);
                    }
                    $_SESSION['error_message'] = '파일 크기는 5MB를 초과할 수 없습니다.';
                    return $this->response->redirect('/resources/create');
                }

                $uploadDir = __DIR__ . '/../../public/uploads/resources/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
                $filePath = '/uploads/resources/' . $fileName;

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName)) {
                    error_log('[DEBUG] 파일 업로드 실패');
                    if ($request->wantsJson() || $request->isAjax()) {
                        return $this->response->json(['error' => '파일 업로드에 실패했습니다.'], 500);
                    }
                    $_SESSION['error_message'] = '파일 업로드에 실패했습니다.';
                    return $this->response->redirect('/resources/create');
                }
            }
            error_log('[DEBUG] 파일 업로드 처리 완료');

            // 태그 처리
            error_log('[DEBUG] Start: 태그 처리');
            $tags = [];
            if (!empty($_POST['tags'])) {
                $tags = array_map('trim', explode(',', $_POST['tags']));
                $tags = array_filter($tags);
            }
            error_log('[DEBUG] 태그 처리 완료: ' . json_encode($tags));

            // 리소스 데이터 준비 (기본 테이블용 - title, content, description 제외)
            error_log('[DEBUG] Start: 리소스 데이터 준비');
            
            function slugify($text) {
                $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
                $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
                $text = preg_replace('~[^-\w]+~', '', $text);
                $text = trim($text, '-');
                $text = preg_replace('~-+~', '-', $text);
                $text = strtolower($text);
                return empty($text) ? 'n-a' : $text;
            }
            
            $slug = slugify($_POST['title']);
            $status = $_POST['status'] ?? 'draft';
            $visibility = $_POST['visibility'] ?? 'public';
            $languageCode = $_POST['language_code'] ?? 'ko';
            $category = $_POST['category'] ?? null;
            
            // resources 테이블에 저장될 데이터 (title, content, description 제외)
            $resourceData = [
                'user_id' => $userId,
                'file_path' => $filePath,
                'tags' => $tags,
                'is_public' => isset($_POST['is_public']) ? 1 : 0,
                'slug' => $slug,
                'status' => $status,
                'visibility' => $visibility,
                'language_code' => $languageCode,
                'link' => $_POST['link'] ?? null,
                'category' => $category
            ];
            error_log('[DEBUG] 리소스 데이터 준비 완료: ' . json_encode($resourceData));

            // 리소스 생성 (기본 테이블만)
            error_log('[DEBUG] Start: 리소스 생성');
            $resourceId = $this->resource->create($resourceData);
            error_log('[DEBUG] 리소스 생성 결과: ' . json_encode($resourceId));

            // 번역 테이블에 다국어 데이터 저장
            if ($resourceId) {
                error_log('[DEBUG] Start: 번역 데이터 저장');
                $db = \App\Core\Database::getInstance();
                $sql = "INSERT INTO resource_translations (resource_id, language_code, title, content, description) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), description = VALUES(description)";
                $result = $db->query($sql, [
                    $resourceId,
                    $languageCode,
                    $_POST['title'],
                    $_POST['content'], // CKEditor 내용 그대로 저장
                    $_POST['description']
                ]);
                error_log('[DEBUG] 번역 데이터 저장 결과: ' . json_encode($result));
            }

            if (!$resourceId) {
                error_log('[DEBUG] 리소스 생성 실패');
                throw new \Exception('리소스 생성에 실패했습니다.');
            }

            if ($request->wantsJson() || $request->isAjax()) {
                error_log('[DEBUG] 리소스 생성 성공 - JSON 반환');
                return $this->response->json([
                    'message' => '리소스가 성공적으로 생성되었습니다.',
                    'data' => ['id' => $resourceId]
                ], 201);
            }

            if (!empty($resourceId)) {
                return $this->response->redirect('/resources/view/' . $resourceId);
            } else {
                return $this->response->redirect('/resources');
            }

            error_log('[DEBUG] End of try block in store');
            return $this->response->json(['error' => 'Unknown error (try block fallthrough)'], 500);
        } catch (\Exception $e) {
            error_log("Error in ResourceController::store: " . $e->getMessage());
            error_log($e->getTraceAsString());
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json(['error' => $e->getMessage()], 500);
            }
            $_SESSION['error_message'] = $e->getMessage();
            return $this->response->redirect('/resources/create');
        }
        error_log('[DEBUG] End of store method');
    }

    public function update(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                if ($request->wantsJson() || $request->isAjax()) {
                    return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
                }
                $_SESSION['error_message'] = '로그인이 필요합니다.';
                return $this->response->redirect('/login');
            }
            $resource = $this->resource->findById($id);
            if (!$resource) {
                if ($request->wantsJson() || $request->isAjax()) {
                    return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
                }
                $_SESSION['error_message'] = '리소스를 찾을 수 없습니다.';
                return $this->response->redirect('/resources');
            }
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                if ($request->wantsJson() || $request->isAjax()) {
                    return $this->response->json(['error' => '수정 권한이 없습니다.'], 403);
                }
                $_SESSION['error_message'] = '수정 권한이 없습니다.';
                return $this->response->redirect('/resources');
            }
            
            // 태그 처리
            $tags = [];
            if (!empty($_POST['tags'])) {
                $tags = array_map('trim', explode(',', $_POST['tags']));
                $tags = array_filter($tags);
            }
            
            function slugify($text) {
                $text = preg_replace('~[^\pL\d]+~u', '-', $text);
                $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
                $text = preg_replace('~[^-\w]+~', '', $text);
                $text = trim($text, '-');
                $text = preg_replace('~-+~', '-', $text);
                $text = strtolower($text);
                return empty($text) ? 'n-a' : $text;
            }
            
            $slug = slugify($_POST['title']);
            $status = $_POST['status'] ?? ($resource['status'] ?? 'draft');
            $visibility = $_POST['visibility'] ?? ($resource['visibility'] ?? 'public');
            $is_public = isset($_POST['is_public']) ? 1 : ($resource['is_public'] ?? 0);
            $languageCode = $_POST['language_code'] ?? ($_SESSION['lang'] ?? 'ko');
            $category = $_POST['category'] ?? ($resource['category'] ?? null);
            
            // resources 테이블 업데이트 (title, content, description 제외)
            $resourceUpdateData = [
                'status' => $status,
                'visibility' => $visibility,
                'is_public' => $is_public,
                'slug' => $slug,
                'tags' => $tags,
                'language_code' => $languageCode,
                'link' => $_POST['link'] ?? ($resource['link'] ?? null),
                'category' => $category
            ];
            
            // 파일 업로드 처리 (있을 경우)
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if (in_array($_FILES['file']['type'], $allowedTypes) && $_FILES['file']['size'] <= $maxSize) {
                    $uploadDir = __DIR__ . '/../../public/uploads/resources/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
                    $filePath = '/uploads/resources/' . $fileName;

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName)) {
                        $resourceUpdateData['file_path'] = $filePath;
                    }
                }
            }
            
            $this->resource->update($id, $resourceUpdateData);
            
            // 번역 테이블 업데이트
            if (!empty($_POST['title']) || !empty($_POST['content']) || !empty($_POST['description'])) {
                $db = \App\Core\Database::getInstance();
                $sql = "INSERT INTO resource_translations (resource_id, language_code, title, content, description) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), description = VALUES(description)";
                $db->query($sql, [
                    $id,
                    $languageCode,
                    $_POST['title'] ?? '',
                    $_POST['content'] ?? '',
                    $_POST['description'] ?? ''
                ]);
            }
            
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json(['message' => '리소스가 수정되었습니다.']);
            }
            $_SESSION['success_message'] = '리소스가 수정되었습니다.';
            return $this->response->redirect("/resources/view/{$id}");
        } catch (\Exception $e) {
            error_log("Error in ResourceController::update: " . $e->getMessage());
            error_log($e->getTraceAsString());
            if ($request->wantsJson() || $request->isAjax()) {
                return $this->response->json(['error' => $e->getMessage()], 500);
            }
            $_SESSION['error_message'] = $e->getMessage();
            return $this->response->redirect("/resources/{$id}/edit");
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

    public function search(Request $request) {
        $keyword = $request->get('keyword', '');
        $tagIds = $request->get('tags', []);
        $sort = $request->get('sort', 'created_desc');
        $filter = $request->get('filter', 'all');
        $page = max(1, (int)$request->get('page', 1));
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

        try {
            $resourceModel = new \App\Models\Resource();
            $resources = $resourceModel->search($params);
            $total_count = $resourceModel->count($params);
            $total_pages = ceil($total_count / $limit);

            $tagModel = new \App\Models\Tag();
            $all_tags = $tagModel->getAllTags();

            return $this->view('resources/search', [
                'resources' => $resources,
                'all_tags' => $all_tags,
                'keyword' => $keyword,
                'selected_tag_ids' => $tagIds,
                'sort' => $sort,
                'filter' => $filter,
                'total_count' => $total_count,
                'total_pages' => $total_pages,
                'current_page' => $page
            ]);
        } catch (\Exception $e) {
            error_log("Error in ResourceController::search: " . $e->getMessage());
            return $this->view('resources/search', [
                'resources' => [],
                'all_tags' => [],
                'keyword' => $keyword,
                'selected_tag_ids' => $tagIds,
                'sort' => $sort,
                'filter' => $filter,
                'total_count' => 0,
                'total_pages' => 1,
                'current_page' => $page,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function toggleVisibility(Request $request, $id) {
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
                return $this->response->json(['error' => '권한이 없습니다.'], 403);
            }

            $isPublic = !$resource['is_public'];
            $this->resource->update($id, ['is_public' => $isPublic]);

            return $this->response->json([
                'message' => $isPublic ? '리소스가 공개되었습니다.' : '리소스가 비공개되었습니다.',
                'is_public' => $isPublic
            ]);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
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

    public function create(Request $request)
    {
        $user = $this->auth->user();
        if (!$user) {
            $_SESSION['error_message'] = '로그인이 필요합니다.';
            return $this->response->redirect('/login');
        }

        $response = new \App\Core\Response();
        $content = $this->view('resources/create');
        $response->setContent($content);
        return $response;
    }

    private function validateResourceData(Request $request, $isUpdate = false) {
        $data = [];
        $errors = [];
        $lang = $_SESSION['lang'] ?? 'ko';
        $translations = [];

        // POST 데이터 로그 출력 (디버깅용)
        error_log('POST title: ' . var_export($request->getPost('title'), true));
        error_log('POST content: ' . var_export($request->getPost('content'), true));
        error_log('POST description: ' . var_export($request->getPost('description'), true));

        // 다국어 입력 처리
        $title = trim($request->getPost('title') ?? '');
        $content = trim($request->getPost('content') ?? '');
        $description = trim($request->getPost('description') ?? '');

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

        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }

        $data['translations'] = $translations;
        return $data;
    }

    /**
     * 리소스 수정 폼
     */
    public function edit(Request $request, $id)
    {
        try {
            $resource = $this->resource->findById($id);
            if (!$resource) {
                throw new \Exception("리소스를 찾을 수 없습니다.", 404);
            }
            // 태그, 관련 리소스 등 추가 데이터 필요시 불러오기
            $tags = $this->resource->getTags($id);
            $all_resources = $this->resource->getAll();
            $current_related_ids = $this->resource->getResourceTagIds($id);
            $csrf_token = $_SESSION['csrf_token'] ?? '';
            return $this->view('resources/create', [
                'resource' => $resource,
                'tags' => $tags,
                'all_resources' => $all_resources,
                'current_related_ids' => $current_related_ids,
                'csrf_token' => $csrf_token
            ]);
        } catch (\Exception $e) {
            return $this->view('errors/500', [
                'error' => $e->getMessage(),
                'title' => '500 Internal Server Error'
            ], 500);
        }
    }

    public function delete(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                $_SESSION['error_message'] = '로그인이 필요합니다.';
                return $this->response->redirect('/login');
            }
            $resource = $this->resource->findById($id);
            if (!$resource) {
                $_SESSION['error_message'] = '리소스를 찾을 수 없습니다.';
                return $this->response->redirect('/resources');
            }
            if ($resource['user_id'] !== $user['id'] && empty($user['is_admin'])) {
                $_SESSION['error_message'] = '삭제 권한이 없습니다.';
                return $this->response->redirect('/resources');
            }
            // CSRF 토큰 검증
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$csrfToken || $csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['error_message'] = 'CSRF 토큰이 유효하지 않습니다.';
                return $this->response->redirect("/resources/{$id}");
            }
            $this->resource->delete($id);
            $_SESSION['success_message'] = '리소스가 삭제되었습니다.';
            return $this->response->redirect('/resources');
        } catch (\Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            return $this->response->redirect("/resources/{$id}");
        }
    }
}