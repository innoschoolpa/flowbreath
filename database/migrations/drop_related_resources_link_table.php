<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // related_resources_link 테이블 삭제
    $pdo->exec("DROP TABLE IF EXISTS related_resources_link");
    echo "related_resources_link 테이블이 성공적으로 삭제되었습니다.\n";

} catch (PDOException $e) {
    die("테이블 삭제 실패: " . $e->getMessage() . "\n");
} 