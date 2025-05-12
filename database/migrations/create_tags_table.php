<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // resource_tags 테이블이 있다면 먼저 삭제
    $pdo->exec("DROP TABLE IF EXISTS resource_tags");
    echo "resource_tags 테이블이 삭제되었습니다.\n";

    // 기존 tags 테이블이 있다면 삭제
    $pdo->exec("DROP TABLE IF EXISTS tags");
    echo "기존 tags 테이블이 삭제되었습니다.\n";

    // tags 테이블 생성
    $pdo->exec("CREATE TABLE tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "태그 테이블이 성공적으로 생성되었습니다.\n";
} catch (PDOException $e) {
    die("테이블 생성 실패: " . $e->getMessage() . "\n");
} 