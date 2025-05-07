<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Core\Application;
use App\Core\Database;
use App\Core\Session;

class TestCase extends BaseTestCase
{
    protected $app;
    protected $db;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트 환경 설정
        $this->app = Application::getInstance();
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
        
        // 테스트 데이터베이스 설정
        $this->configureTestDatabase();
        
        // 테스트 세션 설정
        $this->configureTestSession();
    }

    protected function tearDown(): void
    {
        // 테스트 데이터 정리
        $this->cleanupTestData();
        
        parent::tearDown();
    }

    protected function configureTestDatabase()
    {
        $config = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'flowbreath_test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'max_pool_size' => 5,
            'min_pool_size' => 1,
            'connection_timeout' => 60,
            'pool_check_interval' => 30,
            'query_cache_enabled' => true,
            'query_cache_time' => 60,
            'query_cache_size' => 100
        ];

        $this->app->setConfig('database', $config);
    }

    protected function configureTestSession()
    {
        $config = [
            'session' => [
                'lifetime' => 3600,
                'path' => sys_get_temp_dir(),
                'encryption_key' => 'test_key_123',
                'cookie' => [
                    'name' => 'test_session',
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            ]
        ];

        $this->app->setConfig('app', $config);
    }

    protected function cleanupTestData()
    {
        // 테스트 데이터베이스 정리
        $tables = $this->db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $this->db->query("TRUNCATE TABLE {$table}");
        }

        // 세션 데이터 정리
        $this->session->clear();
    }

    protected function createTestUser($data = [])
    {
        $defaultData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $userData = array_merge($defaultData, $data);
        return $this->db->insert('users', $userData);
    }

    protected function loginAs($userId)
    {
        $user = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        )->fetch();

        if ($user) {
            $this->session->set('user_id', $user['id']);
            $this->session->set('user_role', $user['role']);
            return true;
        }

        return false;
    }

    protected function logout()
    {
        $this->session->remove('user_id');
        $this->session->remove('user_role');
    }
} 