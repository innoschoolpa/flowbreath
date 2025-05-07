<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // 데이터베이스 연결 설정
    $host = 'srv636.hstgr.io';
    $dbname = 'u573434051_flowbreath';
    $username = 'u573434051_flow';
    $password = 'Eduispa1712!';
    
    // PDO 연결 생성
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // resources2의 모든 데이터 가져오기
    $stmt = $pdo->query("SELECT * FROM resources2");
    $resource2Data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($resource2Data) . " records in resources2 table.\n";
    
    // resources 테이블에 데이터 삽입
    $stmt = $pdo->prepare("
        INSERT INTO resources (
            user_id, title, slug, content, description,
            visibility, status, published_at, view_count,
            created_at, updated_at
        ) VALUES (
            :user_id, :title, :slug, :content, :description,
            :visibility, :status, :published_at, :view_count,
            :created_at, :updated_at
        )
    ");
    
    foreach ($resource2Data as $data) {
        // slug 생성 (영문자, 숫자, 하이픈만 허용)
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $data['title']), '-'));
        
        $stmt->execute([
            'user_id' => 1, // 기본 사용자 ID
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'] ?? $data['description'],
            'description' => $data['description'],
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => $data['created_at'],
            'view_count' => 0,
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        ]);
        
        echo "Migrated: {$data['title']}\n";
    }
    
    // 트랜잭션 커밋
    $pdo->commit();
    echo "\nMigration completed successfully!\n";
    
} catch (PDOException $e) {
    // 오류 발생 시 롤백
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    // 기타 오류 발생 시 롤백
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
} 