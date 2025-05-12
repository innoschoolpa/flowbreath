<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check table structure
    $stmt = $pdo->query("SHOW CREATE TABLE resources");
    $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Resources table structure:\n" . $tableInfo['Create Table'] . "\n\n";
    
    // Check row count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM resources");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records: " . $count['count'] . "\n\n";
    
    // Check sample data
    $stmt = $pdo->query("SELECT id, user_id, title, slug, LEFT(content, 100) as content_preview, visibility, status, created_at, updated_at FROM resources LIMIT 3");
    echo "Sample records:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "----------------------------------------\n";
        foreach ($row as $key => $value) {
            echo $key . ": " . $value . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 