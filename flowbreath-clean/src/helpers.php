<?php
// src/helpers.php
require_once __DIR__ . '/../vendor/autoload.php';
// use Parsedown\Parsedown; // 네임스페이스 없는 라이브러리이므로 삭제

// 애플리케이션 전반에서 사용될 수 있는 헬퍼(도우미) 함수들을 정의합니다.

// 함수 중복 정의 방지 (다른 파일에서 이미 정의했을 경우를 대비)
if (!function_exists('load_view')) {
    /**
     * 지정된 뷰 파일을 로드하고 데이터를 전달합니다.
     *
     * @param string $viewPath 'src/View/' 디렉토리 기준의 뷰 파일 경로 (확장자 .php 제외) 예: 'auth/login'
     * @param array $data 뷰 파일 내에서 사용할 데이터 배열 (키가 변수명이 됨)
     * @return void
     * @throws Exception 뷰 파일을 찾을 수 없는 경우
     */
    function load_view(string $viewPath, array $data = []): void {
        // $data 배열의 키를 현재 심볼 테이블의 변수로 가져옵니다.
        // 예를 들어 $data = ['title' => 'My Page'] 이면, 뷰 파일 내에서 $title 변수를 사용할 수 있게 됩니다.
        // 보안에 유의하여 사용해야 합니다. Controller에서 전달하는 데이터만 포함되도록 합니다.
        extract($data);

        // 뷰 파일의 실제 경로를 구성합니다.
        // 이 helpers.php 파일이 src 폴더에 있다고 가정하고, View 폴더는 같은 레벨에 있다고 가정합니다.
        $filePath = __DIR__ . '/View/' . $viewPath . '.php';

        // 뷰 파일이 존재하는지 확인합니다.
        if (file_exists($filePath)) {
            // 뷰 파일을 포함하여 실행합니다. (뷰 파일 내에서 $data의 키들이 변수로 사용 가능)
            include $filePath;
        } else {
            // 뷰 파일을 찾을 수 없을 경우 오류 로그를 남기고 예외를 발생시킵니다.
            $errorMessage = "View file not found at path: " . $filePath;
            error_log($errorMessage);
            // 실제 서비스에서는 사용자 친화적인 오류 페이지를 보여주는 것이 좋습니다.
            // 이 함수를 호출한 Controller에서 try-catch로 처리하도록 예외를 던질 수 있습니다.
            throw new Exception($errorMessage);
            // 또는 직접 오류 페이지 로드 (덜 권장됨)
            // http_response_code(404);
            // include __DIR__ . '/View/error/404.php';
            // exit;
        }
    }
}

