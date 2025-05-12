<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // 테이블 목록 조회
    $tables = $db->getTables();
    
    echo "=== Database Tables ===\n";
    foreach ($tables as $table) {
        $tableName = reset($table); // 첫 번째 컬럼 값이 테이블 이름
        echo "\nTable: $tableName\n";
        echo "-------------------\n";
        
        // 테이블 구조 조회
        $columns = $db->getTableStructure($tableName);
        foreach ($columns as $column) {
            echo sprintf(
                "Field: %-20s Type: %-15s Null: %-5s Key: %-5s Default: %s\n",
                $column['Field'],
                $column['Type'],
                $column['Null'],
                $column['Key'],
                $column['Default'] ?? 'NULL'
            );
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 