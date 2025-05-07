<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateBookmarksTable extends Migration
{
    protected $tableName = 'bookmarks';

    public function up()
    {
        // bookmarks 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `bookmarks` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `resource_id` INT UNSIGNED NOT NULL,
                `folder_name` VARCHAR(50) DEFAULT 'default',
                `note` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_bookmark` (`user_id`, `resource_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // resource_shares 테이블 생성
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `resource_shares` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `resource_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `share_token` VARCHAR(64) NOT NULL,
                `expires_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_share` (`resource_id`, `share_token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->connection->exec("DROP TABLE IF EXISTS `bookmarks`");
        $this->connection->exec("DROP TABLE IF EXISTS `resource_shares`");
    }
} 