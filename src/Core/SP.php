<?php

namespace App\Core;

class SP
{
    private static $instance = null;
    private $db;
    private $config = [];

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig()
    {
        $app = Application::getInstance();
        $this->config = $app->getConfig('database');
    }

    public function create($name, $parameters, $body)
    {
        $sql = "CREATE PROCEDURE `{$name}` ({$parameters}) BEGIN {$body} END";
        return $this->db->query($sql);
    }

    public function drop($name)
    {
        $sql = "DROP PROCEDURE IF EXISTS `{$name}`";
        return $this->db->query($sql);
    }

    public function exists($name)
    {
        $sql = "SELECT COUNT(*) as count FROM information_schema.routines 
                WHERE routine_type = 'PROCEDURE' 
                AND routine_schema = '{$this->config['database']}' 
                AND routine_name = '{$name}'";
        
        $result = $this->db->query($sql)->fetch();
        return $result->count > 0;
    }

    public function call($name, $parameters = [])
    {
        $params = implode(', ', array_fill(0, count($parameters), '?'));
        $sql = "CALL `{$name}`({$params})";
        return $this->db->query($sql, $parameters);
    }

    public function getDefinition($name)
    {
        $sql = "SHOW CREATE PROCEDURE `{$name}`";
        $result = $this->db->query($sql)->fetch();
        return $result->{'Create Procedure'} ?? null;
    }

    public function listAll()
    {
        $sql = "SELECT routine_name, created, modified, security_type, definer 
                FROM information_schema.routines 
                WHERE routine_type = 'PROCEDURE' 
                AND routine_schema = '{$this->config['database']}'";
        
        return $this->db->query($sql)->fetchAll();
    }

    public function getParameters($name)
    {
        $sql = "SELECT parameter_name, data_type, parameter_mode 
                FROM information_schema.parameters 
                WHERE specific_schema = '{$this->config['database']}' 
                AND specific_name = '{$name}' 
                ORDER BY ordinal_position";
        
        return $this->db->query($sql)->fetchAll();
    }

    public function alter($name, $parameters, $body)
    {
        // MySQL에서는 ALTER PROCEDURE가 제한적이므로, DROP 후 CREATE로 대체
        $this->drop($name);
        return $this->create($name, $parameters, $body);
    }

    public function grant($name, $user, $host = '%')
    {
        $sql = "GRANT EXECUTE ON PROCEDURE `{$this->config['database']}`.`{$name}` TO '{$user}'@'{$host}'";
        return $this->db->query($sql);
    }

    public function revoke($name, $user, $host = '%')
    {
        $sql = "REVOKE EXECUTE ON PROCEDURE `{$this->config['database']}`.`{$name}` FROM '{$user}'@'{$host}'";
        return $this->db->query($sql);
    }

    public function getStatus($name)
    {
        $sql = "SHOW PROCEDURE STATUS WHERE Db = '{$this->config['database']}' AND Name = '{$name}'";
        return $this->db->query($sql)->fetch();
    }

    public function validate($name, $parameters = [])
    {
        if (!$this->exists($name)) {
            return false;
        }

        $expectedParams = $this->getParameters($name);
        if (count($parameters) !== count($expectedParams)) {
            return false;
        }

        return true;
    }

    public function backup($name)
    {
        $definition = $this->getDefinition($name);
        if (!$definition) {
            return false;
        }

        $backupDir = $this->config['backup_path'] ?? 'database/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $backupDir . '/' . $name . '_' . date('Y-m-d_H-i-s') . '.sql';
        return file_put_contents($filename, $definition) !== false;
    }

    public function restore($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $sql = file_get_contents($filename);
        return $this->db->query($sql);
    }
} 