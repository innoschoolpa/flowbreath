<?php

use App\Core\Database;

return function (Database $db) {
    $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NULL,
            role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
            google_id VARCHAR(255) NULL UNIQUE,
            profile_image VARCHAR(255) NULL,
            bio TEXT NULL,
            status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
            email_verified_at TIMESTAMP NULL,
            remember_token VARCHAR(100) NULL,
            failed_login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            login_count INT DEFAULT 0,
            last_login_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_google_id (google_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
}; 