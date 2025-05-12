<?php
require_once __DIR__ . '/src/Model/BaseModel.php';
require_once __DIR__ . '/src/Model/Resource.php';
require_once __DIR__ . '/src/config/database.php';

use Model\Resource;
use Config\Database;

try {
    // 데이터베이스 연결 (싱글톤 패턴 사용)
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Resource 인스턴스 생성
    $resource = new Resource($pdo);
    
    // 테스트: 전체 리소스 조회
    $resources = $resource->all();
    echo "Total resources: " . count($resources) . "\n";
    
    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 