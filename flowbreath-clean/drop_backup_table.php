<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    // resources_backup 테이블 삭제
    $pdo->exec("DROP TABLE IF EXISTS resources_backup");
    echo "resources_backup 테이블이 성공적으로 삭제되었습니다.\n";

} catch (PDOException $e) {
    echo "오류 발생: " . $e->getMessage() . "\n";
} 