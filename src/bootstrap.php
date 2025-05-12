<?php
// src/bootstrap.php

// Load environment variables
$envFile = dirname(__DIR__) . '/.env';
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
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Set error reporting based on environment
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('Asia/Seoul');

// Set session configuration
ini_set('session.save_handler', 'files');
ini_set('session.save_path', __DIR__ . '/../storage/sessions');
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

// Create session directory if it doesn't exist
$sessionPath = __DIR__ . '/../storage/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load Parsedown
$parsedown = new Parsedown();
echo "Parsedown 클래스가 정상적으로 로드되었습니다.\n";

try {
    $parsedown = new Parsedown();
    echo "Parsedown 인스턴스 생성 성공\n";
} catch (Exception $e) {
    echo "Parsedown 인스턴스 생성 실패: " . $e->getMessage() . "\n";
}

// Database configuration
try {
    $db = [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];
    echo "Database configuration loaded successfully\n";
} catch (Exception $e) {
    echo "Database configuration error: " . $e->getMessage() . "\n";
    exit(1);
}

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}",
        $db['username'],
        $db['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Store connection count in a file
    $countFile = __DIR__ . '/../storage/connection_count.txt';
    $count = file_exists($countFile) ? (int)file_get_contents($countFile) : 0;
    $count++;
    file_put_contents($countFile, $count);
    echo "New database connection established. Total connections this hour: $count\n";
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit(1);
} 