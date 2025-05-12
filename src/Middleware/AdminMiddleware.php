<?php
namespace Middleware;

/**
 * 관리자 인증 미들웨어
 * 관리자 권한이 필요한 페이지에 대한 접근을 제어합니다.
 */
class AdminMiddleware extends Middleware {
    /**
     * 관리자 권한 확인 및 처리
     */
    public function handle() {
        // 세션이 시작되지 않은 경우 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 로그인 상태 확인
        if (!isset($_SESSION['user'])) {
            // 현재 URL을 세션에 저장 (로그인 후 리디렉션용)
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            // 로그인 페이지로 리디렉션
            header('Location: /auth/login');
            exit;
        }

        // 관리자 권한 확인
        if (!$_SESSION['user']['is_admin']) {
            // 권한 없음 페이지로 리디렉션
            header('Location: /error/403');
            exit;
        }

        // 다음 미들웨어로 전달
        return $this->handleNext();
    }
} 