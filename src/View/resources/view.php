<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// src/View/resources/show.php

// Generate meta description from content
$description = !empty($resource['content']) ? 
    substr(strip_tags($resource['content']), 0, 160) . '...' : 
    'Resource details page';

// Get the first image URL from content if exists, otherwise use default
$imageUrl = !empty($resource['featured_image']) ? $resource['featured_image'] : '/assets/images/default-resource.jpg';

// Get absolute URL for the current page
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$title = $title ?? '리소스 상세';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
    /* 컨텐츠 영역 스타일 */
    .card-text {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    /* 이미지 컨테이너 */
    .card-text figure {
        max-width: 100%;
        margin: 1rem 0;
        box-sizing: border-box;
        clear: both;
    }

    /* 이미지 스타일 */
    .card-text img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 1rem auto;
        box-sizing: border-box;
        object-fit: contain;
    }

    /* 이미지 정렬 */
    .card-text .image-align-left {
        float: left;
        margin: 0.5rem 1rem 0.5rem 0;
        max-width: 45%;
    }

    .card-text .image-align-center {
        margin: 1rem auto;
        max-width: 100%;
    }

    .card-text .image-align-right {
        float: right;
        margin: 0.5rem 0 0.5rem 1rem;
        max-width: 45%;
    }

    /* 반응형 이미지 */
    @media (max-width: 768px) {
        .card-text .image-align-left,
        .card-text .image-align-right {
            max-width: 100%;
            float: none;
            margin: 1rem 0;
        }
    }
    </style>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo !empty($resource['tags']) ? htmlspecialchars(implode(', ', $resource['tags'])) : ''; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($resource['user_name'] ?? 'FlowBreath'); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">

    <!-- Open Graph Tags for Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($resource['title'] ?? $title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($imageUrl); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="FlowBreath Resources">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($resource['title'] ?? $title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($imageUrl); ?>">

    <!-- Schema.org markup for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": <?php echo json_encode($resource['title'] ?? $title); ?>,
        "author": {
            "@type": "Person",
            "name": <?php echo json_encode($resource['user_name'] ?? 'FlowBreath'); ?>
        },
        "datePublished": <?php echo json_encode($resource['created_at'] ?? date('Y-m-d H:i:s')); ?>,
        "dateModified": <?php echo json_encode($resource['updated_at'] ?? $resource['created_at'] ?? date('Y-m-d H:i:s')); ?>,
        "description": <?php echo json_encode($description); ?>,
        "image": <?php echo json_encode($imageUrl); ?>
    }
    </script>
</head>
<body>
    <div class="container py-5">
        <?php if (isset($resource) && !empty($resource)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($resource['title'] ?? ''); ?></h1>
                    <div class="d-flex gap-2">
                        <a href="/resources" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> 목록으로
                        </a>
                        <?php if (isset($_SESSION['user_id']) && isset($resource['user_id']) && $_SESSION['user_id'] === $resource['user_id']): ?>
                            <a href="/resources/edit/<?php echo htmlspecialchars($resource['id']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> 수정
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($resource['id']); ?>', '<?php echo htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko'); ?>')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($resource['content'])): ?>
                        <?php
                        // link 또는 content에서 유튜브 링크 추출
                        $videoId = null;
                        if (!empty($resource['link'])) {
                            if (preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^"&?\\/\\s]{11})/', $resource['link'], $matches)) {
                                $videoId = $matches[1];
                            }
                        }
                        if (!$videoId && !empty($resource['content'])) {
                            if (preg_match('/https?:\/\/(www\.)?(youtube\\.com|youtu\\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                                if (preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^"&?\\/\\s]{11})/', $ytMatch[0], $matches)) {
                                    $videoId = $matches[1];
                                }
                            }
                        }
                        if ($videoId): ?>
                            <div class="mb-4" style="max-width:640px;margin:24px auto 0 auto;">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/<?= $videoId ?>"
                                            title="YouTube video"
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <h5>상세 내용</h5>
                            <div class="card-text">
                                <?php
                                // HTML 엔티티가 중첩되어 저장된 경우를 방지하고, 줄바꿈은 <br>로 변환
                                $content = $resource['content'] ?? '';
                                // 1. 중첩된 엔티티를 한 번만 디코딩
                                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // 2. <br>이 아닌 \n은 <br>로 변환
                                $content = nl2br($content, false);
                                echo $content;
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($resource['link'])): ?>
                            <div class="mb-3">
                                <strong>링크:</strong> <a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($resource['link']) ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($resource['description'])): ?>
                        <div class="alert alert-info mb-4">
                            <strong>설명:</strong> <?php echo html_entity_decode($resource['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($resource['tags'])): ?>
                        <div class="mb-4">
                            <h5 class="card-title">태그</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($resource['tags'] as $tag): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5 class="card-title">DB 원본 정보</h5>
                        <ul class="list-unstyled small">
                            <li><b>ID:</b> <?= htmlspecialchars($resource['id']) ?></li>
                            <li><b>언어 코드:</b> <?= htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko') ?></li>
                            <li><b>작성자:</b> <a href="/profile/<?= htmlspecialchars($resource['user_id']) ?>" class="text-decoration-none"><?= htmlspecialchars($resource['author_name'] ?? '-') ?></a></li>
                            <li><b>작성일:</b> <?= htmlspecialchars($resource['created_at'] ?? '-') ?></li>
                            <li><b>수정일:</b> <?= htmlspecialchars($resource['updated_at'] ?? '-') ?></li>
                            <li><b>공개여부:</b> <?= ($resource['visibility'] ?? 'public') === 'public' ? '공개' : '비공개' ?></li>
                            <li><b>유형:</b> <?= htmlspecialchars($resource['type'] ?? '-') ?></li>
                            <li><b>슬러그:</b> <?= htmlspecialchars($resource['slug'] ?? '-') ?></li>
                            <li><b>파일:</b> <?= htmlspecialchars($resource['file_path'] ?? '-') ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex gap-2">
                    <a href="/resources" class="btn btn-secondary">
                        <i class="fas fa-list"></i> 목록으로
                    </a>
                    <?php if (isset($_SESSION['user_id']) && isset($resource['user_id']) && $_SESSION['user_id'] === $resource['user_id']): ?>
                        <a href="/resources/edit/<?php echo htmlspecialchars($resource['id']); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> 수정
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars($resource['id']); ?>', '<?php echo htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko'); ?>')">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                리소스를 찾을 수 없습니다.
            </div>
            <a href="/resources" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 더보기 버튼 관련 코드 제거
    });

    function confirmDelete(resourceId, languageCode) {
        if (confirm('정말로 이 번역본을 삭제하시겠습니까?')) {
            fetch(`/api/resources/${resourceId}/translation`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                },
                body: JSON.stringify({ language_code: languageCode })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('서버 응답이 올바르지 않습니다.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // 성공 메시지 표시
                    alert(data.message);
                    
                    // 리소스가 완전히 삭제되었는지 확인
                    if (data.data && data.data.original_deleted) {
                        window.location.href = '/resources';
                    } else {
                        window.location.reload();
                    }
                } else {
                    // 서버에서 반환된 에러 메시지 표시
                    alert(data.error || '알 수 없는 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || '삭제 중 오류가 발생했습니다.');
            });
        }
    }
    </script>
</body>
</html>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 