<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

class DatabaseAnalyzer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function analyze() {
        try {
            // 모든 테이블 조회
            $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            echo "\n=== Database Structure Analysis ===\n\n";
            
            foreach ($tables as $table) {
                echo "\nTable: {$table}\n";
                echo str_repeat('-', strlen($table) + 7) . "\n";
                
                // 테이블 구조 조회
                $columns = $this->db->query("SHOW FULL COLUMNS FROM {$table}")->fetchAll();
                echo "Columns:\n";
                foreach ($columns as $column) {
                    echo sprintf(
                        "  %-20s %-20s %-8s %-8s %s\n",
                        $column['Field'],
                        $column['Type'],
                        $column['Null'],
                        $column['Key'],
                        $column['Comment']
                    );
                }
                
                // 인덱스 조회
                $indexes = $this->db->query("SHOW INDEX FROM {$table}")->fetchAll();
                if ($indexes) {
                    echo "\nIndexes:\n";
                    foreach ($indexes as $index) {
                        echo sprintf(
                            "  %-20s %-20s %s\n",
                            $index['Key_name'],
                            $index['Column_name'],
                            $index['Non_unique'] ? 'Non-Unique' : 'Unique'
                        );
                    }
                }
                
                // 레코드 수 조회
                $count = $this->db->query("SELECT COUNT(*) as count FROM {$table}")->fetch();
                echo "\nTotal Records: {$count['count']}\n";
                
                // 샘플 데이터 조회 (첫 번째 레코드)
                $sample = $this->db->query("SELECT * FROM {$table} LIMIT 1")->fetch();
                if ($sample) {
                    echo "\nSample Record:\n";
                    print_r($sample);
                }
                
                echo "\n" . str_repeat('=', 50) . "\n";
            }
            
        } catch (Exception $e) {
            echo "Error analyzing database: " . $e->getMessage() . "\n";
        }
    }
}

// 분석 실행
$analyzer = new DatabaseAnalyzer();
$analyzer->analyze(); 