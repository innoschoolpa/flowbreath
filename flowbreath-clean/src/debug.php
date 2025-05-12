<?php

require_once __DIR__ . '/bootstrap.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // 테이블 구조 확인
    $sql = "DESCRIBE users";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table structure:\n";
    print_r($structure);
    
    // 세션 정보 확인
    echo "\nSession data:\n";
    print_r($_SESSION);
    
    // 사용자 데이터 확인
    if (isset($_SESSION['user_id'])) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nUser data:\n";
        print_r($user);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 