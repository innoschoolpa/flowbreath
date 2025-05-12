<?php

namespace App\Auth;

class Auth {
    private static $instance = null;
    private $user = null;
    private $db;

    private function __construct() {
        global $db;
        $this->db = $db;
        $this->checkSession();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function checkSession() {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND is_active = TRUE");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->user = $user;
            } else {
                $this->logout();
            }
        }
    }

    public function attempt(string $email, string $password): bool {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $this->login($user);
            
            // 마지막 로그인 시간 업데이트
            $stmt = $this->db->prepare("UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            return true;
        }

        return false;
    }

    public function login(array $user) {
        // name 필드 유효성 검사
        if (!isset($user['name']) || !preg_match('/^[a-zA-Z가-힣0-9 _-]{2,50}$/u', $user['name'])) {
            $user['name'] = '사용자';
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $this->user = $user;
    }

    public function logout() {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        $this->user = null;
        session_regenerate_id(true);
    }

    public function check(): bool {
        return $this->user !== null;
    }

    public function user() {
        return $this->user;
    }

    public function id() {
        return $this->user ? $this->user['id'] : null;
    }

    public function isAdmin(): bool {
        return $this->user && $this->user['role'] === 'admin';
    }

    public function hasRole(string $role): bool {
        return $this->user && $this->user['role'] === $role;
    }

    public function findOrCreateGoogleUser(array $googleUser): array {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE google_id = ? OR email = ?
        ");
        $stmt->execute([$googleUser['id'], $googleUser['email']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            // 기존 사용자 업데이트
            if (empty($user['google_id'])) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET google_id = ?, profile_image = ?, is_verified = TRUE 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $googleUser['id'],
                    $googleUser['picture'] ?? null,
                    $user['id']
                ]);
            }
            // name 필드 검증
            if (!isset($user['name']) || !preg_match('/^[a-zA-Z가-힣0-9 _-]{2,50}$/u', $user['name'])) {
                $user['name'] = '사용자';
            }
            return $user;
        }

        // 새 사용자 생성
        $name = isset($googleUser['name']) && preg_match('/^[a-zA-Z가-힣0-9 _-]{2,50}$/u', $googleUser['name']) ? $googleUser['name'] : '사용자';
        $stmt = $this->db->prepare("
            INSERT INTO users (
                email, name, google_id, profile_image, 
                is_active, is_verified, role, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                TRUE, TRUE, 'user', CURRENT_TIMESTAMP
            )
        ");

        $stmt->execute([
            $googleUser['email'],
            $name,
            $googleUser['id'],
            $googleUser['picture'] ?? null
        ]);

        return [
            'id' => $this->db->lastInsertId(),
            'email' => $googleUser['email'],
            'name' => $name,
            'google_id' => $googleUser['id'],
            'profile_image' => $googleUser['picture'] ?? null,
            'is_active' => true,
            'is_verified' => true,
            'role' => 'user'
        ];
    }

    public function loginWithGoogle(array $googleUser) {
        $user = $this->findOrCreateGoogleUser($googleUser);
        $this->login($user);

        // 마지막 로그인 시간 업데이트
        $stmt = $this->db->prepare("
            UPDATE users 
            SET last_login_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
    }
} 