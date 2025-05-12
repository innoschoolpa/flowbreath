<?php

try {
    // 데이터베이스 연결 설정
    $pdo = new PDO(
        'mysql:host=srv636.hstgr.io;dbname=u573434051_flowbreath',
        'u573434051_flow',
        'Eduispa1712!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // resources2의 데이터 수 확인
    $resources2Count = $pdo->query("SELECT COUNT(*) FROM resources2")->fetchColumn();
    echo "Total records in resources2: $resources2Count\n";
    
    // 최근 추가된 resources 데이터 확인
    $stmt = $pdo->query("
        SELECT id, title, slug, created_at, updated_at
        FROM resources
        WHERE user_id = 1
        ORDER BY created_at DESC
        LIMIT 5
    ");
    
    echo "\nRecently migrated resources:\n";
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}\n";
        echo "Title: {$row['title']}\n";
        echo "Slug: {$row['slug']}\n";
        echo "Created: {$row['created_at']}\n";
        echo "Updated: {$row['updated_at']}\n";
        echo "-------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 