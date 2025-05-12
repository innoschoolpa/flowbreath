<?php
require_once 'src/Config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // 1. 리소스 통계
    echo "=== 리소스 통계 ===\n";
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total_resources,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_resources,
            SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) as public_resources,
            SUM(view_count) as total_views,
            SUM(like_count) as total_likes
        FROM resources
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo "전체 리소스 수: {$stats['total_resources']}\n";
    echo "발행된 리소스 수: {$stats['published_resources']}\n";
    echo "공개 리소스 수: {$stats['public_resources']}\n";
    echo "총 조회수: {$stats['total_views']}\n";
    echo "총 좋아요 수: {$stats['total_likes']}\n\n";

    // 2. 최근 리소스
    echo "=== 최근 리소스 (최대 5개) ===\n";
    $recentResources = $pdo->query("
        SELECT r.*, u.name as author_name
        FROM resources r
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recentResources as $resource) {
        echo "ID: {$resource['id']}\n";
        echo "제목: {$resource['slug']}\n";
        echo "작성자: {$resource['author_name']}\n";
        echo "상태: {$resource['status']}\n";
        echo "가시성: {$resource['visibility']}\n";
        echo "조회수: {$resource['view_count']}\n";
        echo "좋아요: {$resource['like_count']}\n";
        echo "생성일: {$resource['created_at']}\n";
        echo "-------------------\n";
    }
    echo "\n";

    // 3. 태그 통계
    echo "=== 태그 통계 (상위 10개) ===\n";
    $topTags = $pdo->query("
        SELECT t.*, COUNT(rt.resource_id) as usage_count
        FROM tags t
        LEFT JOIN resource_tags rt ON t.id = rt.tag_id
        GROUP BY t.id
        ORDER BY usage_count DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($topTags as $tag) {
        echo "태그: {$tag['name']}\n";
        echo "사용 횟수: {$tag['usage_count']}\n";
        echo "-------------------\n";
    }
    echo "\n";

    // 4. 사용자 활동
    echo "=== 사용자 활동 ===\n";
    $userActivity = $pdo->query("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.role,
            u.status,
            u.login_count,
            u.last_login_at,
            COUNT(DISTINCT r.id) as resources_count,
            COUNT(DISTINCT c.id) as comments_count,
            COUNT(DISTINCT l.id) as likes_count
        FROM users u
        LEFT JOIN resources r ON u.id = r.user_id
        LEFT JOIN comments c ON u.id = c.user_id
        LEFT JOIN likes l ON u.id = l.user_id
        GROUP BY u.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($userActivity as $user) {
        echo "사용자: {$user['name']} ({$user['email']})\n";
        echo "역할: {$user['role']}\n";
        echo "상태: {$user['status']}\n";
        echo "로그인 횟수: {$user['login_count']}\n";
        echo "마지막 로그인: {$user['last_login_at']}\n";
        echo "작성 리소스: {$user['resources_count']}\n";
        echo "작성 댓글: {$user['comments_count']}\n";
        echo "좋아요: {$user['likes_count']}\n";
        echo "-------------------\n";
    }
    echo "\n";

    // 5. 시스템 리소스
    echo "=== 시스템 리소스 ===\n";
    $systemResources = $pdo->query("
        SELECT resource_type, COUNT(*) as count
        FROM system_resources
        GROUP BY resource_type
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($systemResources as $resource) {
        echo "타입: {$resource['resource_type']}\n";
        echo "개수: {$resource['count']}\n";
        echo "-------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 