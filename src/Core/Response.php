<?php

namespace App\Core;

class Response
{
    private $headers = [];
    private $statusCode = 200;
    private $content = '';
    private $sent = false;

    public function setHeader($name, $value)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify headers after response has been sent');
        }
        $this->headers[$name] = $value;
        return $this;
    }

    public function setStatusCode($code)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify status code after response has been sent');
        }
        $this->statusCode = (int)$code;
        return $this;
    }

    public function setContent($content)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify content after response has been sent');
        }
        $this->content = $content;
        return $this;
    }

    public function setContentType($type)
    {
        return $this->setHeader('Content-Type', $type);
    }

    private function clearOutputBuffer()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    public function json($data, $statusCode = 200)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot send response after it has been sent');
        }

        $this->clearOutputBuffer();
        $this->setContentType('application/json');
        $this->setStatusCode($statusCode);
        $this->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // $this->send(); // Remove immediate send
        return $this;
    }

    public function view($view, $data = [], $statusCode = 200)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot send response after it has been sent');
        }

        $viewPath = __DIR__ . '/../View/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$view}");
        }

        $this->clearOutputBuffer();
        $this->setStatusCode($statusCode);
        $this->setContentType('text/html; charset=UTF-8');
        
        ob_start();
        extract($data);
        require $viewPath;
        $this->setContent(ob_get_clean());
        // $this->send(); // 주석처리
        return $this;
    }

    public function redirect($url, $statusCode = 302)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot send response after it has been sent');
        }

        $this->clearOutputBuffer();
        $this->setHeader('Location', $url);
        $this->setStatusCode($statusCode);
        // $this->send(); // Remove immediate send
        return $this;
    }

    public function error($message, $statusCode = 400)
    {
        $this->json([
            'error' => true,
            'message' => $message,
            'code' => $statusCode
        ], $statusCode);
    }

    public function success($message, $data = null)
    {
        $response = [
            'error' => false,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response);
    }

    public function send()
    {
        if ($this->sent) {
            throw new \RuntimeException('Response has already been sent');
        }

        $this->clearOutputBuffer();

        if (!headers_sent()) {
            foreach ($this->headers as $name => $value) {
                header("$name: $value", true);
            }
            http_response_code((int)$this->statusCode);
        }

        echo $this->content;
        $this->sent = true;
    }

    public function isSent()
    {
        return $this->sent;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function __toString()
    {
        return (string)$this->content;
    }
} 