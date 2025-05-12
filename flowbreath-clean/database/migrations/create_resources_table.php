<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // resources 테이블 생성
    $pdo->exec("CREATE TABLE IF NOT EXISTS resources (
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
        INDEX idx_published_at (published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "리소스 테이블이 성공적으로 생성되었습니다.\n";

    // 외래 키 추가
    $pdo->exec("ALTER TABLE resources 
        ADD CONSTRAINT fk_resources_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE");

    echo "리소스 테이블의 외래 키가 성공적으로 추가되었습니다.\n";

    // resource_tags 테이블 생성
    $pdo->exec("CREATE TABLE IF NOT EXISTS resource_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resource_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_resource_tag (resource_id, tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "리소스-태그 테이블이 성공적으로 생성되었습니다.\n";

    // resource_tags 테이블의 외래 키 추가
    $pdo->exec("ALTER TABLE resource_tags 
        ADD CONSTRAINT fk_resource_tags_resource 
        FOREIGN KEY (resource_id) REFERENCES resources(id) 
        ON DELETE CASCADE");

    $pdo->exec("ALTER TABLE resource_tags 
        ADD CONSTRAINT fk_resource_tags_tag 
        FOREIGN KEY (tag_id) REFERENCES tags(id) 
        ON DELETE CASCADE");

    echo "리소스-태그 테이블의 외래 키가 성공적으로 추가되었습니다.\n";

} catch (PDOException $e) {
    die("테이블 생성 실패: " . $e->getMessage() . "\n");
} 