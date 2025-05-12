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

// --- 라우터 분기 추가 시작 ---
require_once PROJECT_ROOT . '/src/Core/Router.php';
require_once PROJECT_ROOT . '/src/Controllers/LanguageController.php';
// 필요시 다른 컨트롤러도 require

require_once PROJECT_ROOT . '/src/Core/Request.php';
require_once PROJECT_ROOT . '/src/Core/Response.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

$request = new Request();
$uri = $request->getPath();
$method = $request->getMethod();

// 라우터 초기화 및 라우트 설정
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
    // 에러 처리
    $response = new Response();
    $response->setContentType('text/html; charset=UTF-8');
    $response->setStatusCode($e->getCode() ?: 500);
    
    // 에러 페이지 HTML 생성
    $errorHtml = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$e->getCode()}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #e74c3c;
            margin: 0 0 20px;
        }
        p {
            color: #34495e;
            margin: 0 0 20px;
        }
        .home-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .home-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Error {$e->getCode()}</h1>
        <p>{$e->getMessage()}</p>
        <a href="/" class="home-link">홈으로 돌아가기</a>
    </div>
</body>
</html>
HTML;
    
    $response->setContent($errorHtml);
    $response->send();
}
exit;
// --- 라우터 분기 추가 끝 ---

// DB 연결 (config/database.php 사용)
require_once PROJECT_ROOT . '/config/database.php';
$pdo = getDbConnection();

// Language 객체 생성
require_once PROJECT_ROOT . '/src/Core/Language.php';
use App\Core\Language;
$language = Language::getInstance();

// 최근 리소스
$resourceModel = new \App\Models\Resource();
$recentResources = $resourceModel->getRecentPublic(4);

// 인기 태그
$tagModel = new \App\Models\Tag();
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
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($resource['title']) ?></h5>
                            <p class="card-text text-muted">
                                <?= htmlspecialchars($resource['author_name'] ?? '익명') ?> · 
                                <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                            </p>
                            <p class="card-text">
                                <?= htmlspecialchars(mb_substr($resource['description'], 0, 100)) ?>...
                            </p>
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

    <!-- 인기 태그 -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-4">인기 태그</h2>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($popularTags as $tag): ?>
                <a href="/tags/<?= urlencode($tag['name']) ?>" class="btn btn-outline-secondary">
                    #<?= htmlspecialchars($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once PROJECT_ROOT . '/src/View/layouts/footer.php'; ?>