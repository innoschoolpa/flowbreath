<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== 데이터베이스 분석 시작 ===\n\n";
    
    // 1. 테이블 구조 분석
    echo "1. 테이블 구조 분석:\n";
    $tables = ['resources', 'tags', 'resource_tags', 'resource_translations'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE $table");
        $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n[$table] 테이블 구조:\n" . $tableInfo['Create Table'] . "\n";
    }
    
    // 2. 데이터 통계
    echo "\n2. 데이터 통계:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$table: {$count['count']} 레코드\n";
    }
    
    // 3. 인덱스 사용 현황
    echo "\n3. 인덱스 사용 현황:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        echo "\n[$table] 인덱스:\n";
        while ($index = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$index['Key_name']}: {$index['Column_name']}\n";
        }
    }
    
    // 4. 샘플 데이터 분석
    echo "\n4. 샘플 데이터 분석:\n";
    $stmt = $pdo->query("
        SELECT r.*, 
               GROUP_CONCAT(t.name) as tags,
               COUNT(DISTINCT rtr.language_code) as translation_count
        FROM resources r
        LEFT JOIN resource_tags rt ON r.id = rt.resource_id
        LEFT JOIN tags t ON rt.tag_id = t.id
        LEFT JOIN resource_translations rtr ON r.id = rtr.resource_id
        GROUP BY r.id
        LIMIT 5
    ");
    
    echo "\n리소스 샘플:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "\nID: {$row['id']}\n";
        echo "제목: {$row['title']}\n";
        echo "상태: {$row['status']}\n";
        echo "공개여부: {$row['visibility']}\n";
        echo "조회수: {$row['view_count']}\n";
        echo "태그: {$row['tags']}\n";
        echo "번역 수: {$row['translation_count']}\n";
        echo "생성일: {$row['created_at']}\n";
        echo "----------------------------------------\n";
    }
    
    // 5. 성능 관련 통계
    echo "\n5. 성능 관련 통계:\n";
    $stmt = $pdo->query("SHOW TABLE STATUS");
    while ($table = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (in_array($table['Name'], $tables)) {
            echo "\n[{$table['Name']}]\n";
            echo "데이터 크기: " . round($table['Data_length'] / 1024 / 1024, 2) . " MB\n";
            echo "인덱스 크기: " . round($table['Index_length'] / 1024 / 1024, 2) . " MB\n";
            echo "평균 행 길이: {$table['Avg_row_length']} bytes\n";
        }
    }
    
    // 6. 외래 키 제약 조건
    echo "\n6. 외래 키 제약 조건:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        echo "\n[$table] 외래 키:\n";
        while ($fk = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    }
    
    echo "\n=== 데이터베이스 분석 완료 ===\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 