<?php
namespace App\Middleware;

use App\Auth\Auth;

/**
 * 인증 미들웨어
 * 로그인이 필요한 페이지에 대한 접근을 제어합니다.
 */
class AuthMiddleware {
    private $auth;

    public function __construct() {
        $this->auth = Auth::getInstance();
    }

    /**
     * 인증 상태 확인 및 처리
     */
    public function handle() {
        if (!$this->auth->check()) {
            $_SESSION['auth_error'] = '로그인이 필요한 서비스입니다.';
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
}

class AdminMiddleware {
    private $auth;

    public function __construct() {
        $this->auth = Auth::getInstance();
    }

    public function handle() {
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            require APP_PATH . '/View/error/403.php';
            exit;
        }
    }
} 