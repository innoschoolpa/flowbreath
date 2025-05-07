<?php
/**
 * config/app.php
 * 애플리케이션 전반에 사용될 설정 값들을 정의합니다.
 */

// 보안을 위해 이 파일이 웹에서 직접 접근되는 것을 방지
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    die('Access denied.');
}

return [
    // 사이트 이름
    'site_name' => 'FlowBreath.io',

    // 기본 URL
    'base_url' => 'https://flowbreath.io',

    // 디버그 모드
    'debug' => true,

    // 기본 시간대 설정
    'default_timezone' => 'Asia/Seoul',

    // Google OAuth 2.0 설정
    'google_client_id' => '1080389288749-v6ioot1blhc8vp95t4et0sbg3ttd01ub.apps.googleusercontent.com',
    'google_client_secret' => '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',
    'google_redirect_uri' => 'https://flowbreath.io/google-callback',

    // 기본 사용자 역할
    'default_user_role' => 'user',

    // 세션 설정
    'session' => [
        'name' => 'flowbreath_session',
        'lifetime' => 7200,
        'path' => '/',
        'domain' => 'flowbreath.io',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    // 데이터베이스 설정
    'database' => [
        'host' => 'localhost',
        'dbname' => 'flowbreath',
        'username' => 'flowbreath',
        'password' => '',
        'charset' => 'utf8mb4'
    ],

    // 로깅 설정
    'logging' => [
        'path' => __DIR__ . '/../logs',
        'level' => 'error'
    ],

    // 업로드 설정
    'upload' => [
        'path' => __DIR__ . '/../public/uploads',
        'max_size' => 10485760,
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf']
    ],

    // 메일 설정
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from' => [
            'address' => 'noreply@flowbreath.io',
            'name' => 'FlowBreath.io'
        ]
    ]
];

?>