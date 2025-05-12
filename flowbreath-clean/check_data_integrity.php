<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== 데이터 정합성 검사 시작 ===\n\n";
    
    // 1. 고아 레코드 검사
    echo "1. 고아 레코드 검사\n";
    
    // resource_tags의 고아 레코드
    $stmt = $pdo->query("
        SELECT rt.* 
        FROM resource_tags rt
        LEFT JOIN resources r ON rt.resource_id = r.id
        WHERE r.id IS NULL
    ");
    $orphanedTags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($orphanedTags) > 0) {
        echo "  - resource_tags 테이블에 고아 레코드 발견: " . count($orphanedTags) . "개\n";
    }
    
    // resource_translations의 고아 레코드
    $stmt = $pdo->query("
        SELECT rt.* 
        FROM resource_translations rt
        LEFT JOIN resources r ON rt.resource_id = r.id
        WHERE r.id IS NULL
    ");
    $orphanedTranslations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($orphanedTranslations) > 0) {
        echo "  - resource_translations 테이블에 고아 레코드 발견: " . count($orphanedTranslations) . "개\n";
    }
    
    // 2. 중복 데이터 검사
    echo "\n2. 중복 데이터 검사\n";
    
    // 중복된 태그명
    $stmt = $pdo->query("
        SELECT name, COUNT(*) as count
        FROM tags
        GROUP BY name
        HAVING count > 1
    ");
    $duplicateTags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($duplicateTags) > 0) {
        echo "  - 중복된 태그명 발견: " . count($duplicateTags) . "개\n";
    }
    
    // 3. 누락된 필수 데이터 검사
    echo "\n3. 누락된 필수 데이터 검사\n";
    
    // 제목이 없는 리소스
    $stmt = $pdo->query("
        SELECT id 
        FROM resources 
        WHERE title IS NULL OR title = ''
    ");
    $missingTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($missingTitles) > 0) {
        echo "  - 제목이 없는 리소스 발견: " . count($missingTitles) . "개\n";
    }
    
    // 4. 데이터 일관성 검사
    echo "\n4. 데이터 일관성 검사\n";
    
    // published_at이 created_at보다 이전인 리소스
    $stmt = $pdo->query("
        SELECT id, title, created_at, published_at
        FROM resources
        WHERE published_at < created_at
    ");
    $inconsistentDates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($inconsistentDates) > 0) {
        echo "  - 발행일이 생성일보다 이전인 리소스 발견: " . count($inconsistentDates) . "개\n";
    }
    
    echo "\n=== 데이터 정합성 검사 완료 ===\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 