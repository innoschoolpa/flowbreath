<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $patterns = [];
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function add($method, $path, $handler, $middleware = [])
    {
        $pattern = $this->convertPathToPattern($path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    private function convertPathToPattern($path)
    {
        if ($path === '*') {
            return '.*';
        }
        // 숫자와 문자를 모두 허용하도록 패턴 수정하고, 경로 구분자(/)를 이스케이프
        $pattern = str_replace('/', '\/', $path);
        return preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^\/]+)', $pattern);
    }

    public function resolve($method, $path)
    {
        $method = strtoupper($method);
        $matchedRoute = null;
        
        error_log("[DEBUG] Resolving route: {$method} {$path}");
        
        foreach ($this->routes as $route) {
            error_log("[DEBUG] Checking route: {$route['method']} {$route['path']}");
            error_log("[DEBUG] Pattern: {$route['pattern']}");
            
            if ($route['method'] !== $method) {
                continue;
            }

            if ($route['path'] === '*') {
                $matchedRoute = $route;
                continue;
            }

            // 경로 패턴 매칭 시 대소문자 구분 없이 비교하고, 경로 구분자(/)를 이스케이프
            $pattern = '#^' . $route['pattern'] . '$#i';
            error_log("[DEBUG] Testing pattern: {$pattern}");
            
            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                error_log("[DEBUG] Route matched: {$route['method']} {$route['path']}");
                error_log("[DEBUG] Matched params: " . json_encode($params));
                return [
                    'handler' => $route['handler'],
                    'params' => $params,
                    'middleware' => $route['middleware']
                ];
            }
        }

        // 와일드카드 라우트가 있고 다른 라우트가 매칭되지 않은 경우
        if ($matchedRoute !== null) {
            error_log("[DEBUG] Using wildcard route");
            return [
                'handler' => $matchedRoute['handler'],
                'params' => [],
                'middleware' => $matchedRoute['middleware']
            ];
        }

        error_log("[DEBUG] No route found for: {$method} {$path}");
        return null;
    }

    public function dispatch($method, $path)
    {
        try {
            $route = $this->resolve($method, $path);
            
            if ($route === null) {
                throw new \Exception("Route not found", 404);
            }

            $handler = $route['handler'];
            $params = $route['params'];

            if (is_array($handler)) {
                [$class, $method] = $handler;
                if (!class_exists($class)) {
                    throw new \Exception("Controller class {$class} not found", 500);
                }

                // Create controller instance with Request object
                $controller = new $class($this->request);
                
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method {$method} not found in controller {$class}", 500);
                }

                // Log which controller and method are being dispatched to
                error_log("[ROUTER] Dispatching to: {$class}::{$method}");

                // Get method parameters using reflection
                $reflection = new \ReflectionMethod($controller, $method);
                $methodParams = $reflection->getParameters();
                
                // Prepare arguments array
                $args = [];
                foreach ($methodParams as $param) {
                    $paramName = $param->getName();
                    $paramType = $param->getType();
                    
                    // If parameter is Request type, pass the request object
                    if ($paramType && $paramType->getName() === 'App\\Core\\Request') {
                        $args[] = $this->request;
                    }
                    // Otherwise, try to get the value from route parameters
                    else if (isset($params[$paramName])) {
                        $args[] = $params[$paramName];
                    }
                    // If parameter is optional, use default value
                    else if ($param->isOptional()) {
                        $args[] = $param->getDefaultValue();
                    }
                    // Required parameter is missing
                    else {
                        throw new \Exception("Required parameter {$paramName} is missing", 400);
                    }
                }
                
                return $controller->$method(...$args);
            }

            return $handler(...array_values($params));
        } catch (\Exception $e) {
            error_log("[ERROR] Router dispatch error: " . $e->getMessage());
            error_log($e->getTraceAsString());
            return $this->renderErrorPage($e);
        }
    }

    private function renderErrorPage(\Exception $e)
    {
        $statusCode = $e->getCode() ?: 500;
        $message = $e->getMessage();
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

    public function get($path, $controller, $action)
    {
        $this->add('GET', $path, [$controller, $action]);
        return $this;
    }

    public function post($path, $controller, $action)
    {
        $this->add('POST', $path, [$controller, $action]);
        return $this;
    }

    public function delete($path, $controller, $action)
    {
        $this->add('DELETE', $path, [$controller, $action]);
        return $this;
    }

    public function put($path, $controller, $action)
    {
        $this->add('PUT', $path, [$controller, $action]);
        return $this;
    }

    public function patch($path, $controller, $action)
    {
        $this->add('PATCH', $path, [$controller, $action]);
        return $this;
    }

    // API Routes
    public function addRoute($method, $path, $handler)
    {
        $this->add($method, $path, $handler);
    }
} 