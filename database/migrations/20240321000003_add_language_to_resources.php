<?php

use App\Core\Database;

class AddLanguageToResources
{
    public function up()
    {
        $pdo = Database::getInstance()->getConnection();
        
        // resources 테이블에 language 필드 추가
        $pdo->exec("ALTER TABLE resources ADD COLUMN language VARCHAR(2) NOT NULL DEFAULT 'ko' AFTER content");
        
        // language 필드에 인덱스 추가
        $pdo->exec("CREATE INDEX idx_resources_language ON resources(language)");
    }

    public function down()
    {
        $pdo = Database::getInstance()->getConnection();
        
        // 인덱스 삭제
        $pdo->exec("DROP INDEX IF EXISTS idx_resources_language ON resources");
        
        // language 필드 삭제
        $pdo->exec("ALTER TABLE resources DROP COLUMN language");
    }
} 