<?php

use App\Auth\GoogleAuth;

// Google 로그인 라우트
Route::get('/auth/google', function() {
    try {
        if (!session_id()) {
            session_start();
        }
        
        // Clear any existing error messages
        unset($_SESSION['error']);
        
        return redirect(GoogleAuth::getInstance()->getAuthUrl());
    } catch (\Exception $e) {
        error_log("Google auth error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return redirect('/login')->with('error', 'Google 로그인을 시작할 수 없습니다. 잠시 후 다시 시도해주세요.');
    }
});

// Google 콜백 라우트
Route::get('/auth/google/callback', function() {
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
            
            return redirect($result['redirect']);
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
        
        return redirect('/login');
    }
}); 