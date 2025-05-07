<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateResourcesTable extends Migration
{
    protected $tableName = 'resources';

    public function up()
    {
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `resources` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `content` LONGTEXT,
                `type` VARCHAR(50) NOT NULL DEFAULT 'article',
                `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
                `visibility` VARCHAR(20) NOT NULL DEFAULT 'private',
                `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `published_at` TIMESTAMP NULL,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_type` (`type`),
                INDEX `idx_status` (`status`),
                INDEX `idx_visibility` (`visibility`),
                INDEX `idx_published_at` (`published_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 태그 관리를 위한 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `tags` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(50) NOT NULL,
                `slug` VARCHAR(50) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 리소스-태그 관계를 위한 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `resource_tags` (
                `resource_id` INT UNSIGNED NOT NULL,
                `tag_id` INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`resource_id`, `tag_id`),
                FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->connection->exec("DROP TABLE IF EXISTS `resource_tags`");
        $this->connection->exec("DROP TABLE IF EXISTS `tags`");
        $this->connection->exec("DROP TABLE IF EXISTS `resources`");
    }
} 