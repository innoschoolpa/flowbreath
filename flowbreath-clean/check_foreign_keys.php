<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    // resources_backup 테이블의 외래 키 제약 조건 확인
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
            AND (TABLE_NAME = 'resources_backup' OR REFERENCED_TABLE_NAME = 'resources_backup')
    ");
    
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($foreignKeys)) {
        echo "resources_backup 테이블에 외래 키 제약 조건이 없습니다.\n";
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