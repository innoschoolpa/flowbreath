<?php

namespace App\Controllers;

use App\Core\Request;

class TestController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function testError()
    {
        throw new \Exception('This is a test error');
    }

    public function testWarning()
    {
        trigger_error('This is a test warning', E_USER_WARNING);
        return $this->json(['message' => 'Warning triggered']);
    }

    public function testNotice()
    {
        trigger_error('This is a test notice', E_USER_NOTICE);
        return $this->json(['message' => 'Notice triggered']);
    }

    public function testMemory()
    {
        $memory = memory_get_usage(true);
        return $this->json([
            'memory_usage' => $memory,
            'memory_usage_formatted' => $this->formatBytes($memory)
        ]);
    }

    public function testPerformance()
    {
        $start = microtime(true);
        // 간단한 성능 테스트
        for ($i = 0; $i < 1000000; $i++) {
            $result = $i * $i;
        }
        $end = microtime(true);
        
        return $this->json([
            'execution_time' => $end - $start,
            'memory_peak' => memory_get_peak_usage(true),
            'memory_peak_formatted' => $this->formatBytes(memory_get_peak_usage(true))
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 