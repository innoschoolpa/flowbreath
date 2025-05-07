<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection = null;
    private $config = [];
    private $inTransaction = false;
    private $statementCache = [];
    private $maxCacheSize = 100;
    private $connectionPool = [];
    private $maxPoolSize = 5;
    private $currentPoolSize = 0;
    private $connectionTimeouts = [];
    private $connectionTimeout = 300; // 5분
    private $minPoolSize = 2;
    private $poolCheckInterval = 60; // 1분
    private $lastPoolCheck = 0;
    private $queryCache = [];
    private $cacheEnabled = true;
    private $defaultCacheTime = 300; // 5분

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeConnectionPool();
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
        
        if (empty($this->config)) {
            throw new \Exception("Database configuration not found");
        }

        // 설정에서 연결 풀 관련 값 로드
        $this->maxPoolSize = $this->config['max_pool_size'] ?? $this->maxPoolSize;
        $this->minPoolSize = $this->config['min_pool_size'] ?? $this->minPoolSize;
        $this->connectionTimeout = $this->config['connection_timeout'] ?? $this->connectionTimeout;
        $this->poolCheckInterval = $this->config['pool_check_interval'] ?? $this->poolCheckInterval;
        $this->cacheEnabled = $this->config['query_cache_enabled'] ?? $this->cacheEnabled;
        $this->defaultCacheTime = $this->config['query_cache_time'] ?? $this->defaultCacheTime;
    }

    private function initializeConnectionPool()
    {
        for ($i = 0; $i < $this->minPoolSize; $i++) {
            $this->createConnection();
        }
    }

    private function createConnection()
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => false
            ];

            $connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            $this->connectionPool[] = $connection;
            $this->connectionTimeouts[spl_object_hash($connection)] = time();
            $this->currentPoolSize++;

            return $connection;
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function checkConnection($connection)
    {
        try {
            $connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function cleanupConnections()
    {
        $currentTime = time();
        if ($currentTime - $this->lastPoolCheck < $this->poolCheckInterval) {
            return;
        }

        $this->lastPoolCheck = $currentTime;
        $validConnections = [];

        foreach ($this->connectionPool as $connection) {
            $hash = spl_object_hash($connection);
            if ($currentTime - $this->connectionTimeouts[$hash] > $this->connectionTimeout) {
                $connection = null;
                unset($this->connectionTimeouts[$hash]);
                $this->currentPoolSize--;
            } elseif ($this->checkConnection($connection)) {
                $validConnections[] = $connection;
            } else {
                $connection = null;
                unset($this->connectionTimeouts[$hash]);
                $this->currentPoolSize--;
            }
        }

        $this->connectionPool = $validConnections;

        // 최소 연결 수 유지
        while ($this->currentPoolSize < $this->minPoolSize) {
            $this->createConnection();
        }
    }

    private function getConnection()
    {
        $this->cleanupConnections();

        if (empty($this->connectionPool)) {
            if ($this->currentPoolSize < $this->maxPoolSize) {
                return $this->createConnection();
            }
            throw new \Exception("No available database connections");
        }

        $connection = array_shift($this->connectionPool);
        if (!$this->checkConnection($connection)) {
            $this->currentPoolSize--;
            return $this->getConnection();
        }

        return $connection;
    }

    private function releaseConnection($connection)
    {
        if ($connection && count($this->connectionPool) < $this->maxPoolSize) {
            if ($this->checkConnection($connection)) {
                $this->connectionPool[] = $connection;
                $this->connectionTimeouts[spl_object_hash($connection)] = time();
            } else {
                $this->currentPoolSize--;
            }
        }
    }

    public function query($sql, $params = [])
    {
        try {
            $cacheKey = md5($sql . serialize($params));
            
            if (isset($this->statementCache[$cacheKey])) {
                $stmt = $this->statementCache[$cacheKey];
                $stmt->execute($params);
                return $stmt;
            }

            $connection = $this->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);

            if (count($this->statementCache) >= $this->maxCacheSize) {
                array_shift($this->statementCache);
            }

            $this->statementCache[$cacheKey] = $stmt;
            $this->releaseConnection($connection);
            
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $this->query($sql, array_values($data));
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $fields),
            $where
        );

        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        return $this->query($sql, $params)->rowCount();
    }

    public function beginTransaction()
    {
        if (!$this->inTransaction) {
            $this->connection = $this->getConnection();
            if (!$this->checkConnection($this->connection)) {
                $this->connection = $this->getConnection();
            }
            $this->connection->beginTransaction();
            $this->inTransaction = true;
        }
    }

    public function commit()
    {
        if ($this->inTransaction) {
            $this->connection->commit();
            $this->releaseConnection($this->connection);
            $this->connection = null;
            $this->inTransaction = false;
        }
    }

    public function rollback()
    {
        if ($this->inTransaction) {
            $this->connection->rollBack();
            $this->releaseConnection($this->connection);
            $this->connection = null;
            $this->inTransaction = false;
        }
    }

    public function quote($value)
    {
        return $this->connection->quote($value);
    }

    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function getRowCount()
    {
        return $this->connection->rowCount();
    }

    public function getConnectionPoolSize()
    {
        return [
            'current' => $this->currentPoolSize,
            'max' => $this->maxPoolSize,
            'available' => count($this->connectionPool)
        ];
    }

    public function lastInsertId()
    {
        $connection = $this->getConnection();
        $id = $connection->lastInsertId();
        $this->releaseConnection($connection);
        return $id;
    }

    public function getCache($key)
    {
        if (!$this->cacheEnabled) {
            return null;
        }

        if (isset($this->queryCache[$key])) {
            $cache = $this->queryCache[$key];
            if ($cache['expires'] > time()) {
                return $cache['data'];
            }
            unset($this->queryCache[$key]);
        }
        return null;
    }

    public function setCache($key, $data, $time = null)
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $time = $time ?? $this->defaultCacheTime;
        $this->queryCache[$key] = [
            'data' => $data,
            'expires' => time() + $time
        ];

        // 캐시 크기 제한
        if (count($this->queryCache) > 1000) {
            array_shift($this->queryCache);
        }
    }

    public function clearCache()
    {
        $this->queryCache = [];
    }

    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    public function __destruct()
    {
        foreach ($this->connectionPool as $connection) {
            $connection = null;
        }
        $this->connectionPool = [];
        $this->connectionTimeouts = [];
        $this->currentPoolSize = 0;
        $this->statementCache = [];
        $this->queryCache = [];
    }
} 