<?php

class FixResourceId {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    public function up() {
        try {
            // id 컬럼을 AUTO_INCREMENT로 수정
            $sql = "ALTER TABLE resources MODIFY id int(11) NOT NULL AUTO_INCREMENT";
            $this->db->query($sql);
            
            // AUTO_INCREMENT 시작값 설정
            $sql = "ALTER TABLE resources AUTO_INCREMENT = 1";
            $this->db->query($sql);
            
            error_log("Successfully modified resources table id column to AUTO_INCREMENT");
        } catch (\Exception $e) {
            error_log("Error in FixResourceId migration: " . $e->getMessage());
            throw $e;
        }
    }

    public function down() {
        try {
            // AUTO_INCREMENT 제거
            $sql = "ALTER TABLE resources MODIFY id int(11) NOT NULL";
            $this->db->query($sql);
            
            error_log("Successfully reverted resources table id column");
        } catch (\Exception $e) {
            error_log("Error in FixResourceId migration rollback: " . $e->getMessage());
            throw $e;
        }
    }
} 