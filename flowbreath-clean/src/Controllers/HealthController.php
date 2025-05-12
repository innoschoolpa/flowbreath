<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Application;

class HealthController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function check()
    {
        $app = Application::getInstance();
        $memoryStats = $app->getMemoryStats();
        
        return $this->json([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'system' => [
                'memory' => [
                    'current' => $memoryStats['current'],
                    'peak' => $memoryStats['peak'],
                    'limit' => ini_get('memory_limit')
                ],
                'php' => [
                    'version' => PHP_VERSION,
                    'extensions' => get_loaded_extensions()
                ],
                'database' => [
                    'status' => $this->checkDatabaseConnection() ? 'connected' : 'disconnected'
                ]
            ],
            'endpoints' => [
                'health' => '/api/health',
                'error' => '/api/test/error',
                'warning' => '/api/test/warning',
                'notice' => '/api/test/notice',
                'memory' => '/api/test/memory',
                'performance' => '/api/test/performance'
            ]
        ]);
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            error_log('Database connection check failed: ' . $e->getMessage());
            return false;
        }
    }
} 