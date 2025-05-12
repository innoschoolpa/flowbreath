<?php
// 모든 테이블의 구조(필드명, 타입)와 데이터를 출력하는 테스트 스크립트
require_once __DIR__ . '/config/database.php'; // 환경에 맞게 경로 조정

try {
    $pdo = Database::getConnection();

    // 1. 데이터베이스의 모든 테이블 목록 조회
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);

    if (!$tables) {
        echo "테이블이 없습니다.";
        exit;
    }

    // 2. 각 테이블의 구조 및 데이터 조회
    foreach ($tables as $tableRow) {
        $table = $tableRow[0];
        echo "<h2>테이블: " . htmlspecialchars($table) . "</h2>";

        // (1) 테이블 구조 출력
        $descStmt = $pdo->query("DESCRIBE `$table`");
        $columns = $descStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<b>필드 구조:</b>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            foreach ($col as $val) {
                echo "<td>" . htmlspecialchars((string)($val === null ? '' : $val)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";

        // (2) 테이블 데이터 출력
        $dataStmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "<p>데이터 없음</p><hr>";
            continue;
        }

        echo "<b>데이터:</b>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr>";
        foreach (array_keys($rows[0]) as $col) {
            echo "<th>" . htmlspecialchars($col) . "</th>";
        }
        echo "</tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><hr>";
    }
} catch (PDOException $e) {
    echo "DB 오류: " . htmlspecialchars($e->getMessage());
} 