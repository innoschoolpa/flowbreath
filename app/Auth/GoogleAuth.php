<?php

namespace App\Auth;

use PDO;
use PDOException;
use Exception;

/**
 * Google 인증 클래스
 * 개선된 Google 로그인 기능을 제공하는 클래스
 * 신규 사용자 로그인 처리 강화
 */
class GoogleAuth
{
    private $db;
    private $config;
    private static $instance = null;

    private function __construct()
    {
        try {
            $this->config = $this->loadConfig();
            $this->db = $this->getDbConnection();
            $this->ensureDatabaseTable();
        } catch (Exception $e) {
            throw new Exception('초기화 중 오류가 발생했습니다: ' . $e->getMessage());
        }
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
        $configPath = __DIR__ . '/../../config/auth.php';
        if (!file_exists($configPath)) {
            throw new Exception("인증 설정 파일을 찾을 수 없습니다: {$configPath}");
        }
        $config = require $configPath;

        $requiredKeys = ['google' => ['client_id', 'client_secret', 'redirect_uri']];
        foreach ($requiredKeys as $section => $keys) {
            if (!isset($config[$section])) {
                throw new Exception("설정 섹션이 누락되었습니다: {$section}");
            }
            foreach ($keys as $key) {
                if (empty($config[$section][$key])) {
                    throw new Exception("설정 키가 누락되었습니다: {$section}.{$key}");
                }
            }
        }

        return $config;
    }

    private function getDbConnection()
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>데이터베이스 연결</h3>";

            $dbConfigPath = __DIR__ . '/../../config/database.php';
            if (!file_exists($dbConfigPath)) {
                throw new Exception("데이터베이스 설정 파일을 찾을 수 없습니다: {$dbConfigPath}");
            }
            $dbConfig = require $dbConfigPath;

