<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Get all tables
    $tables = $db->fetchAll("SHOW TABLES");
    
    echo "Database Tables:\n";
    echo "================\n\n";
    
    foreach ($tables as $table) {
        $tableName = reset($table); // Get the first value from the array
        
        echo "Table: {$tableName}\n";
        echo "----------------\n";
        
        // Get table structure
        $columns = $db->fetchAll("SHOW COLUMNS FROM {$tableName}");
        
        foreach ($columns as $column) {
            $type = $column['Type'];
            $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column['Default'] !== null ? "DEFAULT '{$column['Default']}'" : '';
            $key = $column['Key'] ? "({$column['Key']})" : '';
            
            echo sprintf(
                "%-20s %-20s %-10s %-15s %s\n",
                $column['Field'],
                $type,
                $null,
                $default,
                $key
            );
        }
        
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 