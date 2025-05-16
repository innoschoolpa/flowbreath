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
            return ob_get_clean();
        }

        return $content;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
} 