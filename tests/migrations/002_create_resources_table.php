<?php

declare(strict_types=1);

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS resources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            description TEXT,
            file_path VARCHAR(255),
            visibility ENUM('public', 'private') NOT NULL DEFAULT 'private',
            status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
            slug VARCHAR(255) UNIQUE,
            published_at DATETIME,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_visibility (visibility),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'down' => "DROP TABLE IF EXISTS resources;"
]; 