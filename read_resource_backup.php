<?php
require 'config/database.php';

$pdo = getDbConnection();

try {
    $stmt = $pdo->query("SELECT * FROM resource_backup");
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        echo "resource_backup 테이블에 데이터가 없습니다.\n";
    } else {
        foreach ($rows as $row) {
            print_r($row);
            echo "----------------------\n";
        }
    }
} catch (PDOException $e) {
    echo "쿼리 실행 중 오류 발생: " . $e->getMessage() . "\n";
} 