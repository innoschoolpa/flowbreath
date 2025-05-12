<?php

namespace App\Core;

class Logger
{
    private static $instance = null;
    private $logPath;
    private $logLevel;
    private $logLevels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];

    protected function __construct()
    {
        // 기본 설정 사용
        $this->logPath = PROJECT_ROOT . '/logs/app.log';
        $this->logLevel = 'error';
        
        $this->initializeLogDirectory();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function initializeLogDirectory()
    {
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public function debug($message)
    {
        $this->log('debug', $message);
    }

    public function info($message)
    {
        $this->log('info', $message);
    }

    public function warning($message)
    {
        $this->log('warning', $message);
    }

    public function error($message)
    {
        $this->log('error', $message);
    }

    public function critical($message)
    {
        $this->log('critical', $message);
    }

    private function log($level, $message)
    {
        if ($this->logLevels[$level] < $this->logLevels[$this->logLevel]) {
            return;
        }

        try {
            $logEntry = sprintf(
                "[%s] [%s] %s\n",
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message
            );

            // 로그 디렉토리 확인 및 생성
            $logDir = dirname($this->logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // 로그 파일 권한 확인
            if (file_exists($this->logPath) && !is_writable($this->logPath)) {
                chmod($this->logPath, 0666);
            }

            // 로그 파일 크기 확인 및 로테이션
            if (file_exists($this->logPath) && filesize($this->logPath) > 10 * 1024 * 1024) { // 10MB
                $this->rotateLog();
            }

            // 로그 작성
            if (error_log($logEntry, 3, $this->logPath) === false) {
                throw new \RuntimeException("Failed to write to log file: {$this->logPath}");
            }
        } catch (\Exception $e) {
            // 로그 작성 실패 시 시스템 로그에 기록
            error_log("Logger error: " . $e->getMessage());
        }
    }

    private function rotateLog()
    {
        $maxFiles = 5;
        $logDir = dirname($this->logPath);
        $baseName = basename($this->logPath, '.log');

        // 이전 로그 파일 삭제
        $oldLog = "{$logDir}/{$baseName}.{$maxFiles}.log";
        if (file_exists($oldLog)) {
            unlink($oldLog);
        }

        // 로그 파일 순환
        for ($i = $maxFiles - 1; $i >= 1; $i--) {
            $oldLog = "{$logDir}/{$baseName}.{$i}.log";
            $newLog = "{$logDir}/{$baseName}." . ($i + 1) . ".log";
            if (file_exists($oldLog)) {
                rename($oldLog, $newLog);
            }
        }

        // 현재 로그 파일 백업
        if (file_exists($this->logPath)) {
            rename($this->logPath, "{$logDir}/{$baseName}.1.log");
        }
    }

    public function setLogLevel($level)
    {
        if (!isset($this->logLevels[$level])) {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }
        $this->logLevel = $level;
        return true;
    }

    public function getLogLevel()
    {
        return $this->logLevel;
    }

    public function setLogPath($path)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Log path cannot be empty");
        }
        $this->logPath = $path;
        $this->initializeLogDirectory();
    }

    public function getLogPath()
    {
        return $this->logPath;
    }

    public function clearLog()
    {
        if (file_exists($this->logPath)) {
            file_put_contents($this->logPath, '');
        }
    }

    public function getLogContent($lines = null)
    {
        if (!file_exists($this->logPath)) {
            return '';
        }

        if ($lines === null) {
            return file_get_contents($this->logPath);
        }

        $content = [];
        $file = new \SplFileObject($this->logPath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $start = max(0, $totalLines - $lines);
        $file->seek($start);
        
        while (!$file->eof()) {
            $content[] = $file->fgets();
        }

        return implode('', $content);
    }

    public function __destruct()
    {
        // 리소스 정리
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
} 