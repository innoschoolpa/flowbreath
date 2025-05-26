<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config = [];
    private static int $connectionCount = 0;
    private static int $lastConnectionTime = 0;
    private const MAX_CONNECTIONS_PER_HOUR = 300; // 호스팅 환경에 맞게 더 보수적으로 설정
    private const CONNECTION_TIMEOUT = 3600; // 1시간
    private const CONNECTION_IDLE_TIMEOUT = 180; // 3분으로 증가
    private const MAX_POOL_SIZE = 3; // 호스팅 환경에 맞게 축소
    private const MIN_POOL_SIZE = 1;
    private const CONNECTION_RETRY_DELAY = 2;
    private const MAX_RETRY_ATTEMPTS = 3;
    private const CACHE_ENABLED = true;
    private const CACHE_DEFAULT_TTL = 3600; // 1시간으로 감소
    private const CACHE_MAX_SIZE = 100; // 캐시 크기 감소
    private const THROTTLE_WINDOW = 60;
    private const MAX_CONNECTIONS_PER_MINUTE = 3; // 분당 연결 수 제한
    private static array $connectionQueue = [];
    private static int $lastThrottleReset = 0;
    private static int $connectionsThisMinute = 0;
    private static array $sharedConnections = [];
    private static int $lastConnectionCleanup = 0;
    private const CONNECTION_CLEANUP_INTERVAL = 300;
    private static array $reservedConnections = [];
    private const MAX_QUEUE_SIZE = 10; // 큐 크기 감소
    private const QUEUE_TIMEOUT = 10;
    private static array $connectionLocks = [];
    private const LOCK_TIMEOUT = 5;
    private bool $inTransaction = false;
    private array $statementCache = [];
    private int $maxCacheSize = 50; // 캐시 크기 감소
    private array $queryCache = [];
    private bool $cacheEnabled = true;
    private int $defaultCacheTime = 900; // 15분으로 감소
    private int $lastQueryTime = 0;
    private int $queryCount = 0;
    private int $maxQueriesPerMinute = 100; // 쿼리 수 제한 감소
    private array $preparedStatements = [];
    private int $lastCacheCleanup = 0;
    private int $cacheCleanupInterval = 300;
    private array $connectionPool = [];
    private array $connectionTimeouts = [];
    private int $currentPoolSize = 0;
    private static array $connectionUsage = [];
    private const CONNECTION_REUSE_THRESHOLD = 0.6; // 60%에서 재사용 시작
    private static array $connectionHistory = [];
    private const CONNECTION_HISTORY_WINDOW = 3600;
    private const THROTTLE_DELAY = 2; // 지연 시간 증가
    private int $lastUsed = 0;
    private int $activeConnections = 0;
    private ?PDOStatement $lastStatement = null;

    private function __construct()
    {
        $this->loadConfig();
        $this->initializeConnection();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig()
    {
        try {
            $app = Application::getInstance();
            $config = $app->getConfig('database');
            
            if (empty($config)) {
                error_log("Database configuration not found in Application config. Attempting to load from file...");
                
                // 호스팅 환경의 실제 경로 사용
                $possiblePaths = [
                    '/domains/flowbreath.io/public_html/config/database.php',
                    '/domains/flowbreath.io/public_html/src/Config/database.php',
                    $_SERVER['DOCUMENT_ROOT'] . '/config/database.php',
                    dirname($_SERVER['DOCUMENT_ROOT']) . '/config/database.php',
                    __DIR__ . '/../../config/database.php',
                    __DIR__ . '/../Config/database.php',
                ];

                $dbConfigPath = null;
                foreach ($possiblePaths as $path) {
                    error_log("Trying database config path: " . $path);
                    if (file_exists($path)) {
                        $dbConfigPath = $path;
                        error_log("Found database config at: " . $path);
                        break;
                    }
                }

                if (!$dbConfigPath) {
                    throw new \Exception("Database configuration file not found in any of the possible locations");
                }

                $config = require $dbConfigPath;
            }

            if (empty($config)) {
                throw new \Exception("Database configuration is empty");
            }

            // 기본 설정값 정의
            $this->config = [
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_NAME'] ?? '',
                'username' => $_ENV['DB_USER'] ?? '',
                'password' => $_ENV['DB_PASS'] ?? '',
                'charset' => 'utf8mb4'
            ];

            // 설정 병합
            $this->config = array_merge($this->config, $config);

            // 필수 설정 확인
            $requiredFields = ['host', 'database', 'username', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($this->config[$field])) {
                    throw new \Exception("Required database configuration field '{$field}' is missing");
                }
            }

            error_log("Database configuration loaded successfully");
        } catch (\Exception $e) {
            error_log("Failed to load database configuration: " . $e->getMessage());
            throw $e;
        }
    }

    private function initializeConnection()
    {
        try {
            if ($this->connection === null) {
                error_log("Database::initializeConnection() - Creating new connection");
                
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database']
                );
                
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    \PDO::ATTR_TIMEOUT => 5, // 5초 타임아웃
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ];
                
                error_log("Database::initializeConnection() - Connecting to database with DSN: " . $dsn);
                
                $this->connection = new \PDO($dsn, $this->config['username'], $this->config['password'], $options);
                
                // 연결 테스트 및 설정
                $this->connection->query("SELECT 1");
                $this->connection->exec("SET SESSION wait_timeout=300");
                $this->connection->exec("SET SESSION interactive_timeout=300");
                $this->connection->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
                
                error_log("Database::initializeConnection() - Connection successful");
                
                $this->lastUsed = time();
                $this->activeConnections++;
            } else {
                // 기존 연결 상태 확인
                try {
                    $this->connection->query("SELECT 1");
                    $this->lastUsed = time();
                    error_log("Database::initializeConnection() - Using existing connection");
                } catch (\PDOException $e) {
                    error_log("Database::initializeConnection() - Existing connection failed, creating new one");
                    $this->connection = null;
                    $this->initializeConnection();
                }
            }
        } catch (\PDOException $e) {
            error_log("Database::initializeConnection() - Connection failed: " . $e->getMessage());
            error_log("Database::initializeConnection() - SQL State: " . $e->getCode());
            error_log("Database::initializeConnection() - Error Info: " . json_encode($e->errorInfo));
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        try {
            if ($this->connection === null) {
                $this->initializeConnection();
            } else {
                // 연결 상태 확인 및 필요시 재연결
                try {
                    $this->connection->query("SELECT 1");
                    $this->lastUsed = time();
                } catch (\PDOException $e) {
                    error_log("Database::getConnection() - Connection lost, reconnecting");
                    $this->connection = null;
                    $this->initializeConnection();
                }
            }
            return $this->connection;
        } catch (\Exception $e) {
            error_log("Database::getConnection() - Failed to get connection: " . $e->getMessage());
            throw $e;
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
        
        // Clean up connection history
        self::$connectionHistory = array_filter(self::$connectionHistory, function($time) use ($currentTime) {
            return ($currentTime - $time) < self::CONNECTION_HISTORY_WINDOW;
        });

        // Clean up idle connections
        foreach ($this->connectionPool as $key => $connection) {
            $hash = spl_object_hash($connection);
            if (isset($this->connectionTimeouts[$hash]) && 
                ($currentTime - $this->connectionTimeouts[$hash] > self::CONNECTION_IDLE_TIMEOUT)) {
                try {
                    $connection = null;
                } catch (\Exception $e) {
                    error_log("Error closing idle connection: " . $e->getMessage());
                }
                unset($this->connectionTimeouts[$hash]);
                unset($this->connectionPool[$key]);
                $this->currentPoolSize--;
            }
        }

        // Reindex array after cleanup
        $this->connectionPool = array_values($this->connectionPool);
    }

    private function getConnectionFromPool()
    {
        $this->cleanupConnections();
        $currentTime = time();
        $processId = getmypid();

        // Reset connection count if hour has passed
        if ($currentTime - self::$lastConnectionTime > self::CONNECTION_TIMEOUT) {
            self::$connectionCount = 0;
            self::$lastConnectionTime = $currentTime;
            self::$connectionUsage = [];
            self::$connectionHistory = [];
        }

        // Check per-minute connection limit
        if ($currentTime - self::$lastThrottleReset >= self::THROTTLE_WINDOW) {
            self::$connectionsThisMinute = 0;
            self::$lastThrottleReset = $currentTime;
        }

        if (self::$connectionsThisMinute >= self::MAX_CONNECTIONS_PER_MINUTE) {
            error_log("Connection rate limit exceeded: " . self::$connectionsThisMinute . " connections per minute");
            sleep(self::THROTTLE_DELAY);
        }

        // Check hourly connection limit with buffer
        if (self::$connectionCount >= (self::MAX_CONNECTIONS_PER_HOUR * self::CONNECTION_REUSE_THRESHOLD)) {
            error_log("Warning: Approaching database connection limit: " . self::$connectionCount . " connections in the last hour");
            
            // Force connection reuse when approaching limit
            if (!empty($this->connectionPool)) {
                foreach ($this->connectionPool as $key => $connection) {
                    if ($this->checkConnection($connection)) {
                        unset($this->connectionPool[$key]);
                        $this->connectionPool = array_values($this->connectionPool);
                        self::$sharedConnections[$processId] = $connection;
                        self::$connectionUsage[$processId] = $currentTime;
                        self::$connectionHistory[] = $currentTime;
                        return $connection;
                    }
                }
            }
        }

        if (self::$connectionCount >= self::MAX_CONNECTIONS_PER_HOUR) {
            error_log("Database connection limit reached: " . self::$connectionCount . " connections in the last hour");
            throw new PDOException("Database connection limit exceeded. Please try again later.");
        }

        // Try to reuse existing connection from pool first
        if (!empty($this->connectionPool)) {
            foreach ($this->connectionPool as $key => $connection) {
                if ($this->checkConnection($connection)) {
                    unset($this->connectionPool[$key]);
                    $this->connectionPool = array_values($this->connectionPool);
                    self::$sharedConnections[$processId] = $connection;
                    self::$connectionUsage[$processId] = $currentTime;
                    self::$connectionHistory[] = $currentTime;
                    return $connection;
                }
            }
        }

        // If no reusable connection, create new one if under pool size limit
        if ($this->currentPoolSize < self::MAX_POOL_SIZE) {
            try {
                $connection = $this->createConnection();
                self::$connectionCount++;
                self::$connectionsThisMinute++;
                self::$sharedConnections[$processId] = $connection;
                self::$connectionUsage[$processId] = $currentTime;
                self::$connectionHistory[] = $currentTime;
                return $connection;
            } catch (\Exception $e) {
                error_log("Failed to create new connection: " . $e->getMessage());
                throw $e;
            }
        }

        // If pool is full, try to reuse oldest connection
        $oldestConnection = array_shift($this->connectionPool);
        if ($oldestConnection && $this->checkConnection($oldestConnection)) {
            self::$sharedConnections[$processId] = $oldestConnection;
            self::$connectionUsage[$processId] = $currentTime;
            self::$connectionHistory[] = $currentTime;
            return $oldestConnection;
        }

        throw new PDOException("No available database connections");
    }

    private function releaseConnection($connection)
    {
        if ($connection && count($this->connectionPool) < self::MAX_POOL_SIZE) {
            if ($this->checkConnection($connection)) {
                try {
                    // Reset connection state
                    $connection->exec("SET SESSION wait_timeout=300");
                    $connection->exec("SET SESSION interactive_timeout=300");
                    $connection->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
                    
                    // Add to pool
                    $this->connectionPool[] = $connection;
                    $this->connectionTimeouts[spl_object_hash($connection)] = time();
                    
                    // Clean up usage tracking
                    $processId = getmypid();
                    unset(self::$connectionUsage[$processId]);
                } catch (\Exception $e) {
                    error_log("Error resetting connection state: " . $e->getMessage());
                    $this->currentPoolSize--;
                }
            } else {
                $this->currentPoolSize--;
            }
        } else {
            try {
                $connection = null;
            } catch (\Exception $e) {
                error_log("Error closing connection: " . $e->getMessage());
            }
            $this->currentPoolSize--;
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            // 캐시 키 생성
            $cacheKey = md5($sql . json_encode($params));
            
            // SELECT 쿼리이고 캐시가 활성화된 경우 캐시 확인
            if (self::CACHE_ENABLED && stripos(trim($sql), 'SELECT') === 0) {
                $cachedResult = $this->getCache($cacheKey);
                if ($cachedResult !== null) {
                    error_log("Cache hit for query: " . $sql);
                    return $cachedResult;
                }
            }

            // 쿼리 수 증가
            $this->queryCount++;
            $currentTime = time();

            // 분당 쿼리 수 제한 확인
            if ($currentTime - $this->lastQueryTime >= 60) {
                $this->queryCount = 0;
                $this->lastQueryTime = $currentTime;
            } elseif ($this->queryCount >= $this->maxQueriesPerMinute) {
                error_log("Query rate limit exceeded: " . $this->queryCount . " queries per minute");
                throw new PDOException("Too many queries. Please try again later.");
            }

            // 캐시 정리
            if ($currentTime - $this->lastCacheCleanup >= $this->cacheCleanupInterval) {
                $this->cleanupCache();
            }

            // 준비된 구문 재사용
            if (!isset($this->preparedStatements[$sql])) {
                $this->preparedStatements[$sql] = $this->getConnection()->prepare($sql);
            }
            
            $stmt = $this->preparedStatements[$sql];
            $stmt->execute($params);
            $this->lastStatement = $stmt;  // 마지막 실행된 statement 저장

            // SELECT 쿼리 결과 캐싱
            if (self::CACHE_ENABLED && stripos(trim($sql), 'SELECT') === 0) {
                $this->setCache($cacheKey, $stmt, self::CACHE_DEFAULT_TTL);
            }

            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            throw $e;
        }
    }

    private function cleanupCache()
    {
        $currentTime = time();
        foreach ($this->queryCache as $key => $cache) {
            if ($cache['expires'] < $currentTime) {
                unset($this->queryCache[$key]);
            }
        }
        $this->lastCacheCleanup = $currentTime;
    }

    public function getCache($key)
    {
        if (!self::CACHE_ENABLED) {
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
        if (!self::CACHE_ENABLED) {
            return;
        }

        $time = $time ?? self::CACHE_DEFAULT_TTL;
        $this->queryCache[$key] = [
            'data' => $data,
            'expires' => time() + $time
        ];

        // 캐시 크기 제한
        if (count($this->queryCache) > self::CACHE_MAX_SIZE) {
            $this->cleanupCache();
        }
    }

    public function __destruct()
    {
        try {
            $this->queryCache = [];
            $this->preparedStatements = [];
            $this->statementCache = [];

            if ($this->inTransaction) {
                $this->rollBack();
            }

            $processId = getmypid();
            
            // Clean up connection usage tracking
            unset(self::$connectionUsage[$processId]);

            // Clean up shared connections
            if (isset(self::$sharedConnections[$processId])) {
                try {
                    $connection = self::$sharedConnections[$processId];
                    if ($connection && $this->checkConnection($connection)) {
                        $this->releaseConnection($connection);
                    }
                    self::$sharedConnections[$processId] = null;
                } catch (\Exception $e) {
                    error_log("Error closing shared connection in destructor: " . $e->getMessage());
                }
                unset(self::$sharedConnections[$processId]);
            }

            if ($this->connection) {
                $this->connection = null;
            }
        } catch (\Exception $e) {
            error_log("Error in Database destructor: " . $e->getMessage());
        }
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Database fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database fetchAll error: " . $e->getMessage());
            throw $e;
        }
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
        return $this->getConnection()->lastInsertId();
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
        $this->query($sql, $params);
        return $this->getRowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
        $this->query($sql, $params);
        return $this->getRowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    public function quote($value)
    {
        return $this->getConnection()->quote($value);
    }

    public function getLastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    public function getRowCount(): int
    {
        if ($this->lastStatement === null) {
            error_log("Database::getRowCount() - No statement has been executed");
            return 0;
        }
        return $this->lastStatement->rowCount();
    }

    public function getConnectionPoolSize()
    {
        return [
            'current' => $this->currentPoolSize,
            'max' => self::MAX_POOL_SIZE,
            'available' => count($this->connectionPool)
        ];
    }

    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    public function clearCache()
    {
        $this->queryCache = [];
    }

    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    public function getTables()
    {
        return $this->fetchAll("SHOW TABLES");
    }

    public function getTableStructure($table)
    {
        return $this->fetchAll("SHOW COLUMNS FROM `$table`");
    }

    /**
     * SQL 문을 준비하고 PDOStatement를 반환합니다.
     *
     * @param string $sql SQL 쿼리문
     * @return \PDOStatement
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    private function createConnection()
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ];

            $connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            
            // 연결 설정 최적화
            $connection->exec("SET SESSION wait_timeout=300");
            $connection->exec("SET SESSION interactive_timeout=300");
            $connection->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            
            $this->connectionPool[] = $connection;
            $this->connectionTimeouts[spl_object_hash($connection)] = time();
            $this->currentPoolSize++;
            
            return $connection;
        } catch (PDOException $e) {
            error_log("Failed to create new connection: " . $e->getMessage());
            throw $e;
        }
    }

    public function resetConnectionCount()
    {
        self::$connectionCount = 0;
    }

    public function closeConnection()
    {
        if ($this->connection !== null) {
            $this->connection = null;
        }
    }
} 