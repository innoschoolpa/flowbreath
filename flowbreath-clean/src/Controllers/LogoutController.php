<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class LogoutController extends Controller
{
    public function index()
    {
        // 세션 초기화 및 파기
        $session = Session::getInstance();
        $session->clear(); // 모든 세션 데이터 삭제
        session_unset();
        session_destroy();

        // CSRF 토큰 등 쿠키도 삭제
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // 로그아웃 후 로그인 페이지로 이동
        header('Location: /login');
        exit;
    }
} 