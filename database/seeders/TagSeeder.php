<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // 기본 태그 데이터
    $tags = [
        ['name' => 'PHP', 'slug' => 'php', 'description' => 'PHP 프로그래밍 언어'],
        ['name' => 'JavaScript', 'slug' => 'javascript', 'description' => 'JavaScript 프로그래밍 언어'],
        ['name' => 'HTML', 'slug' => 'html', 'description' => 'HTML 마크업 언어'],
        ['name' => 'CSS', 'slug' => 'css', 'description' => 'CSS 스타일시트'],
        ['name' => 'Database', 'slug' => 'database', 'description' => '데이터베이스 관련'],
        ['name' => 'MySQL', 'slug' => 'mysql', 'description' => 'MySQL 데이터베이스'],
        ['name' => 'Laravel', 'slug' => 'laravel', 'description' => 'Laravel PHP 프레임워크'],
        ['name' => 'Vue.js', 'slug' => 'vuejs', 'description' => 'Vue.js 프레임워크'],
        ['name' => 'React', 'slug' => 'react', 'description' => 'React 라이브러리'],
        ['name' => 'Node.js', 'slug' => 'nodejs', 'description' => 'Node.js 런타임']
    ];

    // 태그 삽입
    $stmt = $pdo->prepare("INSERT INTO tags (name, slug, description) VALUES (:name, :slug, :description)");
    
    foreach ($tags as $tag) {
        $stmt->execute($tag);
        echo "태그 '{$tag['name']}' 이(가) 생성되었습니다.\n";
    }

    echo "모든 태그가 성공적으로 생성되었습니다.\n";

} catch (PDOException $e) {
    die("태그 생성 실패: " . $e->getMessage() . "\n");
} 