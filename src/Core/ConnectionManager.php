<?php
namespace App\Core;

class ConnectionManager {
    private static $lastResetTime = null;
    private static $resetInterval = 3600; // 1시간

    public static function checkAndResetConnections() {
        $currentTime = time();
        
        if (self::$lastResetTime === null) {
            self::$lastResetTime = $currentTime;
            return;
        }

        if ($currentTime - self::$lastResetTime >= self::$resetInterval) {
            $db = Database::getInstance();
            $db->resetConnectionCount();
            self::$lastResetTime = $currentTime;
        }
    }
} 