<?php

namespace App\Controllers;

use App\Models\Diary;
use App\Core\Auth;

class DiaryController {
    private $diaryModel;
    private $auth;

    public function __construct() {
        $this->diaryModel = new Diary();
        $this->auth = new Auth();
    }

    public function index() {
        $page = $_GET['page'] ?? 1;
        $userId = $this->auth->isLoggedIn() ? $this->auth->getUserId() : null;
        
        $diaries = $this->diaryModel->getList($page, 20, $userId);
        
        return view('diary/index', [
            'diaries' => $diaries,
            'page' => $page
        ]);
    }

    public function create() {
        if (!$this->auth->isLoggedIn()) {
            redirect('/login');
        }

        return view('diary/create');
    }

    public function store() {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $isPublic = isset($_POST['is_public']) ? (bool)$_POST['is_public'] : true;
        $tags = $_POST['tags'] ?? [];

        if (empty($title) || empty($content)) {
            return json_response(['error' => 'Title and content are required'], 400);
        }

        try {
            $diaryId = $this->diaryModel->create(
                $this->auth->getUserId(),
                $title,
                $content,
                $isPublic,
                $tags
            );

            return json_response([
                'success' => true,
                'diary_id' => $diaryId
            ]);
        } catch (\Exception $e) {
            return json_response(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        $diary = $this->diaryModel->getById($id);
        
        if (!$diary) {
            return view('errors/404');
        }

        if (!$diary['is_public'] && (!$this->auth->isLoggedIn() || $this->auth->getUserId() != $diary['user_id'])) {
            return view('errors/403');
        }

        return view('diary/show', [
            'diary' => $diary
        ]);
    }

    public function edit($id) {
        if (!$this->auth->isLoggedIn()) {
            redirect('/login');
        }

        $diary = $this->diaryModel->getById($id);
        
        if (!$diary || $diary['user_id'] != $this->auth->getUserId()) {
            return view('errors/403');
        }

        return view('diary/edit', [
            'diary' => $diary
        ]);
    }

    public function update($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $diary = $this->diaryModel->getById($id);
        
        if (!$diary || $diary['user_id'] != $this->auth->getUserId()) {
            return json_response(['error' => 'Forbidden'], 403);
        }

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $isPublic = isset($_POST['is_public']) ? (bool)$_POST['is_public'] : true;
        $tags = $_POST['tags'] ?? [];

        if (empty($title) || empty($content)) {
            return json_response(['error' => 'Title and content are required'], 400);
        }

        try {
            $this->diaryModel->update(
                $id,
                $this->auth->getUserId(),
                $title,
                $content,
                $isPublic,
                $tags
            );

            return json_response(['success' => true]);
        } catch (\Exception $e) {
            return json_response(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        $diary = $this->diaryModel->getById($id);
        
        if (!$diary || $diary['user_id'] != $this->auth->getUserId()) {
            return json_response(['error' => 'Forbidden'], 403);
        }

        try {
            $this->diaryModel->delete($id, $this->auth->getUserId());
            return json_response(['success' => true]);
        } catch (\Exception $e) {
            return json_response(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleLike($id) {
        if (!$this->auth->isLoggedIn()) {
            return json_response(['error' => 'Unauthorized'], 401);
        }

        try {
            $this->diaryModel->toggleLike($id, $this->auth->getUserId());
            return json_response(['success' => true]);
        } catch (\Exception $e) {
            return json_response(['error' => $e->getMessage()], 500);
        }
    }

    public function search() {
        $query = $_GET['q'] ?? '';
        $tags = $_GET['tags'] ?? [];
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $page = $_GET['page'] ?? 1;

        $diaries = $this->diaryModel->search($query, $tags, $startDate, $endDate, $page);

        return view('diary/search', [
            'diaries' => $diaries,
            'query' => $query,
            'tags' => $tags,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'page' => $page
        ]);
    }
} 