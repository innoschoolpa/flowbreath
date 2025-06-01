<?php

use App\Core\Database;

class Migration_2024_06_01_add_deleted_at_to_diaries {
    public function up() {
        $db = Database::getInstance();
        
        // Add deleted_at column to diaries table
        $sql = "ALTER TABLE diaries ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL";
        $db->query($sql);
        
        // Add deleted_at column to diary_comments table
        $sql = "ALTER TABLE diary_comments ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL";
        $db->query($sql);
    }

    public function down() {
        $db = Database::getInstance();
        
        // Remove deleted_at column from diaries table
        $sql = "ALTER TABLE diaries DROP COLUMN deleted_at";
        $db->query($sql);
        
        // Remove deleted_at column from diary_comments table
        $sql = "ALTER TABLE diary_comments DROP COLUMN deleted_at";
        $db->query($sql);
    }
} 