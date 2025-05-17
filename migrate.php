<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/Database.php';

use App\Core\Database;

// 마이그레이션 파일 로드
$migrationFile = __DIR__ . '/migrations/20240318_fix_resource_id.php';
require_once $migrationFile;

try {
    // 마이그레이션 실행
    $migration = new FixResourceId();
    $migration->up();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} 