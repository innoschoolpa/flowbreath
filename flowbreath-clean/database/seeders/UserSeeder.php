<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // 기본 사용자 데이터
    $users = [
        [
            'name' => '관리자',
            'email' => 'admin@flowbreath.com',
            'password' => password_hash('admin123!@#', PASSWORD_DEFAULT),
            'email_verified_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => '테스트 사용자',
            'email' => 'user@flowbreath.com',
            'password' => password_hash('user123!@#', PASSWORD_DEFAULT),
            'email_verified_at' => date('Y-m-d H:i:s')
        ]
    ];

    // 사용자 삽입
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, email_verified_at) VALUES (:name, :email, :password, :email_verified_at)");
    
    foreach ($users as $user) {
        $stmt->execute($user);
        echo "사용자 '{$user['email']}' 이(가) 생성되었습니다.\n";
    }

    echo "모든 사용자가 성공적으로 생성되었습니다.\n";

} catch (PDOException $e) {
    die("사용자 생성 실패: " . $e->getMessage() . "\n");
} 