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
require_once PROJECT_ROOT . '/src/Core/Database.php';
require_once PROJECT_ROOT . '/src/Core/ConnectionManager.php';
require_once PROJECT_ROOT . '/src/Controllers/LanguageController.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Language;
use App\Core\ConnectionManager;

// 데이터베이스 연결 카운터 체크 및 리셋
ConnectionManager::checkAndResetConnections();

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