<?php

use App\Core\Database;

class UpdateResourceVisibility {
    public function up() {
        $db = Database::getInstance();
        
        // 1. visibility 컬럼 추가
        $db->query("ALTER TABLE resources ADD COLUMN visibility VARCHAR(10) DEFAULT 'public'");
        
        // 2. 기존 is_public 데이터를 visibility로 마이그레이션
        $db->query("UPDATE resources SET visibility = CASE WHEN is_public = 1 THEN 'public' ELSE 'private' END");
        
        // 3. is_public 컬럼 삭제
        $db->query("ALTER TABLE resources DROP COLUMN is_public");
    }

    public function down() {
        $db = Database::getInstance();
        
        // 1. is_public 컬럼 추가
        $db->query("ALTER TABLE resources ADD COLUMN is_public TINYINT(1) DEFAULT 1");
        
        // 2. visibility 데이터를 is_public으로 마이그레이션
        $db->query("UPDATE resources SET is_public = CASE WHEN visibility = 'public' THEN 1 ELSE 0 END");
        
        // 3. visibility 컬럼 삭제
        $db->query("ALTER TABLE resources DROP COLUMN visibility");
    }
} 