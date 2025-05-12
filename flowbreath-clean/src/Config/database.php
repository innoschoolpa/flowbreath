<?php
namespace Config;

use PDO;
use PDOException;
use Exception;

/**
 * config/database.php
 * 데이터베이스 연결 설정 및 PDO 객체 관리
 */

error_log("Loading database.php from: " . __FILE__);

// --- 초기 설정 ---

// 캐싱 비활성화 (개발 중 테스트 시 유용)
// 실제 서비스 환경에서는 성능을 위해 활성화하는 것이 좋습니다.
// ini_set('opcache.enable', 0);

// 로드 확인 로그 (디버깅용)
error_log("database.php loaded successfully at " . date("Y-m-d H:i:s"));

// --- 오류 보고 및 로깅 설정 ---
error_reporting(E_ALL); // 모든 PHP 오류 보고
ini_set('display_errors', 1); // 화면에 오류 표시 (개발 환경용, 운영 환경에서는 0으로 설정 권장)
ini_set('log_errors', 1);     // 오류 로그 기록 활성화

// --- Database 클래스 정의 (Singleton 패턴) ---
class Database {
    private static $instance = null;
    private $connection = null;
    private $lastConnectionTime = null;
    private $connectionTimeout = 300; // 5분
    
    private function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            // 환경 변수에서 데이터베이스 설정 가져오기
            $host = getenv('DB_HOST') ?: 'srv636.hstgr.io';
            $dbname = getenv('DB_NAME') ?: 'u573434051_flowbreath';
            $username = getenv('DB_USER') ?: 'u573434051_flow';
            $password = getenv('DB_PASS') ?: 'Eduispa1712!';
            
            error_log("Attempting to connect to database: {$dbname} on {$host}");
            
            $this->connection = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true // 영구 연결 사용
                ]
            );
            
            $this->lastConnectionTime = time();
            error_log("Database connection established successfully");
            
            // 연결 직후 테스트 쿼리 실행
            $this->testConnection();
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("데이터베이스 연결에 실패했습니다: " . $e->getMessage());
        }
    }
    
    public function testConnection() {
        try {
            if (!$this->connection) {
                throw new Exception("데이터베이스 연결이 없습니다.");
            }
            
            // 연결 상태 확인
            $this->connection->query('SELECT 1');
            
            // 서버 상태 확인
            $status = $this->connection->query('SHOW STATUS')->fetchAll(PDO::FETCH_KEY_PAIR);
            error_log("Database server status: " . json_encode($status));
            
            // 연결 시간 확인
            $uptime = $this->connection->query('SHOW STATUS LIKE "Uptime"')->fetch(PDO::FETCH_ASSOC);
            error_log("Database uptime: " . json_encode($uptime));
            
            return true;
        } catch (PDOException $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            $this->reconnect();
            return false;
        }
    }
    
    public function reconnect() {
        try {
            if ($this->connection) {
                $this->connection = null;
            }
            $this->connect();
            return true;
        } catch (Exception $e) {
            error_log("Database reconnection failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConnection() {
        // 연결이 없거나 타임아웃된 경우 재연결
        if (!$this->connection || (time() - $this->lastConnectionTime) > $this->connectionTimeout) {
            $this->reconnect();
        }
        return $this->connection;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // 싱글톤 패턴을 위한 메서드들
    private function __clone() {}
    public function __wakeup() {
        // 싱글톤 패턴 유지
        self::$instance = $this;
    }
}

// 초기 연결 테스트
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 상세 연결 정보 로깅
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $charset = $pdo->query('SHOW VARIABLES LIKE "character_set_database"')->fetch(PDO::FETCH_ASSOC);
    
    error_log("Database connection test successful");
    error_log("MySQL Version: " . $version);
    error_log("Database Charset: " . json_encode($charset));
    
} catch (Exception $e) {
    error_log("Initial database connection test failed: " . $e->getMessage());
    throw $e;
}

?>
