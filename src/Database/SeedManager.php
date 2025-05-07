<?php

namespace App\Database;

use PDO;
use Exception;
use DirectoryIterator;

class SeedManager {
    private $db;
    private $seedsPath;
    private $seedsTable = 'seeds';

    public function __construct(PDO $db, string $seedsPath) {
        $this->db = $db;
        $this->seedsPath = $seedsPath;
        $this->createSeedsTable();
    }

    private function createSeedsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->seedsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seed VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->exec($sql);
    }

    public function getSeeds() {
        $seeds = [];
        $dir = new DirectoryIterator($this->seedsPath);
        
        foreach ($dir as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $seeds[] = $file->getBasename('.php');
            }
        }
        
        sort($seeds);
        return $seeds;
    }

    public function getRanSeeds() {
        $stmt = $this->db->query("SELECT seed FROM {$this->seedsTable} ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPendingSeeds() {
        return array_diff($this->getSeeds(), $this->getRanSeeds());
    }

    public function getLastBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->seedsTable}");
        return (int) $stmt->fetchColumn();
    }

    public function runSeeds() {
        $pendingSeeds = $this->getPendingSeeds();
        if (empty($pendingSeeds)) {
            return "이미 모든 시드가 실행되었습니다.";
        }

        $batch = $this->getLastBatchNumber() + 1;
        $this->db->beginTransaction();

        try {
            foreach ($pendingSeeds as $seed) {
                $this->runSeed($seed, $batch);
            }
            
            $this->db->commit();
            return "시드가 성공적으로 실행되었습니다.";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("시드 실행 실패: " . $e->getMessage());
        }
    }

    public function rollbackSeeds() {
        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            return "롤백할 시드가 없습니다.";
        }

        $stmt = $this->db->prepare("SELECT seed FROM {$this->seedsTable} WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $seeds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->db->beginTransaction();

        try {
            foreach ($seeds as $seed) {
                $this->rollbackSeed($seed);
            }
            
            $this->db->commit();
            return "시드가 성공적으로 롤백되었습니다.";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("시드 롤백 실패: " . $e->getMessage());
        }
    }

    private function runSeed($seed, $batch) {
        require_once $this->seedsPath . '/' . $seed . '.php';
        $class = $this->getSeedClass($seed);
        $instance = new $class($this->db);
        
        $instance->run();
        
        $stmt = $this->db->prepare("INSERT INTO {$this->seedsTable} (seed, batch) VALUES (?, ?)");
        $stmt->execute([$seed, $batch]);
    }

    private function rollbackSeed($seed) {
        require_once $this->seedsPath . '/' . $seed . '.php';
        $class = $this->getSeedClass($seed);
        $instance = new $class($this->db);
        
        if (method_exists($instance, 'down')) {
            $instance->down();
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->seedsTable} WHERE seed = ?");
        $stmt->execute([$seed]);
    }

    private function getSeedClass($seed) {
        $parts = explode('_', $seed);
        array_shift($parts); // Remove timestamp
        $className = implode('', array_map('ucfirst', $parts));
        return "App\\Database\\Seeds\\{$className}";
    }
} 