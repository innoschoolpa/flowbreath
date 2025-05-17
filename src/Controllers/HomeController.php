<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Response;
use App\Core\Request;
use App\Core\Language;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Models\Resource;
use App\Models\Tag;
use App\Models\User;

class HomeController extends BaseController
{
    private $db;
    private $request;
    private $session;
    private $view;
    private $resourceModel;
    private $tagModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->request = new Request();
        $this->session = new Session();
        $this->view = new View();
        $this->resourceModel = new Resource();
        $this->tagModel = new Tag();
        $this->userModel = new User();
    }

    public function index()
    {
        if ($this->session->isLoggedIn()) {
            $this->renderMainPage();
        } else {
            $this->renderLandingPage();
        }
    }

    private function renderMainPage()
    {
        $userId = $this->session->get('user_id');
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $this->session->set('error', 'User not found');
            $this->redirect('/logout');
            return;
        }

        $page = $this->request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = $this->request->get('search', '');
        $tagId = $this->request->get('tag_id');
        $sort = $this->request->get('sort', 'latest');
        $filter = $this->request->get('filter', 'all');

        $resources = $this->resourceModel->getResourcesForUser($userId, $search, $tagId, $sort, $filter, $limit, $offset);
        $totalResources = $this->resourceModel->getTotalResourcesForUser($userId, $search, $tagId, $filter);
        $totalPages = ceil($totalResources / $limit);

        $popularTags = $this->tagModel->getPopularTags($userId, 10);
        $recentTags = $this->tagModel->getRecentTags($userId, 10);

        $language = Language::getInstance();

        $this->view->render('home', [
            'user' => $user,
            'resources' => $resources,
            'popularTags' => $popularTags,
            'recentTags' => $recentTags,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'tagId' => $tagId,
            'sort' => $sort,
            'filter' => $filter,
            'language' => $language
        ]);
    }

    private function renderLandingPage()
    {
        $this->view->render('landing');
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
        
        $language = Language::getInstance();
        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(200);
        $response->setContent($this->renderApiDocsPage($language));
        return $response;
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

    private function renderApiDocsPage($language)
    {
        ob_start();
        include dirname(__DIR__) . '/View/api/docs.php';
        return ob_get_clean();
    }
} 