<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== Diaries 테이블 content 컬럼 타입 변경 ===\n";
    
    // 현재 컬럼 정보 확인
    $stmt = $pdo->query("SHOW COLUMNS FROM diaries LIKE 'content'");
    $columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "현재 content 컬럼 타입: " . $columnInfo['Type'] . "\n";
    
    // MEDIUMTEXT로 변경
    $sql = "ALTER TABLE diaries MODIFY content MEDIUMTEXT";
    $pdo->exec($sql);
    
    echo "content 컬럼이 MEDIUMTEXT로 변경되었습니다.\n";
    
    // 변경 후 확인
    $stmt = $pdo->query("SHOW COLUMNS FROM diaries LIKE 'content'");
    $columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "변경 후 content 컬럼 타입: " . $columnInfo['Type'] . "\n";
    
    echo "마이그레이션이 성공적으로 완료되었습니다.\n";
    
} catch (PDOException $e) {
    die("마이그레이션 실패: " . $e->getMessage() . "\n");
} 