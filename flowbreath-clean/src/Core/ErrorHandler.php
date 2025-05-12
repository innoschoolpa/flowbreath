<?php

namespace App\Core;

class ErrorHandler
{
    private static $instance = null;
    private $logger;
    private $displayErrors;

    protected function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->displayErrors = ini_get('display_errors');
        $this->initialize();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize()
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'type' => $this->getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'code' => 500
        ];

        $this->logError($error);
        $this->displayError($error);
        return true;
    }

    public function handleException($exception)
    {
        $code = $exception->getCode();
        if ($code < 100 || $code > 599) {
            $code = 500;
        }

        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'code' => $code
        ];

        $this->logError($error);
        $this->displayError($error);
    }

    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && $this->isFatalError($error['type'])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private function getErrorType($type)
    {
        switch($type) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }

    private function isFatalError($type)
    {
        return in_array($type, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR
        ]);
    }

    private function logError($error)
    {
        $message = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (isset($error['trace'])) {
            $message .= "\nStack trace:\n" . $error['trace'];
        }

        $this->logger->error($message);
    }

    private function displayError($error)
    {
        if (php_sapi_name() === 'cli') {
            $this->displayCliError($error);
        } else {
            $this->displayWebError($error);
        }
    }

    private function displayCliError($error)
    {
        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }

        echo "\nError: {$error['message']}\n";
        echo "Type: {$error['type']}\n";
        echo "File: {$error['file']}\n";
        echo "Line: {$error['line']}\n";
        if (isset($error['trace'])) {
            echo "\nStack trace:\n{$error['trace']}\n";
        }
        exit;
    }

    private function displayWebError($error)
    {
        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            $statusCode = $error['code'] ?? 500;
            $statusText = $this->getHttpStatusText($statusCode);
            header("HTTP/1.1 {$statusCode} {$statusText}");
        }

        // Check if this is an API request
        $isApiRequest = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
        $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isJsonRequest = isset($_SERVER['HTTP_ACCEPT']) && 
                        strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if ($isApiRequest || $isAjaxRequest || $isJsonRequest) {
            header('Content-Type: application/json');
            $response = [
                'error' => true,
                'message' => $error['message'],
                'type' => $error['type']
            ];

            if ($this->displayErrors) {
                $response['file'] = $error['file'];
                $response['line'] = $error['line'];
                if (isset($error['trace'])) {
                    $response['trace'] = $error['trace'];
                }
            }

            echo json_encode($response);
        } else {
            header('Content-Type: text/html; charset=UTF-8');
            echo $this->renderErrorPage($error);
        }
        exit;
    }

    private function renderErrorPage($error)
    {
        $statusCode = $error['code'] ?? 500;
        $message = $error['message'];
        $title = "Error {$statusCode}";

        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
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
        <h1>{$title}</h1>
        <p>{$message}</p>
        <a href="/" class="home-link">홈으로 돌아가기</a>
    </div>
</body>
</html>
HTML;
    }

    private function getHttpStatusText($code)
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];

        return $statusTexts[$code] ?? 'Unknown Status';
    }
} 