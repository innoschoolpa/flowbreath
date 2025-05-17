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
        try {
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
        } catch (\Exception $e) {
            error_log("View render error: " . $e->getMessage());
            $response = new Response();
            $response->setContentType('text/html; charset=UTF-8');
            $response->setStatusCode(500);
            $response->setContent("An error occurred while rendering the view.");
            return $response;
        }
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
} 