<?php
require_once 'src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== Diaries 테이블 구조 ===\n";
    $stmt = $pdo->query('DESCRIBE diaries');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
    
    echo "\n=== 최근 일기 데이터 샘플 ===\n";
    $stmt = $pdo->query('SELECT id, title, LENGTH(content) as content_length, created_at FROM diaries ORDER BY id DESC LIMIT 3');
    $diaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($diaries);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 