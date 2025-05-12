<?php

namespace App\Core;

class ResourceManager
{
    private static $instance = null;
    private $db;
    private $tableName = 'system_resources';

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->initializeTable();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resource_type VARCHAR(50) NOT NULL,
            resource_name VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            version VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_resource (resource_type, resource_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->query($sql);
    }

    public function storeSQLResource($name, $content, $version = '1.0')
    {
        try {
            $sql = "INSERT INTO {$this->tableName} 
                    (resource_type, resource_name, content, version) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    content = VALUES(content),
                    version = VALUES(version)";

            $this->db->query($sql, ['sql', $name, $content, $version]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to store SQL resource: " . $e->getMessage());
            return false;
        }
    }

    public function getSQLResource($name)
    {
        try {
            $sql = "SELECT content, version FROM {$this->tableName} 
                    WHERE resource_type = ? AND resource_name = ?";
            
            $result = $this->db->query($sql, ['sql', $name])->fetch();
            return $result ? $result['content'] : null;
        } catch (\Exception $e) {
            error_log("Failed to retrieve SQL resource: " . $e->getMessage());
            return null;
        }
    }

    public function listSQLResources()
    {
        try {
            $sql = "SELECT resource_name, version, created_at, updated_at 
                    FROM {$this->tableName} 
                    WHERE resource_type = ? 
                    ORDER BY resource_name";
            
            return $this->db->query($sql, ['sql'])->fetchAll();
        } catch (\Exception $e) {
            error_log("Failed to list SQL resources: " . $e->getMessage());
            return [];
        }
    }

    public function deleteSQLResource($name)
    {
        try {
            $sql = "DELETE FROM {$this->tableName} 
                    WHERE resource_type = ? AND resource_name = ?";
            
            $this->db->query($sql, ['sql', $name]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to delete SQL resource: " . $e->getMessage());
            return false;
        }
    }

    public function updateSQLResource($name, $content, $version = null)
    {
        try {
            if ($version === null) {
                $sql = "UPDATE {$this->tableName} 
                        SET content = ? 
                        WHERE resource_type = ? AND resource_name = ?";
                $params = [$content, 'sql', $name];
            } else {
                $sql = "UPDATE {$this->tableName} 
                        SET content = ?, version = ? 
                        WHERE resource_type = ? AND resource_name = ?";
                $params = [$content, $version, 'sql', $name];
            }
            
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update SQL resource: " . $e->getMessage());
            return false;
        }
    }
} 