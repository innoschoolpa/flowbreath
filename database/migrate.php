<?php

require_once __DIR__ . '/../src/config/database.php';

use Config\Database;

try {
    // 데이터베이스 연결
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 마이그레이션 파일 목록 가져오기
    $migrations = glob(__DIR__ . '/migrations/*.php');
    sort($migrations); // 파일명 순서대로 실행

    foreach ($migrations as $migration) {
        require_once $migration;
        
        // 클래스 이름 추출 (파일명에서 날짜와 언더스코어 제거)
        $className = basename($migration, '.php');
        $className = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $className);
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));
        
        if (class_exists($className)) {
            echo "Running migration: $className\n";
            $instance = new $className();
            $instance->up($pdo);
        } else {
            echo "Warning: Class $className not found in $migration\n";
        }
    }

    echo "All migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
} 