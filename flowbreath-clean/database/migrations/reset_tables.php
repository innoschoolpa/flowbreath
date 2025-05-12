<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // 외래 키 체크 비활성화
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // users 테이블을 참조하는 모든 테이블 찾기
    $stmt = $pdo->query("
        SELECT TABLE_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'users' 
        AND TABLE_SCHEMA = DATABASE()
    ");
    $user_referencing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // users 테이블을 참조하는 테이블 먼저 삭제
    foreach ($user_referencing_tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "$table 테이블이 삭제되었습니다.\n";
    }

    // tags 테이블을 참조하는 모든 테이블 찾기
    $stmt = $pdo->query("
        SELECT TABLE_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'tags' 
        AND TABLE_SCHEMA = DATABASE()
    ");
    $tag_referencing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // tags 테이블을 참조하는 테이블 삭제
    foreach ($tag_referencing_tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "$table 테이블이 삭제되었습니다.\n";
    }

    // 관련된 모든 테이블 삭제
    $tables = [
        'resource_tags',
        'related_resources_link',
        'resources',
        'tags',
        'users'
    ];

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "$table 테이블이 삭제되었습니다.\n";
    }

    // 외래 키 체크 활성화
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // users 테이블 생성
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(100),
        email_verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "users 테이블이 생성되었습니다.\n";

    // tags 테이블 생성
    $pdo->exec("CREATE TABLE tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "tags 테이블이 생성되었습니다.\n";

    // resources 테이블 생성
    $pdo->exec("CREATE TABLE resources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        description TEXT,
        visibility ENUM('public', 'private') DEFAULT 'private',
        status ENUM('draft', 'published') DEFAULT 'draft',
        published_at TIMESTAMP NULL,
        view_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL,
        INDEX idx_user_status (user_id, status),
        INDEX idx_visibility_status (visibility, status),
        INDEX idx_published_at (published_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "resources 테이블이 생성되었습니다.\n";

    // resource_tags 테이블 생성
    $pdo->exec("CREATE TABLE resource_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resource_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_resource_tag (resource_id, tag_id),
        FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "resource_tags 테이블이 생성되었습니다.\n";

    echo "모든 테이블이 성공적으로 재설정되었습니다.\n";

} catch (PDOException $e) {
    die("테이블 재설정 실패: " . $e->getMessage() . "\n");
} 