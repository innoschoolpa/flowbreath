<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 테이블 목록 조회
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Database Tables:\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($tables as $table) {
        echo "\nTable: $table\n";
        echo str_repeat('-', 80) . "\n";
        
        // 테이블 구조 조회
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        printf("%-20s %-20s %-10s %-10s %-20s\n", 'Field', 'Type', 'Null', 'Key', 'Default');
        echo str_repeat('-', 80) . "\n";

        foreach ($columns as $column) {
            printf("%-20s %-20s %-10s %-10s %-20s\n",
                $column['Field'],
                $column['Type'],
                $column['Null'],
                $column['Key'],
                $column['Default'] ?? 'NULL'
            );
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 