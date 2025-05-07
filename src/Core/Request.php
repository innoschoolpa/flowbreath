<?php

namespace App\Core;

class Request
{
    private $get;
    private $post;
    private $server;
    private $files;
    private $cookies;
    private $headers;
    private $rawInput;
    private $jsonData;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        $this->get = $this->sanitizeArray($_GET);
        $this->post = $this->sanitizeArray($_POST);
        $this->server = $this->sanitizeArray($_SERVER);
        $this->files = $this->sanitizeFiles($_FILES);
        $this->cookies = $this->sanitizeArray($_COOKIE);
        $this->headers = $this->sanitizeArray($this->getAllHeaders());
        $this->rawInput = file_get_contents('php://input');
        $this->jsonData = $this->parseJsonInput();
    }

    private function sanitizeArray($array)
    {
        if (!is_array($array)) {
            return [];
        }

        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value);
            } else {
                $result[$key] = $this->sanitizeValue($value);
            }
        }
        return $result;
    }

    private function sanitizeFiles($files)
    {
        if (!is_array($files)) {
            return [];
        }

        $result = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $result[$key] = $this->sanitizeArray($file);
            } else {
                $result[$key] = $this->sanitizeValue($file);
            }
        }
        return $result;
    }

    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    private function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    private function parseJsonInput()
    {
        if (empty($this->rawInput)) {
            return null;
        }

        $contentType = $this->getHeader('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            return null;
        }

        $data = json_decode($this->rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $this->sanitizeArray($data);
    }

    public function getMethod()
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath()
    {
        $path = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return rtrim($path, '/') ?: '/';
    }

    public function getQuery($key = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? null;
    }

    public function getPost($key = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? null;
    }

    public function getJson($key = null)
    {
        if ($key === null) {
            return $this->jsonData;
        }
        return $this->jsonData[$key] ?? null;
    }

    public function getFile($key)
    {
        return $this->files[$key] ?? null;
    }

    public function getCookie($key)
    {
        return $this->cookies[$key] ?? null;
    }

    public function getHeader($key)
    {
        return $this->headers[$key] ?? null;
    }

    public function isAjax()
    {
        return isset($this->headers['X-Requested-With']) && 
               strtolower($this->headers['X-Requested-With']) === 'xmlhttprequest';
    }

    public function isJson()
    {
        return strpos($this->getHeader('Content-Type'), 'application/json') !== false;
    }

    public function getIp()
    {
        $ip = $this->server['HTTP_CLIENT_IP'] ?? 
              $this->server['HTTP_X_FORWARDED_FOR'] ?? 
              $this->server['REMOTE_ADDR'] ?? 
              null;

        if ($ip && strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }

        return $ip;
    }

    public function getUserAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    public function getReferer()
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    public function getRawInput()
    {
        return $this->rawInput;
    }
} 