<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected PDO $pdo;
    protected array $config;
    private static bool $migrationsRun = false;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = require __DIR__ . '/config/database.test.php';
        $this->pdo = new PDO(
            "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}",
            $this->config['username'],
            $this->config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        if (!self::$migrationsRun) {
            $runner = new MigrationRunner($this->pdo, __DIR__ . '/migrations');
            $runner->runMigrations();
            self::$migrationsRun = true;
        }

        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }

    protected function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    protected function rollbackTransaction(): void
    {
        $this->pdo->rollBack();
    }

    protected function createTestUser(array $data = []): int
    {
        $defaultData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user'
        ];

        $data = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO users (username, email, password_hash, role, created_at) 
                VALUES (:username, :email, :password_hash, :role, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }

    protected function createTestResource(array $data = []): int
    {
        $defaultData = [
            'title' => 'Test Resource',
            'content' => 'Test Content',
            'user_id' => $this->createTestUser(),
            'is_public' => true
        ];

        $data = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO resources (title, content, user_id, is_public, created_at) 
                VALUES (:title, :content, :user_id, :is_public, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }
} 