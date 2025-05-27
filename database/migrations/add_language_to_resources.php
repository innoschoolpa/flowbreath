<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // Add language column to resources table
    $pdo->exec("ALTER TABLE resources 
        ADD COLUMN language VARCHAR(2) NOT NULL DEFAULT 'ko' AFTER content,
        ADD INDEX idx_language (language)");

    echo "Language column has been added to resources table.\n";

    // Update existing resources to have language field
    $pdo->exec("UPDATE resources SET language = 'ko' WHERE language IS NULL");

    echo "Existing resources have been updated with default language.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
} 