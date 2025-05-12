<?php

namespace App\Database;

use PDO;
use Exception;

abstract class Migration {
    protected $db;
    protected $table;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    abstract public function up();
    abstract public function down();

    protected function createTable($table, $columns) {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS {$table} (";
            $sql .= implode(', ', $columns);
            $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("테이블 생성 실패: " . $e->getMessage());
        }
    }

    protected function dropTable($table) {
        try {
            $sql = "DROP TABLE IF EXISTS {$table};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("테이블 삭제 실패: " . $e->getMessage());
        }
    }

    protected function addColumn($table, $column, $definition) {
        try {
            $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$definition};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("컬럼 추가 실패: " . $e->getMessage());
        }
    }

    protected function dropColumn($table, $column) {
        try {
            $sql = "ALTER TABLE {$table} DROP COLUMN {$column};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("컬럼 삭제 실패: " . $e->getMessage());
        }
    }

    protected function addIndex($table, $index, $columns) {
        try {
            $sql = "CREATE INDEX {$index} ON {$table} ({$columns});";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("인덱스 추가 실패: " . $e->getMessage());
        }
    }

    protected function dropIndex($table, $index) {
        try {
            $sql = "DROP INDEX {$index} ON {$table};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("인덱스 삭제 실패: " . $e->getMessage());
        }
    }

    protected function addForeignKey($table, $name, $columns, $referenceTable, $referenceColumns, $onDelete = 'CASCADE', $onUpdate = 'CASCADE') {
        try {
            $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} FOREIGN KEY ({$columns}) REFERENCES {$referenceTable} ({$referenceColumns}) ON DELETE {$onDelete} ON UPDATE {$onUpdate};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("외래 키 추가 실패: " . $e->getMessage());
        }
    }

    protected function dropForeignKey($table, $name) {
        try {
            $sql = "ALTER TABLE {$table} DROP FOREIGN KEY {$name};";
            $this->db->exec($sql);
            return true;
        } catch (Exception $e) {
            throw new Exception("외래 키 삭제 실패: " . $e->getMessage());
        }
    }
} 