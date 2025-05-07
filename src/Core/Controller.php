<?php
// src/Core/Controller.php

namespace Core;

abstract class Controller {
    protected function __construct() {
        // Initialize session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    protected function view($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = dirname(__DIR__) . '/View/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View file not found: {$viewFile}");
        }

        // Get the buffered content
        $content = ob_get_clean();

        return $content;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = '로그인이 필요한 기능입니다.';
            $this->redirect('/login');
        }
    }

    protected function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user']['is_admin'];
    }

    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = '관리자 권한이 필요한 기능입니다.';
            $this->redirect('/');
        }
    }

    protected function validateCSRF() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
} 