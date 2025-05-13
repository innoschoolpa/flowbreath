<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Request;
use App\Core\Language;
use App\Core\Database;
use App\Models\Resource;
use App\Models\Tag;

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

        // Language 객체 생성
        $language = Language::getInstance();

        // 최근 리소스
        $resourceModel = new Resource();
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
        $recentResources = $resourceModel->getRecentPublic(6, $lang);

        // 인기 태그
        $tagModel = new Tag();
        $popularTags = $tagModel->getPopularTags(8);

        // 로그인 상태
        $isLoggedIn = isset($_SESSION['user_id']);
        $user = $isLoggedIn ? [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'profile_image' => $_SESSION['user_avatar'] ?? null,
            'bio' => $_SESSION['user_bio'] ?? '',
            'social_links' => $_SESSION['user_social_links'] ?? ''
        ] : null;

        // 검색 처리
        $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
        $searchResults = [];
        if ($searchQuery !== '') {
            try {
                $searchResults = $resourceModel->searchResources($searchQuery, 10, 0);
            } catch (\Exception $e) {
                error_log("Search error: " . $e->getMessage());
                $searchResults = [];
            }
        }

        // 메인 페이지 HTML 생성
        $html = $this->renderMainPage($language, $recentResources, $popularTags, $isLoggedIn, $user, $searchQuery, $searchResults);
        
        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(200);
        $response->setContent($html);
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

    public function apiDocs()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(200);
        $response->setContent($this->renderApiDocsPage());
        return $response;
    }

    private function renderMainPage($language, $recentResources, $popularTags, $isLoggedIn, $user, $searchQuery, $searchResults)
    {
        ob_start();
        include dirname(__DIR__, 2) . '/templates/home.php';
        return ob_get_clean();
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

    private function renderApiDocsPage()
    {
        ob_start();
        include dirname(__DIR__) . '/View/api/docs.php';
        return ob_get_clean();
    }
} 