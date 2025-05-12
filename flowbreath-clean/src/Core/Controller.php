<?php

namespace App\Core;

class Controller
{
    protected $request;
    protected $response;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? new Request();
        $this->response = new Response();
    }

    protected function view(string $view, array $data = []): Response
    {
        // Extract data to make variables available in view
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewPath = __DIR__ . '/../View/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        require $viewPath;

        // Get the contents of the buffer
        $content = ob_get_clean();

        // Set the content and return response
        $this->response->setContent($content);
        return $this->response;
    }

    protected function json($data, int $status = 200): Response
    {
        $this->response->setContentType('application/json');
        $this->response->setStatusCode($status);
        $this->response->setContent(json_encode($data));
        return $this->response;
    }

    protected function redirect(string $url): Response
    {
        $this->response->setStatusCode(302);
        $this->response->setHeader('Location', $url);
        return $this->response;
    }
} 