<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 백업 디렉토리 설정
    $backupDir = __DIR__ . '/backups';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    // 백업 파일명 생성
    $date = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "/backup_{$date}.sql";
    
    echo "=== 데이터베이스 백업 시작 ===\n";
    echo "백업 파일: $backupFile\n\n";
    
    // 테이블 목록 가져오기
    $tables = ['resources', 'tags', 'resource_tags', 'resource_translations'];
    
    // 백업 파일 생성
    $output = "-- FlowBreath Database Backup\n";
    $output .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 각 테이블의 구조와 데이터 백업
    foreach ($tables as $table) {
        echo "테이블 백업 중: $table\n";
        
        // 테이블 구조 백업
        $stmt = $pdo->query("SHOW CREATE TABLE $table");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= "\n-- Table structure for table `$table`\n";
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= $row['Create Table'] . ";\n\n";
        
        // 테이블 데이터 백업
        $stmt = $pdo->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $output .= "-- Data for table `$table`\n";
            $output .= "INSERT INTO `$table` VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    $rowValues[] = $value === null ? 'NULL' : $pdo->quote($value);
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }
            
            $output .= implode(",\n", $values) . ";\n";
        }
    }
    
    // 백업 파일 저장
    file_put_contents($backupFile, $output);
    
    echo "\n=== 데이터베이스 백업 완료 ===\n";
    echo "백업 파일 크기: " . round(filesize($backupFile) / 1024, 2) . " KB\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 