if (!function_exists('redirect')) {
    /**
     * 지정된 URL로 사용자를 리디렉션합니다.
     *
     * @param string $path 리디렉션할 경로 (예: '/login', '/dashboard') - 웹 루트 기준 절대 경로 권장
     * @return void
     */
    function redirect(string $path): void {
        // TODO: 설정 파일(config/app.php)의 base_url을 사용하여 완전한 URL을 생성하는 것이 더 좋습니다.
        // 예: $config = require ...; $baseUrl = $config['base_url']; header("Location: " . $baseUrl . $path);

        // 현재는 간단하게 웹 루트 기준 절대 경로로 리디렉션합니다.
        // .htaccess를 사용하여 public 디렉토리로 라우팅하는 구조 기준입니다.
        if (strpos($path, '/') !== 0) {
            $path = '/' . $path; // 슬래시로 시작하지 않으면 추가
        }
        header("Location: " . $path);
        exit; // 리디렉션 후 스크립트 실행을 중단하는 것이 매우 중요합니다.
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * 사용자가 현재 로그인 상태인지 확인합니다.
     * 세션에 user_id가 설정되어 있는지 확인합니다.
     *
     * @return bool 로그인 상태이면 true, 아니면 false
     */
    function is_logged_in(): bool {
        // 세션이 시작되었는지 확인하고, 시작되지 않았다면 시작합니다.
        if (session_status() === PHP_SESSION_NONE) {
            // @ 로 오류 출력을 억제하고 상태만 확인 (다른 곳에서 이미 session_start() 했을 수 있음)
            @session_start();
        }
        // $_SESSION 배열에 'user_id' 키가 존재하고 그 값이 비어있지 않은지 확인합니다.
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    /**
     * 현재 로그인한 사용자가 관리자 권한을 가지고 있는지 확인합니다.
     * 세션에 role이 'admin'으로 설정되어 있는지 확인합니다.
     *
     * @return bool 관리자이면 true, 아니면 false
     */
    function is_admin(): bool {
        // 세션 상태 확인
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        // 로그인 상태이고(user_id 존재), role 값이 'admin'인지 확인합니다.
        return isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('base_url')) {
    /**
     * 설정 파일에 정의된 기본 URL을 반환하거나, 경로를 추가하여 전체 URL을 생성합니다.
     * CSS, JS, 이미지 파일 등 에셋 경로 생성에 유용합니다.
     *
     * @param string $path 기본 URL 뒤에 추가할 경로 (선택 사항, 슬래시(/)로 시작해야 함)
     * @return string 전체 URL
     */
    function base_url(string $path = ''): string {
        // 설정 파일 로드 (서버 환경에 맞게 경로 수정)
        $configPath = __DIR__ . '/../config/app.php';
        if (!file_exists($configPath)) {
            error_log("Config file not found for base_url helper: " . $configPath);
            return '/'; // 기본값 반환
        }
        $config = require $configPath;
        $baseUrl = rtrim($config['base_url'] ?? '', '/'); // 마지막 슬래시 제거
        $path = '/' . ltrim($path, '/'); // 경로가 슬래시로 시작하도록 보정하고 맨 앞 슬래시 추가
        // 이중 슬래시 방지
        if ($path === '//') {
            $path = '/';
        }
        return $baseUrl . $path;
    }
}

if (!function_exists('dd')) {
    /**
     * 디버깅 함수 (Die and Dump)
     * 변수 내용을 보기 좋게 화면에 출력하고 스크립트 실행을 중단합니다.
     * 개발 중에만 사용하고, 실제 서비스 코드에서는 제거해야 합니다.
     * @param mixed ...$vars 덤프할 변수들 (가변 인자)
     */
    function dd(...$vars): void {
        echo '<pre style="background:#222;color:#fff;padding:10px;border-radius:6px;z-index:9999;position:relative;">';
        foreach ($vars as $var) var_dump($var);
        echo '</pre>';
        die();
    }
}

if (!function_exists('e')) {
    /**
     * htmlspecialchars()의 축약 함수 (보안: XSS 방지)
     * 출력을 이스케이프하여 크로스 사이트 스크립팅 공격을 방지합니다.
     * @param string|null $string 이스케이프할 문자열
     * @return string 이스케이프된 문자열
     */
    function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function markdown_to_html($markdown) {
    if ($markdown === null || $markdown === '') {
        return '';
    }
    $parsedown = new Parsedown();
    return $parsedown->text($markdown);
}

if (!function_exists('is_html')) {
    function is_html($string) {
        return $string != strip_tags($string);
    }
}

// 필요에 따라 다른 공통 함수들을 여기에 추가할 수 있습니다.
// 예: CSRF 토큰 생성/검증 함수, 날짜 포맷 함수 등

if (class_exists('Parsedown')) {
    error_log('Parsedown 클래스가 정상적으로 로드되었습니다.');
} else {
    error_log('Parsedown 클래스가 로드되지 않았습니다!');
}

try {
    $parsedown = new Parsedown();
    error_log('Parsedown 인스턴스 생성 성공');
} catch (Throwable $e) {
    error_log('Parsedown 인스턴스 생성 실패: ' . $e->getMessage());
}

if (!function_exists('base_path')) {
    /**
     * Get the base path of the project.
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '') {
        return PROJECT_ROOT . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage path of the project.
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '') {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path of the project.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '') {
        return base_path('config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the public path of the project.
     *
     * @param string $path
     * @return string
     */
    function public_path($path = '') {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the resources path of the project.
     *
     * @param string $path
     * @return string
     */
    function resource_path($path = '') {
        return base_path('resources') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the application path of the project.
     *
     * @param string $path
     * @return string
     */
    function app_path($path = '') {
        return base_path('src') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null) {
        static $config = [];
        
        if (is_null($key)) {
            return $config;
        }
        
        if (isset($config[$key])) {
            return $config[$key];
        }
        
        $parts = explode('.', $key);
        $filename = array_shift($parts);
        
        if (!isset($config[$filename])) {
            $path = config_path($filename . '.php');
            if (file_exists($path)) {
                $config[$filename] = require $path;
            }
        }
        
        $current = $config[$filename] ?? [];
        
        foreach ($parts as $part) {
            if (!is_array($current) || !isset($current[$part])) {
                return $default;
            }
            $current = $current[$part];
        }
        
        return $current;
    }
}

if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    function view($view, $data = []) {
        $path = resource_path('views' . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php');
        
        if (!file_exists($path)) {
            throw new Exception("View [{$view}] not found.");
        }
        
        extract($data);
        
        ob_start();
        include $path;
        return ob_get_clean();
    }
}

if (!function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function session($key = null, $default = null) {
        if (is_null($key)) {
            return $_SESSION;
        }
        
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        
        return $default;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old($key, $default = '') {
        return session('_old_input.' . $key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token.
     *
     * @return string
     */
    function csrf_token() {
        $token = session('_token');
        
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['_token'] = $token;
        }
        
        return $token;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return string
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a form field for spoofing the HTTP verb used by forms.
     *
     * @param string $method
     * @return string
     */
    function method_field($method) {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @return string
     */
    function asset($path) {
        return '/assets/' . ltrim($path, '/');
    }
}

// view() 함수는 더 이상 직접 사용하지 않고, 컨트롤러에서는 반드시 $this->response->view() 또는 $this->view()를 사용하세요.
// load_view()는 내부적으로만 사용하세요.

?>
