<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('ROOT_PATH', dirname(__DIR__));

try {
    // 데이터베이스 연결
    $db = \Config\Database::getInstance()->getConnection();

    // 시드 매니저 생성
    $seedManager = new \App\Database\SeedManager($db);

    // 시드 실행
    $seeder = isset($argv[1]) ? $argv[1] : null;
    $seedManager->seed($seeder);
} catch (\Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
    exit(1);
} 