            // Verify required database configuration
            $requiredConfig = ['host', 'database', 'username', 'password'];
            foreach ($requiredConfig as $key) {
                if (!isset($dbConfig[$key]) || empty($dbConfig[$key])) {
                    throw new Exception("데이터베이스 설정이 누락되었습니다: {$key}");
                }
            }

            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];

            echo "<p>ℹ️ 데이터베이스 연결 시도 중...</p>";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            
            // Test the connection
            $pdo->query("SELECT 1");
            
            // Set character set
            $pdo->exec("SET NAMES utf8mb4");
            $pdo->exec("SET CHARACTER SET utf8mb4");
            $pdo->exec("SET character_set_connection=utf8mb4");
            
            echo "<p style='color: green;'>✅ 데이터베이스 연결 성공</p>";
            echo "</div>";
            
            return $pdo;
        } catch (PDOException $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 데이터베이스 연결 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "Error Details:\n";
            print_r($e->errorInfo);
            echo "</pre>";
            echo "</div>";
            throw new Exception('데이터베이스 연결에 실패했습니다: ' . $e->getMessage());
        }
    }

    private function ensureDatabaseTable()
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>데이터베이스 테이블 확인</h3>";

            // Check if table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'users'");
            if ($tableCheck->rowCount() === 0) {
                echo "<p>ℹ️ users 테이블이 없습니다. 생성합니다...</p>";
                
                // Create users table with all required fields
                $this->db->exec("
                    CREATE TABLE users (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        password VARCHAR(255) NULL,
                        remember_token VARCHAR(100) NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        email_verified_at TIMESTAMP NULL,
                        google_id VARCHAR(255) NULL,
                        role VARCHAR(20) DEFAULT 'user',
                        profile_image VARCHAR(255) NULL,
                        phone VARCHAR(20) NULL,
                        address TEXT NULL,
                        last_login_at TIMESTAMP NULL,
                        login_count INT DEFAULT 0,
                        last_activity_at TIMESTAMP NULL,
                        failed_login_attempts INT DEFAULT 0,
                        status VARCHAR(20) DEFAULT 'active',
                        is_deleted TINYINT(1) DEFAULT 0,
                        PRIMARY KEY (id),
                        UNIQUE KEY idx_email (email),
                        UNIQUE KEY idx_google_id (google_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "<p style='color: green;'>✅ users 테이블 생성 완료</p>";
            } else {
                echo "<p>✅ users 테이블이 존재합니다.</p>";
                
                // Check and fix table structure
                $this->checkAndFixTableStructure();
            }

            echo "<p style='color: green;'>✅ 데이터베이스 테이블 검증 완료</p>";
            echo "</div>";
        } catch (PDOException $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 데이터베이스 테이블 검증 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('데이터베이스 테이블 검증에 실패했습니다: ' . $e->getMessage());
        }
    }

    private function checkAndFixTableStructure()
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>테이블 구조 확인 및 수정</h3>";

            // Get current table structure
            $createTable = $this->db->query("SHOW CREATE TABLE users")->fetch(PDO::FETCH_ASSOC);
            if (!$createTable) {
                throw new Exception('테이블 구조를 확인할 수 없습니다.');
            }

            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "Current Table Structure:\n";
            print_r($createTable);
            echo "</pre>";

            // Required columns and their definitions
            $requiredColumns = [
                'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(100) NOT NULL',
                'email' => 'VARCHAR(100) NOT NULL',
                'password' => 'VARCHAR(255) NULL',
                'google_id' => 'VARCHAR(255) NULL',
                'profile_image' => 'VARCHAR(255) NULL',
                'email_verified_at' => 'TIMESTAMP NULL',
                'remember_token' => 'VARCHAR(100) NULL',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ];

            // Get current columns
            $columns = $this->db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
            $currentColumns = array_column($columns, 'Field');

            // Check and add missing columns
            foreach ($requiredColumns as $column => $definition) {
                if (!in_array($column, $currentColumns)) {
                    echo "<p>ℹ️ {$column} 컬럼 추가 중...</p>";
                    $this->db->exec("ALTER TABLE users ADD COLUMN `{$column}` {$definition}");
                    echo "<p>✅ {$column} 컬럼 추가 완료</p>";
                }
            }

            // Check and modify column definitions
            foreach ($columns as $column) {
                $columnName = $column['Field'];
                if (isset($requiredColumns[$columnName])) {
                    $requiredDef = $requiredColumns[$columnName];
                    $currentDef = $column['Type'] . 
                                 ($column['Null'] === 'NO' ? ' NOT NULL' : ' NULL') .
                                 ($column['Default'] !== null ? " DEFAULT {$column['Default']}" : '') .
                                 ($column['Extra'] ? " {$column['Extra']}" : '');

                    if ($currentDef !== $requiredDef) {
                        echo "<p>ℹ️ {$columnName} 컬럼 수정 중...</p>";
                        $this->db->exec("ALTER TABLE users MODIFY COLUMN `{$columnName}` {$requiredDef}");
                        echo "<p>✅ {$columnName} 컬럼 수정 완료</p>";
                    }
                }
            }

            // Check and add required indexes
            $indexes = $this->db->query("SHOW INDEX FROM users")->fetchAll(PDO::FETCH_ASSOC);
            $currentIndexes = array_column($indexes, 'Key_name');

            // Required indexes
            $requiredIndexes = [
                'email' => 'UNIQUE INDEX `idx_email` (`email`)',
                'google_id' => 'UNIQUE INDEX `idx_google_id` (`google_id`)'
            ];

            foreach ($requiredIndexes as $index => $definition) {
                $indexName = 'idx_' . $index;
                if (!in_array($indexName, $currentIndexes)) {
                    echo "<p>ℹ️ {$index} 인덱스 추가 중...</p>";
                    $this->db->exec("ALTER TABLE users ADD {$definition}");
                    echo "<p>✅ {$index} 인덱스 추가 완료</p>";
                }
            }

            // Verify primary key
            $hasPrimaryKey = false;
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $hasPrimaryKey = true;
                    break;
                }
            }

            if (!$hasPrimaryKey) {
                echo "<p>ℹ️ 기본 키 추가 중...</p>";
                $this->db->exec("ALTER TABLE users ADD PRIMARY KEY (`id`)");
                echo "<p>✅ 기본 키 추가 완료</p>";
            }

            echo "<p style='color: green;'>✅ 테이블 구조 확인 및 수정 완료</p>";
            echo "</div>";
        } catch (PDOException $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 테이블 구조 수정 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('테이블 구조 수정에 실패했습니다: ' . $e->getMessage());
        }
    }

    public function getAuthUrl()
    {
        try {
            // Generate and store state token
            $state = bin2hex(random_bytes(32));
            $_SESSION['google_oauth_state'] = $state;

            // Generate and store CSRF token if not exists
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            $params = [
                'client_id' => $this->config['google']['client_id'],
                'redirect_uri' => $this->config['google']['redirect_uri'],
                'response_type' => 'code',
                'scope' => 'email profile',
                'state' => $state,
                'access_type' => 'offline',
                'prompt' => 'consent'
            ];

            $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
            echo "<p>✅ Google 인증 URL 생성 완료</p>";
            return $authUrl;
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ 인증 URL 생성 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            throw new Exception('인증 URL을 생성할 수 없습니다.');
        }
    }

    public function handleCallback($code, $state)
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>Google 로그인 처리</h3>";

            // 상태 검증
            if (!isset($_SESSION['google_oauth_state']) || $_SESSION['google_oauth_state'] !== $state) {
                echo "<p style='color: red;'>❌ 상태 검증 실패</p>";
                throw new Exception('잘못된 요청입니다. 상태 값이 일치하지 않습니다.');
            }
            echo "<p>✅ 상태 검증 완료</p>";

            // CSRF 토큰 검증 (GET 파라미터로 전달된 경우)
            if (isset($_GET['csrf_token'])) {
                if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_GET['csrf_token']) {
                    echo "<p style='color: red;'>❌ CSRF 토큰 검증 실패</p>";
                    throw new Exception('Invalid CSRF token');
                }
                echo "<p>✅ CSRF 토큰 검증 완료</p>";
            }

            // 액세스 토큰 획득
            try {
                $tokenData = $this->getAccessToken($code);
                if (!isset($tokenData['access_token'])) {
                    echo "<p style='color: red;'>❌ 액세스 토큰 획득 실패</p>";
                    throw new Exception('액세스 토큰을 받지 못했습니다.');
                }
                echo "<p>✅ 액세스 토큰 획득 완료</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 액세스 토큰 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
                throw new Exception('액세스 토큰 처리 중 오류가 발생했습니다: ' . $e->getMessage());
            }

            // 사용자 정보 조회
            try {
                $userInfo = $this->getUserInfo($tokenData['access_token']);
                if (!isset($userInfo['email'])) {
                    echo "<p style='color: red;'>❌ 사용자 정보 조회 실패</p>";
                    throw new Exception('사용자 정보를 가져올 수 없습니다.');
                }
                echo "<p>✅ 사용자 정보 조회 완료</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 사용자 정보 조회 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
                throw new Exception('사용자 정보 조회 중 오류가 발생했습니다: ' . $e->getMessage());
            }

            // Debug: Print user info
            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "User Info from Google:\n";
            print_r($userInfo);
            echo "</pre>";

            // 사용자 생성 또는 조회
            try {
                $user = $this->findOrCreateUser($userInfo);
                if (!$user || !isset($user['id'])) {
                    echo "<p style='color: red;'>❌ 사용자 계정 처리 실패</p>";
                    throw new Exception('사용자 계정을 처리할 수 없습니다.');
                }
                echo "<p>✅ 사용자 처리 완료</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 사용자 계정 처리 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
                throw new Exception('사용자 계정 처리 중 오류가 발생했습니다: ' . $e->getMessage());
            }

            // 세션 생성
            try {
                $this->createSession($user);
                echo "<p>✅ 세션 생성 완료</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 세션 생성 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
                throw new Exception('세션 생성 중 오류가 발생했습니다: ' . $e->getMessage());
            }

            echo "<p style='color: green;'>✅ 로그인 처리 완료</p>";
            echo "</div>";

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (Exception $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 오류 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
            
            // Log detailed error information
            error_log("Google Auth Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            if (isset($userInfo)) {
                error_log("User Info: " . print_r($userInfo, true));
            }
            
            // Return detailed error information
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'details' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_info' => isset($userInfo) ? $userInfo : null,
                    'step' => isset($step) ? $step : 'unknown'
                ]
            ];
        }
    }

    private function getAccessToken($code)
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>Google 액세스 토큰 획득</h3>";

            $params = [
                'code' => $code,
                'client_id' => $this->config['google']['client_id'],
                'client_secret' => $this->config['google']['client_secret'],
                'redirect_uri' => $this->config['google']['redirect_uri'],
                'grant_type' => 'authorization_code'
            ];

            // Debug: Print request parameters (excluding client_secret)
            $debugParams = $params;
            unset($debugParams['client_secret']);
            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "Request Parameters:\n";
            print_r($debugParams);
            echo "</pre>";

            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new Exception("cURL 오류: " . $curlError);
            }

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = isset($errorData['error_description']) 
                    ? $errorData['error_description'] 
                    : "HTTP 오류 코드: {$httpCode}";
                throw new Exception("액세스 토큰 요청 실패: " . $errorMessage);
            }

            $tokenData = json_decode($response, true);
            if (!$tokenData || !isset($tokenData['access_token'])) {
                throw new Exception('유효하지 않은 토큰 응답입니다.');
            }

            echo "<p style='color: green;'>✅ 액세스 토큰 획득 완료</p>";
            echo "</div>";

            return $tokenData;
        } catch (Exception $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 액세스 토큰 획득 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('액세스 토큰을 받을 수 없습니다: ' . $e->getMessage());
        }
    }

    private function getUserInfo($accessToken)
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>Google 사용자 정보 조회</h3>";

            $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new Exception("cURL 오류: " . $curlError);
            }

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = isset($errorData['error']['message']) 
                    ? $errorData['error']['message'] 
                    : "HTTP 오류 코드: {$httpCode}";
                throw new Exception("사용자 정보 요청 실패: " . $errorMessage);
            }

            $userInfo = json_decode($response, true);
            if (!$userInfo) {
                throw new Exception('응답을 JSON으로 파싱할 수 없습니다.');
            }

            if (!isset($userInfo['email'])) {
                throw new Exception('이메일 정보가 없습니다.');
            }

            if (!isset($userInfo['id'])) {
                throw new Exception('Google ID가 없습니다.');
            }

            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "User Info:\n";
            print_r($userInfo);
            echo "</pre>";

            echo "<p style='color: green;'>✅ 사용자 정보 조회 완료</p>";
            echo "</div>";

            return $userInfo;
        } catch (Exception $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 사용자 정보 조회 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('사용자 정보를 가져올 수 없습니다: ' . $e->getMessage());
        }
    }

    private function findOrCreateUser($userInfo)
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>사용자 계정 처리</h3>";

            // Verify database connection
            try {
                $this->db->query("SELECT 1");
            } catch (PDOException $e) {
                throw new Exception('데이터베이스 연결이 끊어졌습니다. 다시 시도해주세요.');
            }

            // Debug: Print raw user info
            echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "Raw User Info:\n";
            print_r($userInfo);
            echo "</pre>";

            // 입력 데이터 검증
            if (!isset($userInfo['email']) || !filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
                echo "<p style='color: red;'>❌ 유효하지 않은 이메일 주소입니다.</p>";
                throw new Exception('유효하지 않은 이메일 주소입니다.');
            }

            if (!isset($userInfo['id'])) {
                echo "<p style='color: red;'>❌ Google ID가 없습니다.</p>";
                throw new Exception('Google ID가 없습니다.');
            }

            $email = filter_var($userInfo['email'], FILTER_SANITIZE_EMAIL);
            $name = isset($userInfo['name']) ? htmlspecialchars($userInfo['name']) : explode('@', $email)[0];
            $googleId = $userInfo['id'];
            $profileImage = $userInfo['picture'] ?? null;

            echo "<p>✅ 입력 데이터 검증 완료</p>";
            echo "<ul style='list-style: none; padding: 0;'>";
            echo "<li>📧 이메일: " . htmlspecialchars($email) . "</li>";
            echo "<li>👤 이름: " . htmlspecialchars($name) . "</li>";
            echo "<li>🆔 Google ID: " . htmlspecialchars($googleId) . "</li>";
            echo "</ul>";

            // 트랜잭션 시작
            $this->db->beginTransaction();

            try {
                // Check if table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'users'");
                if ($tableCheck->rowCount() === 0) {
                    throw new Exception('users 테이블이 존재하지 않습니다.');
                }

                // 기존 사용자 확인 (이메일 또는 Google ID로)
                $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email OR google_id = :google_id');
                $stmt->execute(['email' => $email, 'google_id' => $googleId]);
                $existingUser = $stmt->fetch();

                if ($existingUser) {
                    echo "<p>ℹ️ 기존 사용자 발견</p>";
                    
                    // Google ID가 없는 경우 (일반 이메일 가입 사용자)
                    if (empty($existingUser['google_id'])) {
                        echo "<p>ℹ️ 일반 이메일 가입 사용자를 Google 계정으로 연결합니다.</p>";
                        
                        // Update user with Google information
                        $stmt = $this->db->prepare('
                            UPDATE users 
                            SET google_id = :google_id,
                                profile_image = :profile_image,
                                email_verified_at = CURRENT_TIMESTAMP,
                                last_login_at = CURRENT_TIMESTAMP,
                                login_count = login_count + 1,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id
                        ');

                        $result = $stmt->execute([
                            'google_id' => $googleId,
                            'profile_image' => $profileImage,
                            'id' => $existingUser['id']
                        ]);

                        if (!$result) {
                            $error = $stmt->errorInfo();
                            throw new Exception('사용자 정보 업데이트 실패: ' . $error[2]);
                        }

                        // Get updated user info
                        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
                        $stmt->execute(['id' => $existingUser['id']]);
                        $existingUser = $stmt->fetch();
                    } else {
                        // Update existing Google user
                        $stmt = $this->db->prepare('
                            UPDATE users 
                            SET name = :name,
                                profile_image = :profile_image,
                                last_login_at = CURRENT_TIMESTAMP,
                                login_count = login_count + 1,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id
                        ');

                        $result = $stmt->execute([
                            'name' => $name,
                            'profile_image' => $profileImage,
                            'id' => $existingUser['id']
                        ]);

                        if (!$result) {
                            $error = $stmt->errorInfo();
                            throw new Exception('사용자 정보 업데이트 실패: ' . $error[2]);
                        }
                    }

                    $this->db->commit();
                    echo "<p>✅ 기존 사용자 정보 업데이트 완료</p>";
                    echo "</div>";
                    return $existingUser;
                }

                // 새 사용자 생성
                echo "<p>ℹ️ 신규 사용자 생성 시작</p>";
                
                // Check for duplicate email
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
                $stmt->execute(['email' => $email]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('이미 사용 중인 이메일입니다.');
                }

                // Prepare insert statement
                $sql = 'INSERT INTO users (
                            name, email, google_id, profile_image, 
                            email_verified_at, last_login_at, login_count,
                            role, status, created_at, updated_at
                        ) VALUES (
                            :name, :email, :google_id, :profile_image,
                            CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1,
                            :role, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                        )';
                
                $params = [
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'profile_image' => $profileImage,
                    'role' => 'user',
                    'status' => 'active'
                ];
                
                echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
                echo "SQL Query:\n" . $sql . "\n\nParameters:\n";
                print_r($params);
                echo "</pre>";

                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute($params);

                if (!$result) {
                    $error = $stmt->errorInfo();
                    throw new Exception('사용자 생성 실패: ' . $error[2]);
                }

                $userId = $this->db->lastInsertId();
                
                if (!$userId) {
                    $this->db->rollBack();
                    throw new Exception('생성된 사용자 ID를 가져올 수 없습니다.');
                }

                // Verify the user was created
                $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
                $stmt->execute(['id' => $userId]);
                $newUser = $stmt->fetch();

                if (!$newUser) {
                    $this->db->rollBack();
                    throw new Exception('생성된 사용자 정보를 조회할 수 없습니다.');
                }

                $this->db->commit();
                echo "<p>✅ 신규 사용자 생성 완료</p>";
                echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
                echo "Created User:\n";
                print_r($newUser);
                echo "</pre>";
                echo "</div>";
                return $newUser;

            } catch (PDOException $e) {
                $this->db->rollBack();
                echo "<p style='color: red;'>❌ 데이터베이스 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<pre style='background: #fff; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
                echo "Error Details:\n";
                print_r($e->errorInfo);
                echo "</pre>";
                throw new Exception('사용자 계정 처리 중 오류가 발생했습니다: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 오류 발생: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw $e;
        }
    }

    private function createSession($user)
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>세션 생성</h3>";

            if (!isset($user['id']) || !isset($user['email'])) {
                throw new Exception('유효하지 않은 사용자 정보입니다.');
            }

            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                if (!session_start()) {
                    throw new Exception('세션을 시작할 수 없습니다.');
                }
            }

            // Regenerate session ID for security
            if (!session_regenerate_id(true)) {
                throw new Exception('세션 ID를 재생성할 수 없습니다.');
            }

            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'] ?? '';
            $_SESSION['profile_image'] = $user['profile_image'] ?? '';
            $_SESSION['auth_provider'] = 'google';
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Verify session was created
            if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $user['id']) {
                throw new Exception('세션 데이터가 올바르게 설정되지 않았습니다.');
            }

            // Set session cookie parameters
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $httponly = true;
            $samesite = 'Lax';

            session_set_cookie_params([
                'lifetime' => 86400, // 24 hours
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);

            echo "<p style='color: green;'>✅ 세션 생성 완료</p>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 세션 생성 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('세션 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
            echo "<h3>로그아웃 처리</h3>";

            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Clear all session data
            $_SESSION = [];

            // Destroy the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            // Destroy the session
            if (!session_destroy()) {
                throw new Exception('세션을 종료할 수 없습니다.');
            }

            echo "<p style='color: green;'>✅ 로그아웃 완료</p>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div style='background: #fff0f0; padding: 10px; margin: 10px; border: 1px solid #ffcdd2;'>";
            echo "<p style='color: red;'>❌ 로그아웃 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            throw new Exception('로그아웃 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}

/**
 * 구글 로그인 버튼 렌더링
 */
function renderGoogleLoginButton()
{
    try {
        $auth = GoogleAuth::getInstance();
        $authUrl = $auth->getAuthUrl();

        // Generate CSRF token if not exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Add CSRF token to the auth URL
        $authUrl .= (strpos($authUrl, '?') === false ? '?' : '&') . 'csrf_token=' . $_SESSION['csrf_token'];

        $html = <<<HTML
<div class="google-login-container">
    <a href="{$authUrl}" 
       class="google-login-button" 
       role="button"
       aria-label="Google 계정으로 로그인">
        <img src="/assets/images/google-logo.svg" 
             alt="Google 로고" 
             width="20" 
             height="20">
        <span>Google 계정으로 계속하기</span>
    </a>
    <style>
        .google-login-container {
            margin: 1rem auto;
            max-width: 300px;
        }
        .google-login-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background-color: #fff;
            border: 1px solid #dadce0;
            border-radius: 4px;
            color: #3c4043;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.2s;
            width: 100%;
            cursor: pointer;
        }
        .google-login-button:hover {
            background-color: #f8f9fa;
        }
    </style>
</div>
HTML;
        return $html;
    } catch (Exception $e) {
        return "<p style='color: red;'>로그인 버튼 생성 중 오류가 발생했습니다.</p>";
    }
}

/**
 * 구글 콜백 처리
 */
function handleGoogleCallback()
{
    if (!isset($_GET['code']) || !isset($_GET['state'])) {
        $_SESSION['error'] = '잘못된 요청입니다. 필수 파라미터가 누락되었습니다.';
        header('Location: /login');
        exit;
    }

    try {
        $auth = GoogleAuth::getInstance();
        $result = $auth->handleCallback($_GET['code'], $_GET['state']);

        if ($result['success']) {
            header('Location: /dashboard');
        } else {
            $_SESSION['error'] = $result['error'];
            if (isset($result['details'])) {
                $_SESSION['error_details'] = $result['details'];
            }
            header('Location: /login');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = '로그인 처리 중 오류가 발생했습니다: ' . $e->getMessage();
        $trace = $e->getTraceAsString();
        if ($trace !== '') {
            $_SESSION['error_details'] = [
                'message' => $e->getMessage(),
                'trace' => $trace
            ];
        }
        header('Location: /login');
    }
    exit;
}