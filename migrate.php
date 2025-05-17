<?php

require_once __DIR__ . '/vendor/autoload.php';

// 마이그레이션 디렉토리
$migrationsDir = __DIR__ . '/migrations';

// 마이그레이션 파일 목록 가져오기
$migrationFiles = glob($migrationsDir . '/*.php');

// 마이그레이션 실행
foreach ($migrationFiles as $file) {
    require_once $file;
    $className = 'UpdateResourceVisibility'; // 클래스 이름을 직접 지정
    $migration = new $className();
    
    echo "Running migration: $className\n";
    $migration->up();
    echo "Migration completed: $className\n";
}

echo "All migrations completed successfully.\n"; 