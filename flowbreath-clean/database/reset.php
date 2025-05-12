<?php
// 오류 표시 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 필요한 파일 로드
require_once __DIR__ . '/../src/config/database.php';

function executeSqlFile($pdo, $filePath) {
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        throw new Exception("SQL 파일을 읽을 수 없습니다: " . $filePath);
    }

    // SQL 문장을 개별적으로 분리
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt); }
    );

    // 각 SQL 문장 실행
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            echo "실행 완료: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            echo "SQL 실행 오류: " . $e->getMessage() . "\n";
            echo "실패한 SQL: " . $statement . "\n";
            throw $e;
        }
    }
}

try {
    // 데이터베이스 연결
    $pdo = \Config\Database::getInstance()->getConnection();
    echo "데이터베이스 연결 성공\n";

    // 1. 기존 테이블 삭제
    echo "기존 테이블 삭제 중...\n";
    executeSqlFile($pdo, __DIR__ . '/../database/reset.sql');
    echo "기존 테이블 삭제 완료\n";

    // 2. 테이블 재생성
    echo "테이블 재생성 중...\n";
    executeSqlFile($pdo, __DIR__ . '/../database/schema.sql');
    echo "테이블 재생성 완료\n";

    // 3. 테스트 데이터 추가
    echo "테스트 데이터 추가 중...\n";
    executeSqlFile($pdo, __DIR__ . '/../database/seed.sql');
    echo "테스트 데이터 추가 완료\n";

    echo "모든 작업이 성공적으로 완료되었습니다!\n";

} catch (Exception $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
    exit(1);
} 