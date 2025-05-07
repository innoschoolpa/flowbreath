<?php

namespace App\Controller;

use App\Core\Response;
use App\Core\Request;

class HomeController
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(200);
        $response->setContent($this->renderIndexPage());
        return $response;
    }

    public function notFound()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(404);
        $response->setContent($this->renderNotFoundPage());
        return $response;
    }

    private function renderIndexPage()
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowBreath.io</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        p {
            color: #34495e;
            margin-bottom: 15px;
        }
        .api-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .api-list h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .api-list ul {
            list-style-type: none;
            padding-left: 0;
        }
        .api-list li {
            margin-bottom: 10px;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>FlowBreath.io에 오신 것을 환영합니다</h1>
        <p>이 서비스는 API 엔드포인트를 제공합니다.</p>
        
        <div class="api-list">
            <h2>사용 가능한 API 엔드포인트:</h2>
            <ul>
                <li><strong>GET /api/health</strong> - 서비스 상태 확인</li>
                <li><strong>GET /api/test/error</strong> - 에러 테스트</li>
                <li><strong>GET /api/test/warning</strong> - 경고 테스트</li>
                <li><strong>GET /api/test/notice</strong> - 알림 테스트</li>
                <li><strong>GET /api/test/memory</strong> - 메모리 테스트</li>
                <li><strong>GET /api/test/performance</strong> - 성능 테스트</li>
            </ul>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function renderNotFoundPage()
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 페이지를 찾을 수 없습니다</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        p {
            color: #34495e;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 - 페이지를 찾을 수 없습니다</h1>
        <p>요청하신 페이지를 찾을 수 없습니다.</p>
        <a href="/" class="back-link">홈으로 돌아가기</a>
    </div>
</body>
</html>
HTML;
    }
} 