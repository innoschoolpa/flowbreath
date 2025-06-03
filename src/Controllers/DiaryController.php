<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Diary;
use App\Models\Comment;
use App\Models\Like;
use App\Core\View;
use App\Core\Session;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Auth;
use App\Core\Database;

class DiaryController extends BaseController {
    private $diaryModel;
    private $commentModel;
    private $logger;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->diaryModel = new Diary();
        $this->commentModel = new Comment(Database::getInstance()->getConnection());
        $this->logger = Logger::getInstance();
    }

    public function index() {
        $userId = $this->auth->id();
        error_log("DiaryController::index - auth->id(): " . ($userId ?? 'null'));
        error_log("DiaryController::index - SESSION user_id: " . ($_SESSION['user_id'] ?? 'not set'));
        
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        
        $result = $this->diaryModel->getList($page, $limit, $userId);
        
        return $this->view('diary/index', [
            'diaries' => $result['items'],
            'currentPage' => $page,
            'totalPages' => ceil($result['total'] / $limit)
        ]);
    }

    public function create() {
        if (!$this->auth->isLoggedIn()) {
            return redirect('/login');
        }

        return $this->view('diary/form');
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonResponse(['success' => false, 'error' => '로그인이 필요합니다.'], 401);
        }

        try {
            error_log("Raw POST data: " . file_get_contents('php://input'));
            error_log("POST data: " . print_r($_POST, true));
            error_log("REQUEST data: " . print_r($_REQUEST, true));

            $data = [
                'user_id' => $_SESSION['user_id'],
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'tags' => $_POST['tags'] ?? '',
                'is_public' => isset($_POST['is_public']) ? 1 : 0
            ];

            error_log("Processed diary data: " . print_r($data, true));

            // Validate required fields
            if (empty($data['title'])) {
                error_log("Title validation failed - title is empty");
                return $this->jsonResponse(['success' => false, 'error' => '제목을 입력해주세요.'], 400);
            }

            if (empty($data['content'])) {
                error_log("Content validation failed - content is empty");
                return $this->jsonResponse(['success' => false, 'error' => '내용을 입력해주세요.'], 400);
            }

            $diaryId = $this->diaryModel->create($data);
            
            if ($diaryId) {
                error_log("Diary created successfully with ID: " . $diaryId);
                return $this->jsonResponse(['success' => true, 'id' => $diaryId]);
            } else {
                error_log("Failed to create diary");
                return $this->jsonResponse(['success' => false, 'error' => '일기 저장에 실패했습니다.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error creating diary: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => '일기 저장 중 오류가 발생했습니다.'], 500);
        }
    }

    public function show($id) {
        $diary = $this->diaryModel->find($id);
        
        if (!$diary) {
            return $this->view('errors/404');
        }

        if (!$diary['is_public'] && $this->auth->id() !== $diary['user_id']) {
            return $this->view('errors/403');
        }

        // Debug information
        error_log("DiaryController::show - auth->id(): " . ($this->auth->id() ?? 'null'));
        error_log("DiaryController::show - SESSION user_id: " . ($_SESSION['user_id'] ?? 'not set'));
        error_log("DiaryController::show - Diary data: " . print_r($diary, true));

        return $this->view('diary/show', ['diary' => $diary]);
    }

    public function edit($id) {
        if (!$this->auth->isLoggedIn()) {
            return redirect('/login');
        }

        $diary = $this->diaryModel->find($id);
        
        if (!$diary || $diary['user_id'] !== $this->auth->id()) {
            return $this->view('errors/403');
        }

        return $this->view('diary/form', ['diary' => $diary]);
    }

    public function update($id) {
        try {
            // Get the diary
            $diaryModel = new Diary();
            $diary = $diaryModel->find($id);
            if (!$diary) {
                return $this->jsonResponse(['error' => '일기를 찾을 수 없습니다.'], 404);
            }

            // Check authorization
            if ($diary['user_id'] !== $_SESSION['user_id']) {
                return $this->jsonResponse(['error' => '권한이 없습니다.'], 403);
            }

            // Get request data
            $data = [];
            parse_str(file_get_contents('php://input'), $data);
            
            // Log the received data
            error_log('Received data in update: ' . print_r($data, true));
            
            // Validate required fields
            if (empty($data['title'])) {
                return $this->jsonResponse(['error' => '제목을 입력해주세요.'], 400);
            }

            // Prepare update data
            $updateData = [
                'title' => $data['title'],
                'content' => $data['content'] ?? '',
                'tags' => $data['tags'] ?? '',
                'is_public' => isset($data['is_public']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update the diary
            $result = $diaryModel->update($id, $updateData);
            if (!$result) {
                return $this->jsonResponse(['error' => '일기 수정에 실패했습니다.'], 500);
            }

            return $this->jsonResponse([
                'success' => true,
                'id' => $id,
                'message' => '일기가 수정되었습니다.'
            ]);

        } catch (\Exception $e) {
            error_log('Error in DiaryController::update: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '일기 수정 중 오류가 발생했습니다.'], 500);
        }
    }

    public function delete($id) {
        if (!$this->auth->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $diary = $this->diaryModel->find($id);
        
        if (!$diary || $diary['user_id'] !== $this->auth->id()) {
            return $this->jsonResponse(['error' => 'Forbidden'], 403);
        }

        $result = $this->diaryModel->delete($id);

        if ($result) {
            return $this->jsonResponse(['success' => true]);
        }

        return $this->jsonResponse(['error' => 'Failed to delete diary'], 500);
    }

    public function search() {
        $query = $_GET['query'] ?? '';
        $tag = $_GET['tag'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 20;

        $result = $this->diaryModel->search($query, $tag, $startDate, $endDate, $page, $limit);

        return $this->view('diary/index', [
            'diaries' => $result['items'],
            'currentPage' => $page,
            'totalPages' => ceil($result['total'] / $limit),
            'query' => $query,
            'tag' => $tag,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function toggleLike($id) {
        if (!$this->auth->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $result = $this->diaryModel->toggleLike($id, $this->auth->id());

        if ($result !== null) {
            return $this->jsonResponse([
                'success' => true,
                'liked' => $result,
                'like_count' => $this->diaryModel->getLikeCount($id)
            ]);
        }

        return $this->jsonResponse(['error' => 'Failed to toggle like'], 500);
    }

    public function storeComment() {
        try {
            // 디버깅을 위한 세션 정보 로깅
            error_log("Session data: " . print_r($_SESSION, true));
            error_log("POST data: " . print_r($_POST, true));
            
            if (!isset($_SESSION['user_id'])) {
                error_log("User not logged in. Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
                return $this->jsonResponse(['success' => false, 'error' => '로그인이 필요합니다.'], 401);
            }

            // CSRF 토큰 검증
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
                error_log("CSRF token missing - POST: " . ($_POST['csrf_token'] ?? 'not set') . ", SESSION: " . ($_SESSION['csrf_token'] ?? 'not set'));
                return $this->jsonResponse(['success' => false, 'error' => '보안 토큰이 없습니다. 페이지를 새로고침 후 다시 시도해주세요.'], 403);
            }

            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                error_log("CSRF token mismatch - POST: " . $_POST['csrf_token'] . ", SESSION: " . $_SESSION['csrf_token']);
                return $this->jsonResponse(['success' => false, 'error' => '보안 토큰이 유효하지 않습니다. 페이지를 새로고침 후 다시 시도해주세요.'], 403);
            }

            $diaryId = $_POST['diary_id'] ?? 0;
            if (!$diaryId) {
                error_log("Diary ID is missing or invalid: " . $diaryId);
                return $this->jsonResponse(['success' => false, 'error' => '일기 ID가 유효하지 않습니다.'], 400);
            }

            $diary = $this->diaryModel->find($diaryId);
            if (!$diary) {
                error_log("Diary not found with ID: " . $diaryId);
                return $this->jsonResponse(['success' => false, 'error' => '일기를 찾을 수 없습니다.'], 404);
            }

            // 디버깅을 위한 로그 추가
            error_log("Diary data: " . print_r($diary, true));
            error_log("Current user ID: " . $this->auth->id());
            error_log("Diary user ID: " . $diary['user_id']);
            error_log("Is public: " . ($diary['is_public'] ? 'true' : 'false'));

            // 공개 일기는 모든 로그인한 사용자가 댓글 가능
            // 비공개 일기는 작성자만 댓글 가능
            if (!$diary['is_public'] && $diary['user_id'] != $this->auth->id()) {
                error_log("Access denied: User " . $this->auth->id() . " is not the diary owner and diary is private");
                return $this->jsonResponse(['success' => false, 'error' => '비공개 일기에는 작성자만 댓글을 달 수 있습니다.'], 403);
            }

            $content = trim($_POST['content'] ?? '');
            if (empty($content)) {
                error_log("Empty comment content");
                return $this->jsonResponse(['success' => false, 'error' => '댓글 내용을 입력해주세요.'], 400);
            }

            // XSS 방지를 위한 HTML 이스케이프
            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            $data = [
                'diary_id' => $diaryId,
                'content' => $content,
                'user_id' => $this->auth->id(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            error_log("Attempting to add comment with data: " . print_r($data, true));

            try {
                $commentId = $this->diaryModel->addComment($data);
                if (!$commentId) {
                    error_log("Failed to add comment to database - No error message available");
                    return $this->jsonResponse([
                        'success' => false, 
                        'error' => '댓글 등록에 실패했습니다. 잠시 후 다시 시도해주세요.'
                    ], 500);
                }

                // 댓글 작성자 정보 가져오기
                $comment = $this->diaryModel->findComment($commentId);
                error_log("Comment data after creation: " . print_r($comment, true));
                
                if (!$comment) {
                    error_log("Comment saved but not retrieved. Comment ID: " . $commentId);
                    return $this->jsonResponse([
                        'success' => false,
                        'error' => '댓글이 저장되었지만 표시에 실패했습니다. 페이지를 새로고침해주세요.'
                    ], 500);
                }

                error_log("Comment added successfully: " . print_r($comment, true));
                return $this->jsonResponse([
                    'success' => true, 
                    'message' => '댓글이 등록되었습니다.',
                    'comment' => [
                        'id' => $comment['id'],
                        'content' => $comment['content'],
                        'author_name' => $comment['author_name'] ?? $_SESSION['user_name'],
                        'profile_image' => $comment['profile_image'] ?? $_SESSION['user_avatar'] ?? '/assets/images/default-avatar.png',
                        'created_at' => $comment['created_at']
                    ]
                ]);
            } catch (\PDOException $e) {
                error_log("Database error while adding comment: " . $e->getMessage());
                return $this->jsonResponse([
                    'success' => false, 
                    'error' => '데이터베이스 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Error in storeComment: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->jsonResponse([
                'success' => false, 
                'error' => '댓글 등록 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    public function deleteComment($id) {
        if (!$this->auth->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $comment = $this->diaryModel->findComment($id);
        
        if (!$comment || $comment['user_id'] !== $this->auth->id()) {
            return $this->jsonResponse(['error' => 'Forbidden'], 403);
        }

        $result = $this->diaryModel->deleteComment($id);

        if ($result) {
            return $this->jsonResponse(['success' => true]);
        }

        return $this->jsonResponse(['error' => 'Failed to delete comment'], 500);
    }

    public function getComments($id) {
        try {
            $diary = $this->diaryModel->find($id);
            if (!$diary) {
                return $this->jsonResponse(['success' => false, 'error' => '일기를 찾을 수 없습니다.'], 404);
            }

            if (!$diary['is_public'] && $this->auth->id() !== $diary['user_id']) {
                return $this->jsonResponse(['success' => false, 'error' => '비공개 일기입니다.'], 403);
            }

            $comments = $this->diaryModel->getComments($id);
            $userId = $this->auth->id();

            // 각 댓글에 삭제 권한 추가
            $comments = array_map(function($comment) use ($userId) {
                $comment['can_delete'] = $userId && ($comment['user_id'] == $userId);
                return $comment;
            }, $comments);

            return $this->jsonResponse([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            error_log("Error in getComments: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => '댓글을 불러오는데 실패했습니다.'], 500);
        }
    }

    public function updateComment($id) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return $this->jsonResponse(['success' => false, 'error' => '로그인이 필요합니다.'], 401);
            }

            // CSRF 토큰 검증
            $headers = getallheaders();
            $csrfToken = null;

            // 헤더에서 토큰 확인
            if (isset($headers['X-CSRF-TOKEN'])) {
                $csrfToken = $headers['X-CSRF-TOKEN'];
            } elseif (isset($headers['x-csrf-token'])) {
                $csrfToken = $headers['x-csrf-token'];
            }

            // JSON 데이터 파싱
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // 요청 본문에서 토큰 확인
            if (!$csrfToken && isset($data['csrf_token'])) {
                $csrfToken = $data['csrf_token'];
            }

            // 세션 토큰 확인
            if (!isset($_SESSION['csrf_token'])) {
                error_log("CSRF token missing in session");
                return $this->jsonResponse(['success' => false, 'error' => '보안 토큰이 없습니다. 페이지를 새로고침 후 다시 시도해주세요.'], 403);
            }

            if (!$csrfToken || $csrfToken !== $_SESSION['csrf_token']) {
                error_log("CSRF token mismatch - Header/Data: " . ($csrfToken ?? 'not set') . ", Session: " . $_SESSION['csrf_token']);
                return $this->jsonResponse(['success' => false, 'error' => '보안 토큰이 유효하지 않습니다. 페이지를 새로고침 후 다시 시도해주세요.'], 403);
            }

            if (!isset($data['content'])) {
                return $this->jsonResponse(['success' => false, 'error' => '댓글 내용이 필요합니다.'], 400);
            }

            $content = trim($data['content']);
            if (empty($content)) {
                return $this->jsonResponse(['success' => false, 'error' => '댓글 내용을 입력해주세요.'], 400);
            }

            // 댓글 존재 여부 및 권한 확인
            $comment = $this->diaryModel->findComment($id);
            if (!$comment) {
                return $this->jsonResponse(['success' => false, 'error' => '댓글을 찾을 수 없습니다.'], 404);
            }

            if ($comment['user_id'] !== $this->auth->id()) {
                return $this->jsonResponse(['success' => false, 'error' => '댓글을 수정할 권한이 없습니다.'], 403);
            }

            // XSS 방지를 위한 HTML 이스케이프
            $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            // 댓글 업데이트
            $result = $this->diaryModel->updateComment($id, $content);
            if (!$result) {
                return $this->jsonResponse(['success' => false, 'error' => '댓글 수정에 실패했습니다.'], 500);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => '댓글이 수정되었습니다.',
                'content' => $content
            ]);

        } catch (\Exception $e) {
            error_log("Error in updateComment: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => '댓글 수정 중 오류가 발생했습니다.'], 500);
        }
    }
} 