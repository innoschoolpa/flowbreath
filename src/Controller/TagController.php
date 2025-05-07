<?php
// src/Controller/TagController.php

namespace Controller;

use Model\TagModel;
use Core\Controller;
use Core\Database;
use Core\Response;

class TagController extends Controller {
    private $tagModel;

    public function __construct() {
        parent::__construct();
        $this->tagModel = new TagModel(Database::getInstance());
    }

    public function index() {
        try {
            $tags = $this->tagModel->getAllTags();
            return $this->view('resources/tags', [
                'title' => '태그 관리',
                'tags' => $tags
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = '태그 목록을 불러오는 중 오류가 발생했습니다.';
            return Response::redirect('/');
        }
    }

    public function create() {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = '관리자만 태그를 생성할 수 있습니다.';
            return Response::redirect('/resources/tags');
        }

        if (!$this->validateCSRF()) {
            $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
            return Response::redirect('/resources/tags');
        }

        $tagName = trim($_POST['tag_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($tagName)) {
            $_SESSION['error'] = '태그 이름은 필수입니다.';
            return Response::redirect('/resources/tags');
        }

        try {
            $this->tagModel->createTag($tagName, $description);
            $_SESSION['success'] = '태그가 성공적으로 생성되었습니다.';
        } catch (\Exception $e) {
            $_SESSION['error'] = '태그 생성 중 오류가 발생했습니다.';
        }

        return Response::redirect('/resources/tags');
    }

    public function update($tagId) {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = '관리자만 태그를 수정할 수 있습니다.';
            return Response::redirect('/resources/tags');
        }

        if (!$this->validateCSRF()) {
            $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
            return Response::redirect('/resources/tags');
        }

        $tagName = trim($_POST['tag_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($tagName)) {
            $_SESSION['error'] = '태그 이름은 필수입니다.';
            return Response::redirect('/resources/tags');
        }

        try {
            $this->tagModel->updateTag($tagId, $tagName, $description);
            $_SESSION['success'] = '태그가 성공적으로 수정되었습니다.';
        } catch (\Exception $e) {
            $_SESSION['error'] = '태그 수정 중 오류가 발생했습니다.';
        }

        return Response::redirect('/resources/tags');
    }

    public function delete($tagId) {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = '관리자만 태그를 삭제할 수 있습니다.';
            return Response::redirect('/resources/tags');
        }

        if (!$this->validateCSRF()) {
            $_SESSION['error'] = 'CSRF 토큰이 유효하지 않습니다.';
            return Response::redirect('/resources/tags');
        }

        try {
            $this->tagModel->deleteTag($tagId);
            $_SESSION['success'] = '태그가 성공적으로 삭제되었습니다.';
        } catch (\Exception $e) {
            $_SESSION['error'] = '태그 삭제 중 오류가 발생했습니다.';
        }

        return Response::redirect('/resources/tags');
    }

    private function isAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['is_admin'];
    }

    private function validateCSRF() {
        return isset($_POST['csrf_token']) && 
               isset($_SESSION['csrf_token']) && 
               $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }
} 