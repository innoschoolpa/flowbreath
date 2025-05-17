<?php

namespace App\Controllers;

use App\Services\BreathingService;
use App\Models\BreathingPattern;
use App\Models\BreathingSession;
use App\Models\UserSettings;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Language;

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
        $language = Language::getInstance();
        $view = new View();
        return $view->render('breathing', ['language' => $language]);
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
            $data['vibration'] ?? true,
            $data['inhaleTime'] ?? null,
            $data['exhaleTime'] ?? null
        );

        return $this->jsonResponse([
            'success' => true,
            'data' => $session
        ]);
    }

    public function getSessionStatus($session_id)
    {
        $status = $this->breathingService->getSessionStatus($session_id);
        return $this->jsonResponse([
            'success' => true,
            'data' => $status
        ]);
    }

    public function endSession($session_id)
    {
        try {
            if (empty($session_id)) {
                throw new \InvalidArgumentException('Session ID is required');
            }

            error_log("Attempting to end session: " . $session_id);
            $sessionData = $this->breathingService->endSession($session_id);
            error_log("Session ended successfully: " . json_encode($sessionData));

            return $this->jsonResponse([
                'success' => true,
                'data' => $sessionData,
                'message' => 'Session ended successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Invalid session ID: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            error_log("Error ending session: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to end session: ' . $e->getMessage()
            ], 500);
        }
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