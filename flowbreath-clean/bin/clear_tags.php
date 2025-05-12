<?php

try {
    $pdo = new PDO(
        'mysql:host=srv636.hstgr.io;dbname=u573434051_flowbreath',
        'u573434051_flow',
        'Eduispa1712!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 외래 키 제약 조건 비활성화
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // 태그 관련 테이블 비우기
    $pdo->exec('TRUNCATE TABLE tag_translations');
    echo "Cleared tag_translations table\n";
    
    $pdo->exec('TRUNCATE TABLE tags');
    echo "Cleared tags table\n";
    
    // 외래 키 제약 조건 다시 활성화
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "Tables cleared successfully\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // 오류가 발생해도 외래 키 제약 조건을 다시 활성화
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch(PDOException $e2) {
        echo "Error restoring foreign key checks: " . $e2->getMessage() . "\n";
    }
} 