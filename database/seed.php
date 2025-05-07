<?php
// 오류 표시 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 필요한 파일 로드
require_once __DIR__ . '/../src/config/database.php';

try {
    // 데이터베이스 연결
    $pdo = \Config\Database::getInstance()->getConnection();
    echo "데이터베이스 연결 성공\n";

    // SQL 스크립트 파일 읽기
    $sql = file_get_contents(__DIR__ . '/../database/seed.sql');
    if ($sql === false) {
        throw new Exception("seed.sql 파일을 읽을 수 없습니다.");
    }

    // SQL 스크립트 실행
    echo "테스트 데이터 추가 시작...\n";
    
    // 각 SQL 문장을 개별적으로 실행
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt); }
    );

    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            echo "실행 완료: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "오류 발생: " . $e->getMessage() . "\n";
            echo "실패한 SQL: " . $statement . "\n";
        }
    }

    echo "테스트 데이터 추가 완료!\n";

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
    exit(1);
} 