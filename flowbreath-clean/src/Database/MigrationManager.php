<?php

namespace App\Database;

use PDO;
use Exception;
use DirectoryIterator;

class MigrationManager {
    private $db;
    private $migrationsPath;
    private $migrationsTable = 'migrations';

    public function __construct(PDO $db, string $migrationsPath) {
        $this->db = $db;
        $this->migrationsPath = $migrationsPath;
        $this->createMigrationsTable();
    }

    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    public function getMigrations() {
        $migrations = [];
        $dir = new DirectoryIterator($this->migrationsPath);
        
        foreach ($dir as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $migrations[] = $file->getBasename('.php');
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    public function getRanMigrations() {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPendingMigrations() {
        return array_diff($this->getMigrations(), $this->getRanMigrations());
    }

    public function getLastBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
        return (int) $stmt->fetchColumn();
    }

    public function runMigrations() {
        $pendingMigrations = $this->getPendingMigrations();
        if (empty($pendingMigrations)) {
            return "이미 모든 마이그레이션이 실행되었습니다.";
        }

        $batch = $this->getLastBatchNumber() + 1;
        $this->db->beginTransaction();

        try {
            foreach ($pendingMigrations as $migration) {
                $this->runMigration($migration, $batch);
            }
            
            $this->db->commit();
            return "마이그레이션이 성공적으로 실행되었습니다.";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("마이그레이션 실행 실패: " . $e->getMessage());
        }
    }

    public function rollbackMigrations() {
        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            return "롤백할 마이그레이션이 없습니다.";
        }

        $stmt = $this->db->prepare("SELECT migration FROM {$this->migrationsTable} WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->db->beginTransaction();

        try {
            foreach ($migrations as $migration) {
                $this->rollbackMigration($migration);
            }
            
            $this->db->commit();
            return "마이그레이션이 성공적으로 롤백되었습니다.";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("마이그레이션 롤백 실패: " . $e->getMessage());
        }
    }

    private function runMigration($migration, $batch) {
        require_once $this->migrationsPath . '/' . $migration . '.php';
        $class = $this->getMigrationClass($migration);
        $instance = new $class($this->db);
        
        $instance->up();
        
        $stmt = $this->db->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }

    private function rollbackMigration($migration) {
        require_once $this->migrationsPath . '/' . $migration . '.php';
        $class = $this->getMigrationClass($migration);
        $instance = new $class($this->db);
        
        $instance->down();
        
        $stmt = $this->db->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
    }

    private function getMigrationClass($migration) {
        $parts = explode('_', $migration);
        array_shift($parts); // Remove timestamp
        $className = implode('', array_map('ucfirst', $parts));
        return "App\\Database\\Migrations\\{$className}";
    }
} 