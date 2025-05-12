<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    // 1. 외래 키 제약 조건 비활성화
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "외래 키 제약 조건 비활성화 완료\n";

    // 2. 기존 resources_new 테이블이 있다면 삭제
    $pdo->exec("DROP TABLE IF EXISTS resources_new");
    echo "기존 resources_new 테이블 삭제 완료\n";

    // 3. 새 테이블 생성 (resources_backup과 동일한 구조)
    $pdo->exec("CREATE TABLE resources_new LIKE resources_backup");
    echo "새 테이블 생성 완료\n";

    // 4. 데이터 복사
    $pdo->exec("INSERT INTO resources_new SELECT * FROM resources_backup");
    echo "데이터 복사 완료\n";

    // 5. 기존 테이블 삭제
    $pdo->exec("DROP TABLE IF EXISTS resources");
    echo "기존 resources 테이블 삭제 완료\n";

    // 6. 새 테이블 이름 변경
    $pdo->exec("RENAME TABLE resources_new TO resources");
    echo "새 테이블을 resources로 이름 변경 완료\n";

    // 7. 외래 키 제약 조건 활성화
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "외래 키 제약 조건 활성화 완료\n";

    echo "모든 작업이 성공적으로 완료되었습니다.\n";

} catch (PDOException $e) {
    // 오류 발생 시 외래 키 제약 조건 다시 활성화
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "오류 발생: " . $e->getMessage() . "\n";
} 