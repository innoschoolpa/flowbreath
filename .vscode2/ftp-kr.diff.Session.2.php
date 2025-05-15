<?php

namespace App\Core;

class Session
{
    private static $instance = null;
    private $data = [];
    private $flash = [];
    private $config = [];
    private $encryptionKey;
    private $lastActivity;
    private $sessionPrefix = 'sess_';

    public function __construct()
    {
        $this->loadConfig();
        $this->initializeEncryption();
        $this->initializeSession();
    }

    private function initializeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->configureSession();
            session_start();
        }
        
        $this->data = &$_SESSION;
        $this->lastActivity = time();
        $this->handleFlashMessages();
        $this->checkExpiration();
    }

    private function configureSession()
    {
        $cookieConfig = $this->config['session']['cookie'] ?? [];
        
        // 세션 기본 설정
        $settings = [
            'session.cookie_httponly' => 1,
            'session.use_only_cookies' => 1,
            'session.cookie_secure' => isset($_SERVER['HTTPS']),
            'session.cookie_samesite' => $cookieConfig['samesite'] ?? 'Strict',
            'session.gc_maxlifetime' => $this->config['session']['lifetime'] ?? 7200,
            'session.gc_probability' => $this->config['session']['gc_probability'] ?? 1,
            'session.gc_divisor' => $this->config['session']['gc_divisor'] ?? 100
        ];

        // 설정 적용
        foreach ($settings as $key => $value) {
            ini_set($key, $value);
        }

        // 세션 저장 경로 설정
        if (isset($this->config['session']['path'])) {
            session_save_path($this->config['session']['path']);
        }

        // 쿠키 파라미터 설정
        session_set_cookie_params(
            $this->config['session']['lifetime'] ?? 7200,
            $cookieConfig['path'] ?? '/',
            $cookieConfig['domain'] ?? '',
            $cookieConfig['secure'] ?? true,
            $cookieConfig['httponly'] ?? true
        );
    }

    private function handleFlashMessages()
    {
        $this->flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
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
        $this->config = $app->getConfig('app');
    }

    private function initializeEncryption()
    {
        $this->encryptionKey = $this->config['session']['encryption_key'] ?? bin2hex(random_bytes(32));
    }

    private function checkExpiration()
    {
        if (isset($this->data['_last_activity'])) {
            $inactive = time() - $this->data['_last_activity'];
            if ($inactive >= ($this->config['session']['lifetime'] ?? 7200)) {
                $this->clear();
                $this->regenerate();
            }
        }
        $this->data['_last_activity'] = time();
    }

    private function encrypt($data)
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            serialize($data),
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    private function decrypt($data)
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return unserialize(openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        ));
    }

    public function set($key, $value)
    {
        // Don't encrypt state parameters
        if ($key === 'google_oauth_state') {
            $this->data[$key] = $value;
            return;
        }
        
        // Encrypt other sensitive data
        $this->data[$key] = $this->encrypt($value);
    }

    public function get($key, $default = null)
    {
        if (!isset($this->data[$key])) {
            return $default;
        }
        
        // Don't decrypt state parameters
        if ($key === 'google_oauth_state') {
            return $this->data[$key];
        }
        
        // Decrypt other sensitive data
        return $this->decrypt($this->data[$key]);
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function clear()
    {
        $this->data = [];
        session_destroy();
    }

    public function all()
    {
        return $this->data;
    }

    public function regenerate($deleteOldSession = true)
    {
        if ($deleteOldSession) {
            $oldSessionId = session_id();
            session_regenerate_id(true);
            $this->cleanupOldSession($oldSessionId);
        } else {
            session_regenerate_id(false);
        }
        $this->data['_last_activity'] = time();
    }

    private function cleanupOldSession($oldSessionId)
    {
        $sessionPath = session_save_path();
        $oldSessionFile = $sessionPath . '/' . $this->sessionPrefix . $oldSessionId;
        if (file_exists($oldSessionFile)) {
            unlink($oldSessionFile);
        }
    }

    public function flash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash($key, $default = null)
    {
        return $this->flash[$key] ?? $default;
    }

    public function hasFlash($key)
    {
        return isset($this->flash[$key]);
    }

    public function id()
    {
        return session_id();
    }

    public function name()
    {
        return session_name();
    }

    public function status()
    {
        return session_status();
    }

    public function isStarted()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function __destruct()
    {
        if ($this->isStarted()) {
            session_write_close();
        }
    }
} 