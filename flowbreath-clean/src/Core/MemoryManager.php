<?php

namespace App\Core;

class MemoryManager
{
    private static $instance = null;
    private $checkpoints = [];
    private $memoryLimit;
    private $logger;
    private $lastCheckTime;
    private $checkInterval = 5; // 5초마다 메모리 체크

    private function __construct()
    {
        // 메모리 제한을 먼저 설정
        $this->memoryLimit = '2G';
        ini_set('memory_limit', $this->memoryLimit);
        
        // Logger는 필요할 때만 초기화
        $this->logger = null;
        $this->lastCheckTime = microtime(true);
        
        // 가비지 컬렉션 강제 실행
        $this->forceGarbageCollection();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = Logger::getInstance();
        }
        return $this->logger;
    }

    private function initialize()
    {
        try {
            // 메모리 제한 설정
            $app = Application::getInstance();
            $config = $app->getConfig('app');
            
            if (isset($config['memory_limit'])) {
                $oldLimit = ini_get('memory_limit');
                ini_set('memory_limit', $config['memory_limit']);
                $this->memoryLimit = $config['memory_limit'];
                
                $this->getLogger()->info(sprintf(
                    "Memory limit changed from %s to %s",
                    $oldLimit,
                    $this->memoryLimit
                ));
            }
        } catch (\Exception $e) {
            $this->getLogger()->error("Failed to initialize memory manager: " . $e->getMessage());
            throw $e;
        }
    }

    public function createCheckpoint($name)
    {
        $this->forceGarbageCollection();

        $this->checkpoints[$name] = [
            'memory' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'time' => microtime(true)
        ];

        $this->getLogger()->debug(sprintf(
            "Memory checkpoint created: %s (%.2f MB)",
            $name,
            $this->checkpoints[$name]['memory'] / 1024 / 1024
        ));
    }

    public function getCheckpointDiff($checkpointName)
    {
        if (!isset($this->checkpoints[$checkpointName])) {
            $this->getLogger()->warning("Checkpoint not found: " . $checkpointName);
            return null;
        }

        $current = [
            'memory' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'time' => microtime(true)
        ];

        $checkpoint = $this->checkpoints[$checkpointName];
        $diff = [
            'memory_diff' => $current['memory'] - $checkpoint['memory'],
            'peak_diff' => $current['peak'] - $checkpoint['peak'],
            'time_diff' => $current['time'] - $checkpoint['time']
        ];

        $this->getLogger()->debug(sprintf(
            "Memory diff for %s: %.2f MB",
            $checkpointName,
            $diff['memory_diff'] / 1024 / 1024
        ));

        return $diff;
    }

    public function getMemoryStats()
    {
        $this->forceGarbageCollection();

        $stats = [
            'current' => $this->formatBytes(memory_get_usage(true)),
            'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'limit' => $this->memoryLimit,
            'usage_percent' => $this->getMemoryUsagePercent()
        ];

        $this->getLogger()->debug(sprintf(
            "Memory stats: Current=%s, Peak=%s, Usage=%.1f%%",
            $stats['current'],
            $stats['peak'],
            $stats['usage_percent']
        ));

        return $stats;
    }

    public function checkMemoryLimit($threshold = 0.8)
    {
        // 일정 시간 간격으로만 체크
        $now = microtime(true);
        if ($now - $this->lastCheckTime < $this->checkInterval) {
            return true;
        }
        $this->lastCheckTime = $now;

        $this->forceGarbageCollection();

        $current = memory_get_usage(true);
        $limit = $this->parseMemoryLimit($this->memoryLimit);
        
        if ($limit === -1) { // 무제한
            return true;
        }

        $usage = $current / $limit;
        
        if ($usage > $threshold) {
            $message = sprintf(
                "Memory usage is high: %.2f%% (%.2f MB / %.2f MB)",
                $usage * 100,
                $current / 1024 / 1024,
                $limit / 1024 / 1024
            );
            
            $this->getLogger()->warning($message);
            
            if ($usage > 0.95) { // 95% 이상 사용 시 예외 발생
                throw new \RuntimeException("Critical memory usage: " . $message);
            }
        }

        return $usage < $threshold;
    }

    private function getMemoryUsagePercent()
    {
        $current = memory_get_usage(true);
        $limit = $this->parseMemoryLimit($this->memoryLimit);
        
        if ($limit === -1) {
            return 0;
        }

        return ($current / $limit) * 100;
    }

    private function parseMemoryLimit($limit)
    {
        if ($limit === '-1') {
            return -1;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;

        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function forceGarbageCollection()
    {
        if (function_exists('gc_collect_cycles')) {
            $collected = gc_collect_cycles();
            if ($collected > 0) {
                $this->getLogger()->debug("Garbage collection: {$collected} cycles collected");
            }
        }
    }

    public function clearCheckpoints()
    {
        $this->checkpoints = [];
        $this->forceGarbageCollection();
        $this->getLogger()->debug("Memory checkpoints cleared");
    }

    public function __destruct()
    {
        $this->checkpoints = [];
        $this->logger = null;
        $this->forceGarbageCollection();
    }
} 