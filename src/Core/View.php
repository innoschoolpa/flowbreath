<?php

namespace App\Core;

class View
{
    private $layout = 'default';
    private $viewPath;

    public function __construct()
    {
        $this->viewPath = dirname(__DIR__, 2) . '/src/View/';
    }

    public function render($template, $data = [])
    {
        $templateFile = $this->viewPath . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("View template not found: {$template}");
        }

        ob_start();
        extract($data);
        require $templateFile;
        $content = ob_get_clean();

        $layoutFile = $this->viewPath . 'layouts/' . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            $content = ob_get_clean();
        }

        $response = new Response();
        $response->setContentType('text/html; charset=UTF-8');
        $response->setStatusCode(200);
        $response->setContent($content);
        return $response;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
} 