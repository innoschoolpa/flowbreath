<?php

namespace App\Core;

class Auth
{
    private static $instance = null;
    private $session;
    private $user = null;
    private $db;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db = Database::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function login($user)
    {
        // 이름 유효성 검사 및 기본값 처리
        if (!isset($user['name']) || !preg_match('/^[a-zA-Z가-힣0-9 _-]{2,50}$/u', $user['name'])) {
            $user['name'] = '사용자';
        }
        $this->user = $user;
        // user_id를 암호화/인코딩 없이 실제 int로 저장
        $this->session->set('user_id', (int)$user['id']);
        $this->session->set('user_name', $user['name']);
        $this->session->regenerate();
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user_id');
        $this->session->regenerate();
    }

    public function check()
    {
        return $this->session->has('user_id');
    }

    public function user()
    {
        try {
            if (!$this->check()) {
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] No user session found' . PHP_EOL, FILE_APPEND);
                return null;
            }
            if ($this->user !== null) {
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] Cached user: ' . print_r($this->user, true) . PHP_EOL, FILE_APPEND);
                return $this->user;
            }
            $userId = $this->session->get('user_id');
            file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] user_id from session: ' . $userId . PHP_EOL, FILE_APPEND);

            if (!$userId) {
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] No user ID in session' . PHP_EOL, FILE_APPEND);
                return null;
            }

            try {
                if (!$this->db) {
                    file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] DB connection is null' . PHP_EOL, FILE_APPEND);
                    $this->db = Database::getInstance();
                }
                $this->db->getConnection()->query("SELECT 1");
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] DB connection OK' . PHP_EOL, FILE_APPEND);

                $sql = "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL";
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] SQL: ' . $sql . ' with ID: ' . $userId . PHP_EOL, FILE_APPEND);

                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] Failed to prepare statement' . PHP_EOL, FILE_APPEND);
                    throw new \PDOException("Failed to prepare statement");
                }
                $stmt->execute([$userId]);
                $this->user = $stmt->fetch(\PDO::FETCH_ASSOC);

                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] Query result: ' . print_r($this->user, true) . PHP_EOL, FILE_APPEND);

                if (!$this->user) {
                    file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] User not found with ID: ' . $userId . PHP_EOL, FILE_APPEND);
                    $this->logout();
                    return null;
                }
                if (!isset($this->user['status']) || $this->user['status'] !== 'active') {
                    file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] User is not active. Status: ' . ($this->user['status'] ?? 'not set') . PHP_EOL, FILE_APPEND);
                    $this->logout();
                    return null;
                }
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] Successfully retrieved active user' . PHP_EOL, FILE_APPEND);
                return $this->user;
            } catch (\PDOException $e) {
                file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] DB error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
                return null;
            }
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../logs/error.log', '[Auth::user()] Unexpected error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            return null;
        }
    }

    public function id()
    {
        return $this->session->get('user_id');
    }

    public function isAdmin()
    {
        $user = $this->user();
        return $user && isset($user['role']) && $user['role'] === 'admin';
    }

    public function attempt($email, $password)
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $this->login($user);
            return true;
        }

        return false;
    }

    public function register($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->query(
            "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())",
            [$data['name'], $data['email'], $data['password']]
        );

        if ($stmt->rowCount() > 0) {
            $userId = $this->db->lastInsertId();
            $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
            $user = $stmt->fetch();
            $this->login($user);
            return true;
        }

        return false;
    }

    public function update($data)
    {
        if (!$this->check()) {
            return false;
        }

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $userId = $this->id();

        try {
            $fields = array_map(function($field) {
                return "$field = ?";
            }, array_keys($data));

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $params = array_merge(array_values($data), [$userId]);
            
            $stmt = $this->db->prepare($sql);
            $updated = $stmt->execute($params);

            if ($updated) {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $this->user = $stmt->fetch(\PDO::FETCH_ASSOC);
                return true;
            }
        } catch (\PDOException $e) {
            error_log("Database error in Auth::update(): " . $e->getMessage());
        }

        return false;
    }

    public function resetPassword($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->db->prepare("
                INSERT INTO password_resets (email, token, created_at, expires_at)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $email,
                $token,
                date('Y-m-d H:i:s'),
                $expires
            ]);

            return $token;
        } catch (\PDOException $e) {
            error_log("Database error in Auth::resetPassword(): " . $e->getMessage());
            return false;
        }
    }

    public function validateResetToken($token)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM password_resets 
                WHERE token = ? AND expires_at > ?
            ");
            $stmt->execute([$token, date('Y-m-d H:i:s')]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
        } catch (\PDOException $e) {
            error_log("Database error in Auth::validateResetToken(): " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($token, $password)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM password_resets 
                WHERE token = ? AND expires_at > ?
            ");
            $stmt->execute([$token, date('Y-m-d H:i:s')]);
            $reset = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$reset) {
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = ?, updated_at = ? 
                WHERE email = ?
            ");
            $updated = $stmt->execute([
                password_hash($password, PASSWORD_DEFAULT),
                date('Y-m-d H:i:s'),
                $reset['email']
            ]);

            if ($updated) {
                $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                return true;
            }
        } catch (\PDOException $e) {
            error_log("Database error in Auth::updatePassword(): " . $e->getMessage());
        }

        return false;
    }

    public function socialLogin($provider, $socialUser)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$socialUser->email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (
                        email, name, password, provider, provider_id, 
                        created_at, updated_at, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([
                    $socialUser->email,
                    $socialUser->name,
                    password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    $provider,
                    $socialUser->id,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s')
                ]);

                $userId = $this->db->lastInsertId();
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            if ($user) {
                $this->login($user);
                return true;
            }
        } catch (\PDOException $e) {
            error_log("Database error in Auth::socialLogin(): " . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
} 