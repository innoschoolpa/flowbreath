<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class ApiMiddleware {
    public function handle(Request $request, callable $next) {
        // API 요청인 경우
        if (strpos($request->getPath(), '/api/') === 0) {
            // JSON 응답 헤더 설정
            header('Content-Type: application/json');
            
            // CORS 헤더 설정
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            
            // OPTIONS 요청 처리
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            
            // JSON 요청 본문 파싱
            $content = file_get_contents('php://input');
            if (!empty($content)) {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $_POST = $data;
                }
            }
        }
        
        return $next($request);
    }
} 