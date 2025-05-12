<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== 데이터베이스 최적화 시작 ===\n\n";
    
    // 1. 테이블 최적화
    $tables = ['resources', 'tags', 'resource_tags', 'resource_translations'];
    foreach ($tables as $table) {
        echo "테이블 최적화 중: $table\n";
        $pdo->exec("OPTIMIZE TABLE $table");
    }
    
    // 2. 인덱스 재구성
    foreach ($tables as $table) {
        echo "인덱스 재구성 중: $table\n";
        $pdo->exec("ANALYZE TABLE $table");
    }
    
    // 3. 통계 업데이트
    foreach ($tables as $table) {
        echo "통계 업데이트 중: $table\n";
        $pdo->exec("ANALYZE TABLE $table");
    }
    
    // 4. 캐시 정리
    $pdo->exec("FLUSH QUERY CACHE");
    $pdo->exec("RESET QUERY CACHE");
    
    echo "\n=== 데이터베이스 최적화 완료 ===\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 