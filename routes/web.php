<?php

use App\Auth\GoogleAuth;
use App\Core\Router;
use App\Core\Response;

return function (Router $router) {
    // Google 로그인 라우트
    $router->add('GET', '/auth/google', function() {
        try {
            if (!session_id()) {
                session_start();
            }
            
            // Clear any existing error messages
            unset($_SESSION['error']);
            
            $response = new Response();
            return $response->redirect(GoogleAuth::getInstance()->getAuthUrl());
        } catch (\Exception $e) {
            error_log("Google auth error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Set error message in session
            $_SESSION['error'] = 'Google 로그인을 시작할 수 없습니다. 잠시 후 다시 시도해주세요.';
            
            $response = new Response();
            return $response->redirect('/login');
        }
    });

    // Google 콜백 라우트
    $router->add('GET', '/auth/google/callback', function() {
        try {
            if (!session_id()) {
                session_start();
            }
            
            // Log request data for debugging
            error_log("Google callback request data: " . json_encode([
                'GET' => $_GET,
                'SESSION' => $_SESSION,
                'SERVER' => [
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
                    'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT']
                ]
            ]));
            
            if (!isset($_GET['code']) || !isset($_GET['state'])) {
                throw new \Exception('필수 파라미터가 누락되었습니다.');
            }
            
            $result = GoogleAuth::getInstance()->handleCallback($_GET['code'], $_GET['state']);
            if ($result['success']) {
                // Clear any existing error messages
                unset($_SESSION['error']);
                
                $response = new Response();
                return $response->redirect($result['redirect']);
            }
            
            throw new \Exception('로그인 처리에 실패했습니다.');
        } catch (\Exception $e) {
            error_log("Google callback error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("Request data: " . json_encode($_GET));
            error_log("Session data: " . json_encode($_SESSION));
            
            $errorMessage = 'Google 로그인 처리 중 오류가 발생했습니다. ';
            if (strpos($e->getMessage(), '사용자 정보 처리') !== false) {
                $errorMessage .= '잠시 후 다시 시도해주세요.';
            } else if (strpos($e->getMessage(), '이 이메일은 이미 다른 Google 계정과 연결') !== false) {
                $errorMessage = $e->getMessage();
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            // Store error in session
            $_SESSION['error'] = $errorMessage;
            
            $response = new Response();
            return $response->redirect('/login');
        }
    });

    // Resources routes
    $router->add('GET', '/resources/create', ['App\Controllers\ResourceController', 'create']);
    $router->add('POST', '/resources/store', ['App\Controllers\ResourceController', 'store']);
    $router->add('GET', '/resources', ['App\Controllers\ResourceController', 'index']);
    $router->add('GET', '/resources/{id}', ['App\Controllers\ResourceController', 'show']);
    $router->add('GET', '/resources/{id}/edit', ['App\Controllers\ResourceController', 'edit']);
    $router->add('POST', '/resources/{id}/update', ['App\Controllers\ResourceController', 'update']);
    $router->add('POST', '/resources/{id}/delete', ['App\Controllers\ResourceController', 'delete']);

    // Breathing Exercise Routes
    $router->add('GET', '/breathing', ['App\Controllers\BreathingController', 'index']);
    $router->add('GET', '/api/breathing/patterns', ['App\Controllers\BreathingController', 'getPatterns']);
    $router->add('POST', '/api/breathing/sessions', ['App\Controllers\BreathingController', 'startSession']);
    $router->add('GET', '/api/breathing/sessions/{session_id}', ['App\Controllers\BreathingController', 'getSessionStatus']);
    $router->add('POST', '/api/breathing/sessions/{session_id}/end', ['App\Controllers\BreathingController', 'endSession']);
    $router->add('GET', '/api/breathing/sessions/{session_id}/guide', ['App\Controllers\BreathingController', 'getSessionGuide']);
    $router->add('GET', '/api/breathing/settings', ['App\Controllers\BreathingController', 'getSettings']);
    $router->add('POST', '/api/breathing/settings', ['App\Controllers\BreathingController', 'updateSettings']);
}; 