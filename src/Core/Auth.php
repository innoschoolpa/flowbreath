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
        $this->user = $user;
        $this->session->set('user_id', $user['id']);
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
        if ($this->user === null && $this->check()) {
            $userId = $this->session->get('user_id');
            $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
            $this->user = $stmt->fetch();
        }
        return $this->user;
    }

    public function id()
    {
        return $this->session->get('user_id');
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

        $updated = $this->db->table('users')
            ->where('id', $this->id())
            ->update($data);

        if ($updated) {
            $this->user = $this->db->table('users')
                ->where('id', $this->id())
                ->first();
            return true;
        }

        return false;
    }

    public function resetPassword($email)
    {
        $user = $this->db->table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->db->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expires
        ]);

        // TODO: 이메일 발송 로직 구현
        return $token;
    }

    public function validateResetToken($token)
    {
        $reset = $this->db->table('password_resets')
            ->where('token', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        return $reset !== null;
    }

    public function updatePassword($token, $password)
    {
        $reset = $this->db->table('password_resets')
            ->where('token', $token)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return false;
        }

        $updated = $this->db->table('users')
            ->where('email', $reset->email)
            ->update([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if ($updated) {
            $this->db->table('password_resets')
                ->where('token', $token)
                ->delete();
            return true;
        }

        return false;
    }

    public function socialLogin($provider, $socialUser)
    {
        $user = $this->db->table('users')
            ->where('email', $socialUser->email)
            ->first();

        if (!$user) {
            // 소셜 로그인 사용자 등록
            $userId = $this->db->table('users')->insert([
                'email' => $socialUser->email,
                'name' => $socialUser->name,
                'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'provider' => $provider,
                'provider_id' => $socialUser->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $user = $this->db->table('users')
                ->where('id', $userId)
                ->first();
        }

        $this->login($user);
        return true;
    }
} 