<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class CreateResourceViewsTable extends Migration
{
    protected $tableName = 'resource_views';

    public function up()
    {
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS `resource_views` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `resource_id` INT UNSIGNED NOT NULL,
                `viewer_id` INT UNSIGNED NOT NULL,
                `rating` DECIMAL(3,1) DEFAULT NULL,
                `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`viewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_view` (`resource_id`, `viewer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->connection->exec("DROP TABLE IF EXISTS `resource_views`");
    }
} 