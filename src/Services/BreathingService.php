<?php

namespace App\Services;

use App\Models\BreathingPattern;
use App\Models\BreathingSession;
use App\Models\UserSettings;

class BreathingService
{
    private $patterns = [
        '4-7-8' => [
            'id' => '4-7-8',
            'name' => '4-7-8 호흡법',
            'description' => '4초 들숨, 7초 참기, 8초 날숨',
            'phases' => [
                ['type' => 'inhale', 'duration' => 4],
                ['type' => 'hold', 'duration' => 7],
                ['type' => 'exhale', 'duration' => 8]
            ]
        ],
        'box' => [
            'id' => 'box',
            'name' => '박스 호흡법',
            'description' => '4초 들숨, 4초 참기, 4초 날숨, 4초 참기',
            'phases' => [
                ['type' => 'inhale', 'duration' => 4],
                ['type' => 'hold', 'duration' => 4],
                ['type' => 'exhale', 'duration' => 4],
                ['type' => 'hold', 'duration' => 4]
            ]
        ]
    ];

    private $sessions = [];
    private $settings = [];

    public function getPatterns()
    {
        return array_values($this->patterns);
    }

    public function startSession($patternId, $duration, $sound, $vibration)
    {
        if (!isset($this->patterns[$patternId])) {
            throw new \InvalidArgumentException('Invalid pattern ID');
        }

        $sessionId = uniqid('session_', true);
        $this->sessions[$sessionId] = [
            'id' => $sessionId,
            'pattern' => $patternId,
            'duration' => $duration,
            'sound' => $sound,
            'vibration' => $vibration,
            'started_at' => date('c'),
            'status' => 'in_progress',
            'current_phase' => 0,
            'current_phase_start' => time()
        ];

        return [
            'session_id' => $sessionId,
            'pattern' => $patternId,
            'duration' => $duration,
            'started_at' => $this->sessions[$sessionId]['started_at']
        ];
    }

    public function getSessionStatus($session_id)
    {
        if (!isset($this->sessions[$session_id])) {
            throw new \InvalidArgumentException('Session not found');
        }

        $session = $this->sessions[$session_id];
        $pattern = $this->patterns[$session['pattern']];
        $currentPhase = $pattern['phases'][$session['current_phase']];
        $elapsed = time() - $session['current_phase_start'];
        $remaining = $currentPhase['duration'] - $elapsed;

        if ($remaining <= 0) {
            $session['current_phase'] = ($session['current_phase'] + 1) % count($pattern['phases']);
            $session['current_phase_start'] = time();
            $currentPhase = $pattern['phases'][$session['current_phase']];
            $remaining = $currentPhase['duration'];
            $this->sessions[$session_id] = $session;
        }

        $nextPhase = $pattern['phases'][($session['current_phase'] + 1) % count($pattern['phases'])];

        return [
            'session_id' => $session_id,
            'status' => $session['status'],
            'current_phase' => [
                'type' => $currentPhase['type'],
                'duration' => $currentPhase['duration'],
                'time_remaining' => $remaining
            ],
            'next_phase' => [
                'type' => $nextPhase['type'],
                'duration' => $nextPhase['duration']
            ],
            'progress' => $elapsed / $session['duration'],
            'visual_guide' => $this->getVisualGuide($currentPhase['type'])
        ];
    }

    public function endSession($sessionId)
    {
        error_log("BreathingService::endSession called with ID: " . $sessionId);
        
        if (empty($sessionId)) {
            throw new \InvalidArgumentException('Session ID is required');
        }

        if (!isset($this->sessions[$sessionId])) {
            error_log("Session not found: " . $sessionId);
            throw new \InvalidArgumentException('Session not found');
        }

        error_log("Found session: " . json_encode($this->sessions[$sessionId]));
        
        $this->sessions[$sessionId]['status'] = 'completed';
        $this->sessions[$sessionId]['ended_at'] = date('c');
        
        $sessionData = [
            'session_id' => $sessionId,
            'status' => 'completed',
            'ended_at' => $this->sessions[$sessionId]['ended_at'],
            'duration' => $this->sessions[$sessionId]['duration'],
            'pattern' => $this->sessions[$sessionId]['pattern']
        ];
        
        error_log("Session data to return: " . json_encode($sessionData));
        
        // 세션 데이터 반환
        return $sessionData;
    }

    public function getSessionGuide($sessionId)
    {
        $status = $this->getSessionStatus($sessionId);
        $session = $this->sessions[$sessionId];
        $elapsed = time() - strtotime($session['started_at']);
        $remaining = $session['duration'] - $elapsed;

        return [
            'session_id' => $sessionId,
            'current_phase' => $status['current_phase'],
            'visual_guide' => array_merge(
                $status['visual_guide'],
                ['animation' => $this->getAnimation($status['current_phase']['type'])]
            ),
            'timer' => [
                'total' => $session['duration'],
                'remaining' => max(0, $remaining),
                'formatted' => sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60)
            ]
        ];
    }

    public function getUserSettings()
    {
        return [
            'sound' => [
                'enabled' => true,
                'volume' => 0.8,
                'type' => 'bell'
            ],
            'vibration' => [
                'enabled' => true,
                'intensity' => 'medium'
            ],
            'visual' => [
                'theme' => 'light',
                'animation_speed' => 'normal',
                'show_timer' => true
            ]
        ];
    }

    public function updateUserSettings($data)
    {
        $this->settings = array_merge($this->getUserSettings(), $data);
        return $this->settings;
    }

    private function getVisualGuide($phaseType)
    {
        $guides = [
            'inhale' => ['circle_size' => 1.5, 'color' => '#4CAF50'],
            'hold' => ['circle_size' => 1.5, 'color' => '#2196F3'],
            'exhale' => ['circle_size' => 0.5, 'color' => '#FFC107']
        ];

        return $guides[$phaseType] ?? $guides['inhale'];
    }

    private function getAnimation($phaseType)
    {
        $animations = [
            'inhale' => 'expand',
            'hold' => 'pulse',
            'exhale' => 'contract'
        ];

        return $animations[$phaseType] ?? 'expand';
    }
} 