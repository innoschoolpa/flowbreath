<?php

namespace App\Auth;

use PDO;
use PDOException;

class LoginManager {
    private $db;
    private $config;
    private static $instance = null;
    
    private function __construct() {
        global $config;
        $this->config = $config;
        $this->db = getDbConnection();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function attemptLogin($email, $password) {
        try {
            // 로그인 시도 횟수 확인
            if ($this->isLoginBlocked($email)) {
                throw new \Exception('로그인이 일시적으로 차단되었습니다. 잠시 후 다시 시도해주세요.');
            }
            
            // 사용자 조회
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->incrementLoginAttempts($email);
                throw new \Exception('이메일 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 비밀번호 검증
            if (!password_verify($password, $user['password'])) {
                $this->incrementLoginAttempts($email);
                throw new \Exception('이메일 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 로그인 성공 시 시도 횟수 초기화
            $this->resetLoginAttempts($email);
            
            // 2FA 확인
            if ($user['two_factor_enabled']) {
                return [
                    'success' => true,
                    'requires_2fa' => true,
                    'user_id' => $user['id']
                ];
            }
            
            // 세션 생성
            $this->createSession($user);
            
            return [
                'success' => true,
                'requires_2fa' => false,
                'user' => $user
            ];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            throw new \Exception('로그인 처리 중 오류가 발생했습니다.');
        }
    }
    
    private function isLoginBlocked($email) {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
            FROM login_attempts 
            WHERE email = :email AND attempt_time > DATE_SUB(NOW(), INTERVAL :lockout_time SECOND)
        ');
        
        $stmt->execute([
            'email' => $email,
            'lockout_time' => $this->config['login_lockout_time']
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['attempts'] >= $this->config['max_login_attempts'];
    }
    
    private function incrementLoginAttempts($email) {
        $stmt = $this->db->prepare('
            INSERT INTO login_attempts (email, attempt_time) 
            VALUES (:email, NOW())
        ');
        $stmt->execute(['email' => $email]);
    }
    
    private function resetLoginAttempts($email) {
        $stmt = $this->db->prepare('
            DELETE FROM login_attempts 
            WHERE email = :email
        ');
        $stmt->execute(['email' => $email]);
    }
    
    private function createSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['last_activity'] = time();
    }
    
    public function validatePassword($password) {
        if (strlen($password) < $this->config['password_min_length']) {
            return false;
        }
        
        if ($this->config['require_special_chars'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        if ($this->config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        if ($this->config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        return true;
    }
} 