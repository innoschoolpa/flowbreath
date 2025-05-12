<?php

try {
    $pdo = new PDO(
        'mysql:host=srv636.hstgr.io;dbname=u573434051_flowbreath',
        'u573434051_flow',
        'Eduispa1712!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 테이블 구조 확인
    $stmt = $pdo->query('SHOW COLUMNS FROM tags');
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = $row;
    }
    
    // 인덱스 확인
    $stmt = $pdo->query('SHOW INDEX FROM tags');
    $indexes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $indexes[$row['Key_name']] = $row;
    }
    
    // description 컬럼 삭제
    if (isset($columns['description'])) {
        $pdo->exec('ALTER TABLE tags DROP COLUMN description');
        echo "Dropped description column\n";
    }
    
    // updated_at 컬럼 삭제
    if (isset($columns['updated_at'])) {
        $pdo->exec('ALTER TABLE tags DROP COLUMN updated_at');
        echo "Dropped updated_at column\n";
    }
    
    // name unique key 삭제
    if (isset($indexes['name'])) {
        $pdo->exec('ALTER TABLE tags DROP INDEX name');
        echo "Dropped name unique key\n";
    }
    
    // slug 컬럼 추가
    if (!isset($columns['slug'])) {
        $pdo->exec('ALTER TABLE tags ADD COLUMN slug VARCHAR(50) NOT NULL DEFAULT "" AFTER name');
        echo "Added slug column\n";
    }
    
    // 기존 데이터의 slug 생성
    $stmt = $pdo->query('SELECT id, name FROM tags');
    $usedSlugs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $baseSlug = strtolower(str_replace(' ', '-', $row['name']));
        $slug = $baseSlug;
        $counter = 1;
        
        // 중복된 slug가 있으면 숫자를 붙임
        while (in_array($slug, $usedSlugs)) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        $usedSlugs[] = $slug;
        $update = $pdo->prepare('UPDATE tags SET slug = ? WHERE id = ?');
        $update->execute([$slug, $row['id']]);
    }
    echo "Updated existing records with slugs\n";
    
    // unique_slug 인덱스 추가
    if (!isset($indexes['unique_slug'])) {
        $pdo->exec('ALTER TABLE tags ADD UNIQUE KEY unique_slug (slug)');
        echo "Added unique_slug index\n";
    }
    
    echo "Table modification completed successfully\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 