<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/Database.php';

use App\Core\Database;

// 데이터베이스 연결
$db = Database::getInstance();

// 요청 메소드 확인
$method = $_SERVER['REQUEST_METHOD'];

// 리소스 ID와 언어 코드를 받음
$resourceId = null;
$languageCode = null;

if ($method === 'POST') {
    $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_VALIDATE_INT);
    $languageCode = filter_input(INPUT_POST, 'language_code', FILTER_SANITIZE_STRING);
} elseif ($method === 'DELETE') {
    // URL에서 resource_id 추출
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    $resourceId = filter_var($segments[array_search('resources', $segments) + 1], FILTER_VALIDATE_INT);
    
    // 요청 본문에서 language_code 추출
    $input = json_decode(file_get_contents('php://input'), true);
    $languageCode = $input['language_code'] ?? null;
}

// 입력값 검증
if (!$resourceId || !$languageCode || !in_array($languageCode, ['ko', 'en'])) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => '유효하지 않은 리소스 ID 또는 언어 코드입니다.'
    ]));
}

try {
    // 트랜잭션 시작
    $db->beginTransaction();

    // 리소스 존재 여부 확인
    $stmt = $db->prepare("SELECT id FROM resources WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$resourceId]);
    if (!$stmt->fetch()) {
        throw new Exception('존재하지 않는 리소스입니다.');
    }

    // 해당 언어의 번역본 존재 여부 확인
    $stmt = $db->prepare("SELECT id FROM resource_translations WHERE resource_id = ? AND language_code = ?");
    $stmt->execute([$resourceId, $languageCode]);
    if (!$stmt->fetch()) {
        throw new Exception('해당 언어의 번역본이 존재하지 않습니다.');
    }

    // 해당 리소스의 전체 번역본 개수 확인
    $stmt = $db->prepare("SELECT COUNT(*) as translation_count FROM resource_translations WHERE resource_id = ?");
    $stmt->execute([$resourceId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $translationCount = (int)$result['translation_count'];

    // 번역본 삭제
    $stmt = $db->prepare("DELETE FROM resource_translations WHERE resource_id = ? AND language_code = ?");
    $deleteResult = $stmt->execute([$resourceId, $languageCode]);
    
    if (!$deleteResult) {
        throw new Exception('번역본 삭제 중 오류가 발생했습니다.');
    }

    // 번역본이 하나뿐이었다면 원본 리소스도 삭제
    if ($translationCount === 1) {
        $stmt = $db->prepare("UPDATE resources SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateResult = $stmt->execute([$resourceId]);
        
        if (!$updateResult) {
            throw new Exception('리소스 삭제 중 오류가 발생했습니다.');
        }
    }

    // 트랜잭션 커밋
    if (!$db->commit()) {
        throw new Exception('트랜잭션 커밋 중 오류가 발생했습니다.');
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '번역본이 성공적으로 삭제되었습니다.',
        'data' => [
            'resource_id' => $resourceId,
            'language_code' => $languageCode,
            'original_deleted' => $translationCount === 1
        ]
    ]);

} catch (Exception $e) {
    // 오류 발생 시 롤백
    try {
        $db->rollBack();
    } catch (Exception $rollbackError) {
        error_log("Rollback error: " . $rollbackError->getMessage());
    }
    
    error_log("Translation deletion error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} 