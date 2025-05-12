<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Config;

try {
    // 데이터베이스 연결
    $db = Database::getInstance();
    echo "✅ 데이터베이스 연결 성공\n\n";

    // 테이블 목록 조회
    echo "📋 테이블 목록:\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- {$table}\n";
    }
    echo "\n";

    // users 테이블 구조 확인
    echo "👥 users 테이블 구조:\n";
    $columns = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']} ({$column['Null']})\n";
    }
    echo "\n";

    // users 테이블 데이터 확인
    echo "👤 사용자 목록:\n";
    $users = $db->query("SELECT id, name, email, google_id, role, status, created_at, last_login_at FROM users")->fetchAll(PDO::FETCH_ASSOC);
    if (count($users) > 0) {
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "이름: {$user['name']}\n";
            echo "이메일: {$user['email']}\n";
            echo "Google ID: " . ($user['google_id'] ?? '없음') . "\n";
            echo "역할: {$user['role']}\n";
            echo "상태: {$user['status']}\n";
            echo "가입일: {$user['created_at']}\n";
            echo "마지막 로그인: " . ($user['last_login_at'] ?? '없음') . "\n";
            echo "-------------------\n";
        }
    } else {
        echo "등록된 사용자가 없습니다.\n";
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
} 