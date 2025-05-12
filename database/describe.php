<?php

require_once __DIR__ . '/../src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $table = $argv[1] ?? null;
    if (!$table) {
        echo "Usage: php describe.php <table_name>\n";
        exit(1);
    }

    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table structure for $table:\n";
    echo str_repeat('-', 80) . "\n";
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
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 