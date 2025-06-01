<?php

namespace App\Commands;

use App\Core\Database;

class MigrateCommand {
    private $db;
    private $migrationsPath;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/../Database/migrations';
    }

    public function run() {
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();

        // Get all migration files
        $files = glob($this->migrationsPath . '/*.php');
        sort($files); // Ensure migrations run in order

        foreach ($files as $file) {
            $className = 'Migration_' . pathinfo($file, PATHINFO_FILENAME);
            require_once $file;

            // Check if migration has been run
            $stmt = $this->db->prepare("SELECT * FROM migrations WHERE migration = ?");
            $stmt->execute([$className]);
            $migration = $stmt->fetch();

            if (!$migration) {
                echo "Running migration: $className\n";
                
                $migration = new $className();
                $migration->up();

                // Record migration
                $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$className, time()]);
            }
        }

        echo "Migrations completed successfully.\n";
    }

    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->query($sql);
    }
} 