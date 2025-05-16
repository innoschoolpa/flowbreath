<?php

namespace App\Controllers;

use App\Services\BreathingService;
use App\Models\BreathingPattern;
use App\Models\BreathingSession;
use App\Models\UserSettings;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class BreathingController
{
    private $breathingService;
    private $request;
    private $response;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
        $data = $this->request->getJson();
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
        $data = $this->request->getJson();
        $settings = $this->breathingService->updateUserSettings($data);
        return $this->jsonResponse([
            'success' => true,
            'data' => $settings
        ]);
    }

    private function jsonResponse($data)
    {
        $response = new Response();
        $response->setContentType('application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
} 