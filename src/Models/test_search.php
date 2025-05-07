<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Resource;

// DB 연결
$pdo = getDbConnection();

// Resource 모델 인스턴스 생성
$resourceModel = new Resource($pdo);

// 테스트 검색어
$searchTerm = '호흡';

// 검색 실행
try {
    $results = $resourceModel->searchResources($searchTerm, 10, 0);
    echo "검색 결과 개수: " . count($results) . PHP_EOL;
    foreach ($results as $row) {
        echo "ID: {$row['id']} | 제목: {$row['title']}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "에러: " . $e->getMessage() . PHP_EOL;
} 