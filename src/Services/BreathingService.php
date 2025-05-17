<?php

namespace App\Services;

use App\Models\BreathingPattern;
use App\Models\BreathingSession;
use App\Models\UserSettings;

class BreathingService
{
    private $patterns = [
        'danjeon' => [
            'id' => 'danjeon',
            'name' => '단전 호흡',
            'description' => '들숨-날숨 반복',
            'phases' => [
                ['type' => 'inhale', 'duration' => 4],
                ['type' => 'exhale', 'duration' => 4]
            ]
        ],
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
                ['type' => 'hold_in', 'duration' => 4],
                ['type' => 'exhale', 'duration' => 4],
                ['type' => 'hold_out', 'duration' => 4]
            ]
        ]
    ];

    private $sessions = [];
    private $settings = [];
    private $sessionsFile;

    public function __construct()
    {
        $this->sessionsFile = __DIR__ . '/../../storage/sessions.json';
        $this->loadSessions();
    }

    private function loadSessions()
    {
        if (file_exists($this->sessionsFile)) {
            $content = file_get_contents($this->sessionsFile);
            if ($content !== false) {
                $this->sessions = json_decode($content, true) ?? [];
            }
        }
    }

    private function saveSessions()
    {
        $dir = dirname($this->sessionsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->sessionsFile, json_encode($this->sessions));
    }

    public function getPatterns()
    {
        return array_values($this->patterns);
    }

    public function startSession($patternId, $duration, $sound, $vibration, $inhaleTime = null, $exhaleTime = null)
    {
        if (!isset($this->patterns[$patternId])) {
            throw new \InvalidArgumentException('Invalid pattern ID');
        }

        // 단전 호흡의 경우 시간 설정 적용
        if ($patternId === 'danjeon') {
            error_log("Starting danjeon breathing with times - inhale: " . $inhaleTime . ", exhale: " . $exhaleTime);
            
            $inhaleTime = $inhaleTime ?? 4;  // 기본값 4초
            $exhaleTime = $exhaleTime ?? 4;  // 기본값 4초
            
            // 패턴 복사본 생성
            $pattern = $this->patterns['danjeon'];
            $pattern['phases'] = [
                ['type' => 'inhale', 'duration' => (int)$inhaleTime],
                ['type' => 'exhale', 'duration' => (int)$exhaleTime]
            ];
            $this->patterns['danjeon'] = $pattern;
            
            error_log("Updated danjeon pattern: " . json_encode($pattern));
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
            'current_phase_start' => time(),
            'inhale_time' => $inhaleTime,
            'exhale_time' => $exhaleTime
        ];

        $this->saveSessions();
        error_log("Created session: " . json_encode($this->sessions[$sessionId]));

        return [
            'session_id' => $sessionId,
            'pattern' => $patternId,
            'duration' => $duration,
            'started_at' => $this->sessions[$sessionId]['started_at'],
            'inhale_time' => $inhaleTime,
            'exhale_time' => $exhaleTime
        ];
    }

    public function getSessionStatus($session_id)
    {
        if (!isset($this->sessions[$session_id])) {
            throw new \InvalidArgumentException('Session not found');
        }

        $session = $this->sessions[$session_id];
        $pattern = $this->patterns[$session['pattern']];
        
        // 단전 호흡의 경우 저장된 시간 사용
        if ($session['pattern'] === 'danjeon' && isset($session['inhale_time']) && isset($session['exhale_time'])) {
            $pattern['phases'] = [
                ['type' => 'inhale', 'duration' => (int)$session['inhale_time']],
                ['type' => 'exhale', 'duration' => (int)$session['exhale_time']]
            ];
        }
        
        $currentPhase = $pattern['phases'][$session['current_phase']];
        $elapsed = time() - $session['current_phase_start'];
        $remaining = $currentPhase['duration'] - $elapsed;

        if ($remaining <= 0) {
            $session['current_phase'] = ($session['current_phase'] + 1) % count($pattern['phases']);
            $session['current_phase_start'] = time();
            $currentPhase = $pattern['phases'][$session['current_phase']];
            $remaining = $currentPhase['duration'];
            $this->sessions[$session_id] = $session;
            $this->saveSessions();
        }

        $nextPhase = $pattern['phases'][($session['current_phase'] + 1) % count($pattern['phases'])];

        $visualGuide = $this->getVisualGuide($currentPhase['type']);
        $visualGuide['duration'] = $currentPhase['duration'];

        error_log("Session status - Phase: " . $currentPhase['type'] . ", Duration: " . $currentPhase['duration']);

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
            'visual_guide' => $visualGuide
        ];
    }

    public function endSession($session_id)
    {
        error_log("BreathingService::endSession called with ID: " . $session_id);
        
        if (empty($session_id)) {
            throw new \InvalidArgumentException('Session ID is required');
        }

        if (!isset($this->sessions[$session_id])) {
            error_log("Session not found: " . $session_id);
            throw new \InvalidArgumentException('Session not found');
        }

        error_log("Found session: " . json_encode($this->sessions[$session_id]));
        
        $this->sessions[$session_id]['status'] = 'completed';
        $this->sessions[$session_id]['ended_at'] = date('c');
        
        $sessionData = [
            'session_id' => $session_id,
            'status' => 'completed',
            'ended_at' => $this->sessions[$session_id]['ended_at'],
            'duration' => $this->sessions[$session_id]['duration'],
            'pattern' => $this->sessions[$session_id]['pattern']
        ];
        
        $this->saveSessions();
        error_log("Session data to return: " . json_encode($sessionData));
        
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
            'hold_in' => ['circle_size' => 1.5, 'color' => '#2196F3'],
            'exhale' => ['circle_size' => 0.5, 'color' => '#FFC107'],
            'hold_out' => ['circle_size' => 0.5, 'color' => '#FFC107']
        ];

        return $guides[$phaseType] ?? $guides['inhale'];
    }

    private function getAnimation($phaseType)
    {
        $animations = [
            'inhale' => 'expand',
            'hold_in' => 'pulse',
            'exhale' => 'contract',
            'hold_out' => 'pulse'
        ];

        return $animations[$phaseType] ?? 'expand';
    }
} 