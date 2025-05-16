<?php

namespace App\Controllers;

use App\Services\BreathingService;
use App\Models\BreathingPattern;
use App\Models\BreathingSession;
use App\Models\UserSettings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Core\View;

class BreathingController
{
    private $breathingService;
    private $request;
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->breathingService = new BreathingService();
    }

    public function index()
    {
        $view = new View();
        return $view->render('breathing');
    }

    public function getPatterns()
    {
        $patterns = $this->breathingService->getPatterns();
        return $this->jsonResponse([
            'success' => true,
            'data' => ['patterns' => $patterns]
        ]);
    }

    public function startSession()
    {
        $data = $this->request->getParsedBody();
        $session = $this->breathingService->startSession(
            $data['pattern'],
            $data['duration'] ?? 300,
            $data['sound'] ?? true,
            $data['vibration'] ?? true
        );

        return $this->jsonResponse([
            'success' => true,
            'data' => $session
        ]);
    }

    public function getSessionStatus($sessionId)
    {
        $status = $this->breathingService->getSessionStatus($sessionId);
        return $this->jsonResponse([
            'success' => true,
            'data' => $status
        ]);
    }

    public function endSession($sessionId)
    {
        $this->breathingService->endSession($sessionId);
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Session ended successfully'
        ]);
    }

    public function getSessionGuide($sessionId)
    {
        $guide = $this->breathingService->getSessionGuide($sessionId);
        return $this->jsonResponse([
            'success' => true,
            'data' => $guide
        ]);
    }

    public function getSettings()
    {
        $settings = $this->breathingService->getUserSettings();
        return $this->jsonResponse([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function updateSettings()
    {
        $data = $this->request->getParsedBody();
        $settings = $this->breathingService->updateUserSettings($data);
        return $this->jsonResponse([
            'success' => true,
            'data' => $settings
        ]);
    }

    private function jsonResponse($data)
    {
        $this->response->getBody()->write(json_encode($data));
        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
} 