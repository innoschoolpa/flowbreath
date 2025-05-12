<?php

namespace App\Core;

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__, 2));
}

class Application
{
    private static $instance = null;
    private $container = [];
    private $config = [];
    private $router = null;
    private $request;
    private $response;
    private $initialized = false;
    private $errorHandler = null;
    private $memoryManager = null;

    private function __construct()
    {
        // 메모리 관리자를 먼저 초기화
        $this->memoryManager = MemoryManager::getInstance();
        
        // 최소한의 설정만 로드
        $this->loadEssentialConfig();
        $this->response = new Response();

        // 가비지 컬렉션 강제 실행
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    private function loadEssentialConfig()
    {
        // 필수 설정만 로드
        $appConfig = require PROJECT_ROOT . '/config/app.php';
        
        $this->config = [
            'app' => $appConfig
        ];

        // 필수 PHP 설정만 적용
        date_default_timezone_set($appConfig['default_timezone']);

        // 가비지 컬렉션 강제 실행
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    private function loadFullConfig()
    {
        if (!isset($this->config['database'])) {
            $dbConfigPath = PROJECT_ROOT . '/config/database.php';
            if (!file_exists($dbConfigPath)) {
                throw new \Exception("Database configuration file not found at: " . $dbConfigPath);
            }
            $this->config['database'] = require $dbConfigPath;

            // 가비지 컬렉션 강제 실행
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function debug($message) {
        error_log($message);
        if (php_sapi_name() !== 'cli') {
            echo '<pre style="color:#c00;background:#fffbe6;padding:4px 8px;border:1px solid #f5c6cb;margin:2px 0;font-size:13px;z-index:9999;position:relative;">' . htmlspecialchars($message) . '</pre>';
        } else {
            echo $message . "\n";
        }
    }

    private function initialize()
    {
        $this->debug('[DEBUG] Application::initialize() called');
        if (!$this->initialized) {
            $this->memoryManager->createCheckpoint('initialization_start');
            
            // 필요한 설정만 로드
            $this->loadFullConfig();
            
            $this->initializeErrorHandler();
            $this->initializeComponents();
            
            $this->initialized = true;
            $this->memoryManager->createCheckpoint('initialization_end');

            // 가비지 컬렉션 강제 실행
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    private function initializeErrorHandler()
    {
        try {
            $this->errorHandler = ErrorHandler::getInstance();
        } catch (\Exception $e) {
            error_log("Error initializing ErrorHandler: " . $e->getMessage());
            throw $e;
        }
    }

    private function initializeComponents()
    {
        $this->debug('[DEBUG] Application::initializeComponents() called');
        // 요청/응답 객체 초기화
        $this->request = new Request();
        
        // 라우터 초기화
        if ($this->router === null) {
            $this->debug('[DEBUG] Initializing Router');
            $this->router = new Router($this->request);
            $routes = require PROJECT_ROOT . '/src/routes.php';
            if (is_callable($routes)) {
                $routes($this->router);
            }
        }
        
        // 라우팅 매핑에서 controller 키가 없는 경우 예외 처리
        if (!isset($this->container['controller'])) {
            $this->debug('[DEBUG] Controller key is missing in the container.');
            // 적절한 예외 처리 또는 기본 컨트롤러 지정
            // throw new \Exception('Controller key is missing in the container.', 500);
        }
        
        // 필요한 컴포넌트만 초기화
        if (isset($this->config['database'])) {
            $this->debug('[DEBUG] Initializing Database');
            $this->container['db'] = Database::getInstance();
        }
        
        // 세션 초기화
        $this->debug('[DEBUG] Initializing Session');
        $this->container['session'] = Session::getInstance();
        
        // 인증 관리자 초기화
        $this->debug('[DEBUG] Initializing Auth');
        $this->container['auth'] = Auth::getInstance();

        // 가비지 컬렉션 강제 실행
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    public function run()
    {
        $this->debug('[DEBUG] Application::run() called');
        try {
            $this->initialize();
            
            $this->memoryManager->createCheckpoint('request_start');
            
            // 라우터를 통해 요청 처리
            $this->debug('[DEBUG] Dispatching request: ' . $this->request->getMethod() . ' ' . $this->request->getPath());
            $result = $this->router->dispatch($this->request->getMethod(), $this->request->getPath());
            
            if ($result === null) {
                $this->debug('[DEBUG] Route not found');
                throw new \Exception('Route not found', 404);
            }

            // 응답 처리
            if ($result instanceof Response) {
                $this->debug('[DEBUG] Sending Response object');
                $result->send();
            } else if (is_array($result)) {
                $this->debug('[DEBUG] Sending array response');
                $this->response
                    ->setContentType('application/json')
                    ->setStatusCode(200)
                    ->setContent(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    ->send();
            } else if (is_string($result)) {
                $this->debug('[DEBUG] Sending string response');
                $this->response
                    ->setContentType('text/html; charset=UTF-8')
                    ->setStatusCode(200)
                    ->setContent($result)
                    ->send();
            } else {
                $this->debug('[DEBUG] Invalid response type');
                throw new \Exception('Invalid response type', 500);
            }

            $this->memoryManager->createCheckpoint('request_end');
            $this->logMemoryUsage();
        } catch (\Exception $e) {
            // Clear any previous output
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->debug('[DEBUG] Exception caught in Application::run(): ' . $e->getMessage());
            $this->handleError($e);
            exit;
        }
    }

    private function logMemoryUsage()
    {
        $memoryStats = $this->memoryManager->getMemoryStats();
        $requestDiff = $this->memoryManager->getCheckpointDiff('request_start');
        
        error_log(sprintf(
            "Memory Usage - Current: %s, Peak: %s, Request Delta: %s",
            $memoryStats['current'],
            $memoryStats['peak'],
            $requestDiff['memory_diff']
        ));
    }

    public function getMemoryStats()
    {
        return $this->memoryManager->getMemoryStats();
    }

    private function handleError(\Exception $e)
    {
        $statusCode = $e->getCode() ?: 500;
        $message = $e->getMessage() ?: 'Internal Server Error';
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        if ($this->request->isAjax() || $this->request->isJson() || strpos($this->request->getPath(), '/api/') === 0) {
            $this->response
                ->setContentType('application/json')
                ->setStatusCode($statusCode)
                ->setContent(json_encode([
                    'error' => true,
                    'message' => $message,
                    'code' => $statusCode
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->send();
        } else {
            $this->response
                ->setContentType('text/html; charset=UTF-8')
                ->setStatusCode($statusCode)
                ->setContent($this->renderErrorPage($statusCode, $message))
                ->send();
        }
    }

    private function renderErrorPage($statusCode, $message)
    {
        $template = PROJECT_ROOT . '/templates/error.php';
        if (file_exists($template)) {
            ob_start();
            include $template;
            return ob_get_clean();
        }
        
        // 기본 에러 페이지 HTML
        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$statusCode}</title>
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
        <h1>Error {$statusCode}</h1>
        <p>{$message}</p>
        <a href="/" class="home-link">홈으로 돌아가기</a>
    </div>
</body>
</html>
HTML;
    }

    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function __destruct()
    {
        // 리소스 정리
        if ($this->initialized) {
            $this->memoryManager->createCheckpoint('cleanup_start');
            
            // 객체 참조 해제
            $this->errorHandler = null;
            $this->router = null;
            $this->config = [];
            
            // 컨테이너 객체 정리
            foreach ($this->container as $key => $value) {
                if (method_exists($value, '__destruct')) {
                    $value->__destruct();
                }
                $this->container[$key] = null;
            }
            $this->container = [];
            
            $this->memoryManager->createCheckpoint('cleanup_end');
            
            // 가비지 컬렉션 강제 실행
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }
} 