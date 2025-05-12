<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class GoogleFieldsToUsersTable extends Migration
{
    protected $tableName = 'users';

    public function up()
    {
        $sql = "ALTER TABLE {$this->tableName} 
                ADD COLUMN google_id VARCHAR(255) NULL UNIQUE,
                ADD COLUMN profile_picture VARCHAR(255) NULL,
                ADD COLUMN email_verified BOOLEAN DEFAULT FALSE,
                ADD COLUMN auth_provider VARCHAR(50) DEFAULT 'local',
                ADD INDEX idx_google_id (google_id),
                ADD INDEX idx_auth_provider (auth_provider)";
        
        $this->db->exec($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE {$this->tableName} 
                DROP COLUMN google_id,
                DROP COLUMN profile_picture,
                DROP COLUMN email_verified,
                DROP COLUMN auth_provider";
        
        $this->db->exec($sql);
    }
} 