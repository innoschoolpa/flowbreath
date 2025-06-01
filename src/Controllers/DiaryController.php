<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Diary;

class DiaryController extends Controller {
    private $diaryModel;
    private $auth;

    public function __construct() {
        parent::__construct();
        $this->diaryModel = new Diary();
        $this->auth = Auth::getInstance();
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

        return $this->view('diary/create');
    }

    public function store() {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $diaryData = [
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'tags' => $_POST['tags'] ?? '',
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        ];

        // Add logging
        error_log("Attempting to create diary with data: " . json_encode($diaryData, JSON_UNESCAPED_UNICODE));

        $diaryId = $this->diaryModel->create($diaryData);
        
        if ($diaryId) {
            error_log("Diary created successfully with ID: " . $diaryId);
            return json_response(['success' => true, 'id' => $diaryId]);
        } else {
            error_log("Failed to create diary. Data: " . json_encode($diaryData, JSON_UNESCAPED_UNICODE));
            return json_response(['success' => false, 'error' => 'Failed to create diary'], 500);
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

        return $this->view('diary/edit', ['diary' => $diary]);
    }

    public function update($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $diary = $this->diaryModel->find($id);
        
        if (!$diary || $diary['user_id'] !== $this->auth->id()) {
            return json_response(['error' => 'Forbidden'], 403);
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'tags' => $_POST['tags'] ?? '',
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        ];

        $result = $this->diaryModel->update($id, $data);

        if ($result) {
            return json_response(['success' => true]);
        }

        return json_response(['error' => 'Failed to update diary'], 500);
    }

    public function delete($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $diary = $this->diaryModel->find($id);
        
        if (!$diary || $diary['user_id'] !== $this->auth->id()) {
            return json_response(['error' => 'Forbidden'], 403);
        }

        $result = $this->diaryModel->delete($id);

        if ($result) {
            return json_response(['success' => true]);
        }

        return json_response(['error' => 'Failed to delete diary'], 500);
    }

    public function search() {
        $query = $_GET['query'] ?? '';
        $tag = $_GET['tag'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $page = $_GET['page'] ?? 1;
        $limit = 20;

        $diaries = $this->diaryModel->search($query, $tag, $startDate, $endDate, $page, $limit);

        return $this->view('diary/index', [
            'diaries' => $diaries,
            'currentPage' => $page,
            'totalPages' => ceil($diaries['total'] / $limit),
            'query' => $query,
            'tag' => $tag,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function toggleLike($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $result = $this->diaryModel->toggleLike($id, $this->auth->id());

        if ($result !== null) {
            return json_response(['success' => true, 'liked' => $result]);
        }

        return json_response(['error' => 'Failed to toggle like'], 500);
    }

    public function storeComment() {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        // CSRF 토큰 검증
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return json_response(['error' => 'Invalid CSRF token'], 403);
        }

        $diaryId = $_POST['diary_id'] ?? 0;
        $diary = $this->diaryModel->find($diaryId);
        
        if (!$diary) {
            return json_response(['error' => 'Diary not found'], 404);
        }

        // 비공개 일기에는 작성자만 댓글을 달 수 있도록 수정
        if (!$diary['is_public'] && $diary['user_id'] !== $this->auth->id()) {
            return json_response(['error' => 'Cannot comment on private diary'], 403);
        }

        $data = [
            'diary_id' => $diaryId,
            'content' => $_POST['content'] ?? '',
            'user_id' => $this->auth->id()
        ];

        if (empty($data['content'])) {
            return json_response(['error' => 'Comment content is required'], 400);
        }

        try {
            $result = $this->diaryModel->addComment($data);
            if ($result) {
                return json_response(['success' => true, 'id' => $result]);
            }
            return json_response(['error' => 'Failed to add comment'], 500);
        } catch (\Exception $e) {
            error_log("Error in storeComment: " . $e->getMessage());
            return json_response(['error' => 'Failed to add comment'], 500);
        }
    }

    public function deleteComment($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $comment = $this->diaryModel->findComment($id);
        
        if (!$comment || $comment['user_id'] !== $this->auth->id()) {
            return json_response(['error' => 'Forbidden'], 403);
        }

        $result = $this->diaryModel->deleteComment($id);

        if ($result) {
            return json_response(['success' => true]);
        }

        return json_response(['error' => 'Failed to delete comment'], 500);
    }
} 