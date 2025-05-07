<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateAuthTables extends Migration
{
    protected $tableName = 'users'; // 주 테이블 이름

    public function up()
    {
        // users 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `provider` VARCHAR(50) NULL,
                `provider_id` VARCHAR(255) NULL,
                `email_verified_at` TIMESTAMP NULL,
                `remember_token` VARCHAR(100) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_email` (`email`),
                INDEX `idx_provider` (`provider`, `provider_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // password_resets 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `password_resets` (
                `email` VARCHAR(255) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `expires_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`email`, `token`),
                INDEX `idx_token` (`token`),
                INDEX `idx_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // social_accounts 테이블 생성 (소셜 로그인 계정 정보 저장)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `social_accounts` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `provider` VARCHAR(50) NOT NULL,
                `provider_id` VARCHAR(255) NOT NULL,
                `token` TEXT NULL,
                `refresh_token` TEXT NULL,
                `expires_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_provider_account` (`provider`, `provider_id`),
                INDEX `idx_user_id` (`user_id`),
                CONSTRAINT `fk_social_accounts_user_id` FOREIGN KEY (`user_id`) 
                    REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // user_sessions 테이블 생성 (사용자 세션 관리)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `user_sessions` (
                `id` VARCHAR(255) PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` TEXT NULL,
                `payload` TEXT NOT NULL,
                `last_activity` TIMESTAMP NOT NULL,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_last_activity` (`last_activity`),
                CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) 
                    REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        // 테이블 삭제 (역순으로)
        $this->connection->exec("DROP TABLE IF EXISTS `user_sessions`");
        $this->connection->exec("DROP TABLE IF EXISTS `social_accounts`");
        $this->connection->exec("DROP TABLE IF EXISTS `password_resets`");
        $this->connection->exec("DROP TABLE IF EXISTS `users`");
    }
}