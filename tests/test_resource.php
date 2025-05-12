<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/Model/BaseModel.php';
require_once __DIR__ . '/../src/Model/Resource.php';

use Config\Database;
use Model\Resource;

// 테스트 결과 출력 함수
function printResult($test_name, $success) {
    echo str_pad($test_name, 50, '.') . ($success ? ' 성공' : ' 실패') . PHP_EOL;
}

try {
    // 데이터베이스 연결
    $pdo = Database::getInstance()->getConnection();
    $resourceModel = new Resource($pdo);
    echo "데이터베이스 연결 성공\n\n";

    echo "리소스 CRUD 테스트 시작...\n";
    echo "----------------------------------------\n";

    // 1. 리소스 생성 테스트
    $resource_data = [
        'user_id' => 1,
        'title' => '테스트 리소스',
        'content' => '테스트 내용입니다.',
        'summary' => '테스트 요약',
        'url' => 'https://example.com/test',
        'is_private' => 0
    ];

    $new_resource = $resourceModel->create($resource_data);
    $success = $new_resource && $new_resource['title'] === $resource_data['title'];
    printResult('리소스 생성', $success);

    // 2. 리소스 조회 테스트
    $resource = $resourceModel->getById($new_resource['id']);
    $success = $resource && $resource['title'] === $resource_data['title'];
    printResult('리소스 조회', $success);

    // 3. 리소스 태그 추가 테스트
    $tag_result = $resourceModel->addTag($new_resource['id'], '테스트태그');
    $tags = $resourceModel->getTags($new_resource['id']);
    $success = $tag_result && count($tags) > 0;
    printResult('리소스 태그 추가', $success);

    // 4. 리소스 검색 테스트
    $search_result = $resourceModel->search('테스트');
    $success = count($search_result) > 0 && $search_result[0]['title'] === $resource_data['title'];
    printResult('리소스 검색', $success);

    // 5. 리소스 업데이트 테스트
    $update_data = [
        'title' => '수정된 테스트 리소스',
        'content' => '수정된 테스트 내용입니다.'
    ];
    $updated_resource = $resourceModel->update($new_resource['id'], $update_data);
    $success = $updated_resource && $updated_resource['title'] === $update_data['title'];
    printResult('리소스 업데이트', $success);

    // 6. 리소스 공개/비공개 상태 변경 테스트
    $visibility_result = $resourceModel->updateVisibility($new_resource['id'], 1);
    $resource_after = $resourceModel->getById($new_resource['id']);
    $success = $visibility_result && $resource_after['is_private'] == 1;
    printResult('리소스 공개/비공개 상태 변경', $success);

    // 7. 리소스 태그 제거 테스트
    if (count($tags) > 0) {
        $remove_tag_result = $resourceModel->removeTag($new_resource['id'], $tags[0]['id']);
        $tags_after = $resourceModel->getTags($new_resource['id']);
        $success = $remove_tag_result && count($tags_after) === 0;
        printResult('리소스 태그 제거', $success);
    }

    // 8. 리소스 삭제 테스트
    $delete_result = $resourceModel->delete($new_resource['id']);
    $resource_after = $resourceModel->getById($new_resource['id']);
    $success = $delete_result && !$resource_after;
    printResult('리소스 삭제', $success);

    echo "\n테스트 완료!\n";

} catch (Exception $e) {
    echo "\n오류 발생: " . $e->getMessage() . "\n";
    exit(1);
} 