<?php
// 복구 전 반드시 DB 백업을 하세요!
// DB 연결 정보는 config/database.php에서 자동으로 불러옵니다.

require_once __DIR__ . '/config/database.php';

$host = $config['host'];
$db   = $config['database'];
$user = $config['username'];
$pass = $config['password'];
$charset = $config['charset'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('DB 연결 실패: ' . $e->getMessage());
}

// resources 테이블 복구
$stmt = $pdo->query("SELECT id, content FROM resources WHERE content LIKE '%&lt;%' OR content LIKE '%&gt;%' ");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $decoded = html_entity_decode($row['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($decoded !== $row['content']) {
        $update = $pdo->prepare("UPDATE resources SET content = ? WHERE id = ?");
        $update->execute([$decoded, $row['id']]);
        echo "[resources] ID {$row['id']} 복구 완료<br>\n";
    }
}

// resource_translations 테이블 복구
if ($pdo->query("SHOW TABLES LIKE 'resource_translations'")->rowCount() > 0) {
    $stmt2 = $pdo->query("SELECT id, content FROM resource_translations WHERE content LIKE '%&lt;%' OR content LIKE '%&gt;%' ");
    $rows2 = $stmt2->fetchAll();
    foreach ($rows2 as $row) {
        $decoded = html_entity_decode($row['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded !== $row['content']) {
            $update = $pdo->prepare("UPDATE resource_translations SET content = ? WHERE id = ?");
            $update->execute([$decoded, $row['id']]);
            echo "[resource_translations] ID {$row['id']} 복구 완료<br>\n";
        }
    }
}
echo "모든 복구 완료!"; 