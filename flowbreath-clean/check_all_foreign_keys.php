<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    // 모든 외래 키 제약 조건 확인
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_SCHEMA = 'u573434051_flowbreath'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($foreignKeys)) {
        echo "데이터베이스에 외래 키 제약 조건이 없습니다.\n";
    } else {
        echo "외래 키 제약 조건 목록:\n";
        foreach ($foreignKeys as $fk) {
            print_r($fk);
            echo "----------------------\n";
        }
    }

} catch (PDOException $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
} 