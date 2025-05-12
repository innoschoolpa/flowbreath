<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);

    if (empty($tables)) {
        echo "데이터베이스에 테이블이 없습니다.\n";
    } else {
        foreach ($tables as $tableRow) {
            $tableName = $tableRow[0];
            echo "테이블: $tableName\n";

            $columnsStmt = $pdo->query("SHOW COLUMNS FROM `$tableName`");
            $columns = $columnsStmt->fetchAll();

            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
            echo "----------------------\n";
        }
    }
} catch (PDOException $e) {
    echo "쿼리 실행 중 오류 발생: " . $e->getMessage() . "\n";
} 