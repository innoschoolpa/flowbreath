<?php

namespace Core;

class BaseController
{
    protected function view($view, $data = [])
    {
        // 뷰 파일 경로 설정
        $viewPath = $_SERVER['DOCUMENT_ROOT'] . '/src/Views/' . $view . '.php';
        
        // 레이아웃 파일 경로 설정
        $headerPath = $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/header.php';
        $footerPath = $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/footer.php';
        
        // 데이터 추출
        extract($data);
        
        // 출력 버퍼 시작
        ob_start();
        
        // 헤더 포함
        if (file_exists($headerPath)) {
            require_once $headerPath;
        } else {
            error_log("Header file not found at: " . $headerPath);
            throw new \Exception("View file not found at path: " . $headerPath);
        }
        
        // 뷰 파일 포함
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            error_log("View file not found at: " . $viewPath);
            throw new \Exception("View file not found at path: " . $viewPath);
        }
        
        // 푸터 포함
        if (file_exists($footerPath)) {
            require_once $footerPath;
        } else {
            error_log("Footer file not found at: " . $footerPath);
            throw new \Exception("View file not found at path: " . $footerPath);
        }
        
        // 출력 버퍼 내용 반환
        return ob_get_clean();
    }

    protected function getCurrentUser()
    {
        if (isset($_SESSION['user_id'])) {
            $userModel = new \App\Models\User();
            return $userModel->findById($_SESSION['user_id']);
        }
        return null;
    }

    protected function setFlashMessage($type, $message)
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
} 