<?php

namespace App\Core;

class Config
{
    private static $config = [];

    public static function load()
    {
        // Load .env file
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$config[$key] = $value;
                }
            }
        }

        // Set default configurations
        self::$config['google'] = [
            'client_id' => self::$config['GOOGLE_CLIENT_ID'] ?? '',
            'client_secret' => self::$config['GOOGLE_CLIENT_SECRET'] ?? '',
            'redirect_uri' => self::$config['GOOGLE_REDIRECT_URI'] ?? ''
        ];
    }

    public static function get($key, $default = null)
    {
        if (empty(self::$config)) {
            self::load();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function set($key, $value)
    {
        if (empty(self::$config)) {
            self::load();
        }

        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }
} 