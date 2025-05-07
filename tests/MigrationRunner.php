<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use PDOException;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(PDO $pdo, string $migrationsPath)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath;
    }

    public function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function runMigrations(): void
    {
        $this->createMigrationsTable();
        $executedMigrations = $this->getExecutedMigrations();
        
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $migration = basename($file);
            if (!in_array($migration, $executedMigrations)) {
                $this->runMigration($file, $migration);
            }
        }
    }

    public function rollbackMigrations(): void
    {
        $executedMigrations = $this->getExecutedMigrations();
        rsort($executedMigrations);

        foreach ($executedMigrations as $migration) {
            $file = $this->migrationsPath . '/' . $migration;
            if (file_exists($file)) {
                $this->rollbackMigration($file, $migration);
            }
        }
    }

    private function runMigration(string $file, string $migration): void
    {
        $migrationData = require $file;
        $sql = $migrationData['up'];

        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec($sql);
            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$migration]);
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Migration failed: {$migration}. Error: " . $e->getMessage());
        }
    }

    private function rollbackMigration(string $file, string $migration): void
    {
        $migrationData = require $file;
        $sql = $migrationData['down'];

        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec($sql);
            $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
            $stmt->execute([$migration]);
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException("Rollback failed: {$migration}. Error: " . $e->getMessage());
        }
    }
} 