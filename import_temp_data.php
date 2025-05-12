<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // 임시 테이블 데이터 삭제
    $pdo->exec("TRUNCATE TABLE temp_resources");

    // 데이터 삽입
    $data = [
        [
            'resource_id' => 1,
            'title' => '횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안',
            'url' => 'https://youtu.be/JttlriUGVu4?si=E4w3VzKyZzTojfip',
            'source_type' => 'Video',
            'author_creator' => '연세신통TV',
            'publication_info' => '유튜브',
            'summary' => '요약: 횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안...',
            'initial_impression' => '첫 인상 예시',
            'personal_connection' => '개인적 연결 예시',
            'reliability' => 'High',
            'reliability_rationale' => '신뢰성 근거 예시',
            'usefulness' => 'High',
            'usefulness_context' => '유용성 맥락 예시',
            'perspective_bias' => '관점/편향 예시',
            'strengths' => '강점 예시',
            'weaknesses_limitations' => '약점/한계 예시',
            'flowbreath_relevance' => 'FlowBreath 연관성 예시',
            'reflection_insights' => '성찰/인사이트 예시',
            'application_ideas' => '적용 아이디어 예시',
            'date_added' => '2025-05-03 09:52:06',
            'last_updated' => '2025-05-04 23:00:03',
            'is_public' => 1,
            'is_translated' => 0,
            'is_pinned' => 0,
            'content_language' => 'ko',
            'content' => '요약: 횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안...',
            'created_at' => '2025-05-05 00:49:22',
            'updated_at' => '2025-05-05 00:49:22'
        ],
        [
            'resource_id' => 2,
            'title' => '올바른 복식 호흡 방법과 효과',
            'url' => 'https://youtu.be/FiAfRGt5x5s?si=RCueKRHEhlJnN-Lo',
            'source_type' => 'Video',
            'author_creator' => '가톨릭대학교 인천성모병원',
            'publication_info' => '유튜브',
            'summary' => '요약: 올바른 복식 호흡 방법과 효과...',
            'initial_impression' => '',
            'personal_connection' => '',
            'reliability' => 'High',
            'reliability_rationale' => '',
            'usefulness' => 'High',
            'usefulness_context' => '',
            'perspective_bias' => '',
            'strengths' => '',
            'weaknesses_limitations' => '',
            'flowbreath_relevance' => '',
            'reflection_insights' => '',
            'application_ideas' => '',
            'date_added' => '2025-05-03 10:20:40',
            'last_updated' => '2025-05-04 23:00:03',
            'is_public' => 1,
            'is_translated' => 0,
            'is_pinned' => 0,
            'content_language' => 'ko',
            'content' => '요약: 올바른 복식 호흡 방법과 효과...',
            'created_at' => '2025-05-05 00:49:22',
            'updated_at' => '2025-05-05 00:49:22'
        ]
    ];

    // 데이터 삽입
    $stmt = $pdo->prepare("
        INSERT INTO temp_resources (
            resource_id, title, url, source_type, author_creator, publication_info,
            summary, initial_impression, personal_connection, reliability,
            reliability_rationale, usefulness, usefulness_context, perspective_bias,
            strengths, weaknesses_limitations, flowbreath_relevance,
            reflection_insights, application_ideas, date_added, last_updated,
            is_public, is_translated, is_pinned, content_language, content,
            created_at, updated_at
        ) VALUES (
            :resource_id, :title, :url, :source_type, :author_creator, :publication_info,
            :summary, :initial_impression, :personal_connection, :reliability,
            :reliability_rationale, :usefulness, :usefulness_context, :perspective_bias,
            :strengths, :weaknesses_limitations, :flowbreath_relevance,
            :reflection_insights, :application_ideas, :date_added, :last_updated,
            :is_public, :is_translated, :is_pinned, :content_language, :content,
            :created_at, :updated_at
        )
    ");

    foreach ($data as $row) {
        $stmt->execute($row);
    }

    // 데이터 확인
    $stmt = $pdo->query("SELECT COUNT(*) FROM temp_resources");
    $count = $stmt->fetchColumn();

    echo "Data import completed successfully!\n";
    echo "Number of records imported: " . $count . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 