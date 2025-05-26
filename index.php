<?php
ini_set('error_log', __DIR__ . '/logs/error.log');
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// _method 오버라이드 처리 (POST + _method=DELETE 등 지원)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

define('PROJECT_ROOT', __DIR__);

// Composer Autoloader
require_once PROJECT_ROOT . '/vendor/autoload.php';

// .env 환경변수 로드
if (file_exists(PROJECT_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
    $dotenv->load();
}

// Core 클래스 로드
require_once PROJECT_ROOT . '/src/Core/Router.php';
require_once PROJECT_ROOT . '/src/Core/Request.php';
require_once PROJECT_ROOT . '/src/Core/Response.php';
require_once PROJECT_ROOT . '/src/Core/Language.php';
require_once PROJECT_ROOT . '/src/Controllers/LanguageController.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Language;

// 라우터 초기화 및 설정
$request = new Request();
$uri = $request->getPath();
$method = $request->getMethod();
$router = new Router($request);
$routes = require PROJECT_ROOT . '/src/routes.php';
$routes($router);

try {
    // 라우트 처리
    $result = $router->dispatch($method, $uri);
    
    // 디버깅: 반환값 타입과 값 출력 (로그)
    error_log('[DEBUG] Router dispatch result type: ' . gettype($result));
    if (is_object($result)) {
        error_log('[DEBUG] Router dispatch result class: ' . get_class($result));
    }
    
    // 응답 처리
    if ($result instanceof Response) {
        $result->send();
    } else if (is_string($result)) {
        echo $result;
    } else {
        error_log('[FATAL] Invalid response type: ' . print_r($result, true));
        throw new \Exception('Invalid response type: ' . (is_object($result) ? get_class($result) : gettype($result)), 500);
    }
} catch (\Exception $e) {
    error_log('[FATAL] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    error_log($e->getTraceAsString());
    
    // 에러 페이지 표시
    $response = new Response();
    $response->setContentType('text/html; charset=UTF-8');
    $response->setStatusCode($e->getCode() ?: 500);
    $response->setContent(require PROJECT_ROOT . '/src/View/errors/500.php');
    $response->send();
}
exit;

// DB 연결 (config/database.php 사용)
require_once PROJECT_ROOT . '/config/database.php';
$pdo = getDbConnection();

// Language 객체 생성
$language = Language::getInstance();

// 최근 리소스
$resourceModel = new \App\Models\Resource();
$recentResources = $resourceModel->getRecentPublic(6);

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
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        $searchResults = [];
    }
}

// 공통 헤더 포함
require_once PROJECT_ROOT . '/src/View/layouts/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto text-center">
            <h1 class="display-4 mb-4">호흡을 위한 최고의 자료, FlowBreath.io</h1>
            <p class="lead mb-5">호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.</p>
            
            <!-- 검색 폼 -->
            <form action="/search" method="GET" class="mb-5">
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control" placeholder="검색어를 입력하세요...">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> 검색
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 최근 등록된 호흡 자료 -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>최근 등록된 호흡 자료</h2>
                <a href="/resources" class="btn btn-outline-primary">전체 보기</a>
            </div>
            
            <div class="row">
                <?php foreach ($recentResources as $resource): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php
                        // Extract YouTube video ID from URL
                        $youtubeId = null;
                        if (!empty($resource['link'])) {
                            $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                            if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                                $youtubeId = $matches[1];
                            }
                        }
                        
                        // Display video if found
                        if ($youtubeId): ?>
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0&rel=0" 
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    style="width: 100%; height: 100%;">
                                </iframe>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($resource['title']) ?></h5>
                            <p class="card-text text-muted">
                                <?= htmlspecialchars($resource['username'] ?? '익명') ?> · 
                                <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                            </p>
                            <p class="card-text">
                                <?= htmlspecialchars(mb_substr($resource['description'], 0, 500)) ?>...
                            </p>
                            <div class="mb-2">
                                <?php if (!empty($resource['tags'])): ?>
                                    <?php foreach ($resource['tags'] as $tag): ?>
                                        <a href="/resources?tags[]=<?= is_array($tag) ? ($tag['id'] ?? '') : '' ?>" class="tag-badge">
                                            <i class="fa fa-hashtag"></i>
                                            <span><?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary"><?= htmlspecialchars($resource['type']) ?></span>
                                <a href="/resources/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm">자세히 보기</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once PROJECT_ROOT . '/src/View/layouts/footer.php'; ?>

<style>
    :root {
        --background-color: #0f172a;
        --text-color: #f1f5f9;
        --card-bg: #1e293b;
        --border-color: #334155;
        --primary-color: #3b82f6;
        --secondary-color: #64748b;
        --accent-color: #3b82f6;
    }

    body {
        background-color: var(--background-color);
        color: var(--text-color);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        line-height: 1.6;
    }

    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(90deg, #1e40af 60%, #3b82f6 100%);
        color: #e2e8f0;
        padding: 0.45rem 1.1rem;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.12);
        border: 1px solid #3b82f6;
        transition: all 0.3s ease;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .tag-badge:hover {
        background: linear-gradient(90deg, #2563eb 60%, #1d4ed8 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
        text-decoration: none;
    }

    .tag-badge i {
        margin-right: 0.5rem;
        font-size: 0.95em;
        color: #93c5fd;
    }

    .btn-primary {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        color: var(--text-color);
    }

    .btn-primary:hover {
        background-color: #0284c7;
        border-color: #0284c7;
        color: var(--text-color);
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: var(--text-color);
    }
</style>