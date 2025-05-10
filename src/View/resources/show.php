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
                    <div>
                        <?php if (isset($_SESSION['user_id']) && isset($resource['user_id']) && $_SESSION['user_id'] === $resource['user_id']): ?>
                            <a href="/resources/<?php echo htmlspecialchars($resource['id']); ?>/edit" class="btn btn-primary">
                                <i class="fas fa-edit"></i> 수정
                            </a>
                            <form action="/resources/<?php echo htmlspecialchars($resource['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('정말로 이 리소스를 삭제하시겠습니까?');">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                <button type="submit" class="btn btn-danger ms-1">
                                    <i class="fas fa-trash"></i> 삭제
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($resource['content'])): ?>
                        <div class="mb-4">
                            <h5>상세 내용</h5>
                            <div class="card-text">
                                <?php
                                if (function_exists('is_html') && is_html($resource['content'])) {
                                    echo $resource['content'];
                                } else if (function_exists('markdown_to_html')) {
                                    echo markdown_to_html($resource['content']);
                                } else {
                                    echo nl2br(htmlspecialchars($resource['content']));
                                }
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($resource['description'])): ?>
                        <div class="alert alert-info mb-4" style="white-space:pre-line;">
                            <strong>설명:</strong> <?= htmlspecialchars($resource['description']) ?>
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
                        <ul class="list-unstyled small" id="db-meta-list" style="max-height: 120px; overflow: hidden; transition: max-height 0.3s;">
                            <li><b>ID:</b> <?= htmlspecialchars($resource['id']) ?></li>
                            <li><b>언어 코드:</b> <?= htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko') ?></li>
                            <li><b>작성자 ID:</b> <?= htmlspecialchars($resource['user_id'] ?? '-') ?></li>
                            <li><b>작성일:</b> <?= htmlspecialchars($resource['created_at'] ?? '-') ?></li>
                            <li><b>수정일:</b> <?= htmlspecialchars($resource['updated_at'] ?? '-') ?></li>
                            <li><b>공개여부:</b> <?= ($resource['visibility'] ?? 'public') === 'public' ? '공개' : '비공개' ?></li>
                            <li><b>유형:</b> <?= htmlspecialchars($resource['type'] ?? '-') ?></li>
                            <li><b>슬러그:</b> <?= htmlspecialchars($resource['slug'] ?? '-') ?></li>
                            <li><b>파일:</b> <?= htmlspecialchars($resource['file_path'] ?? '-') ?></li>
                            <li><b>설명(description):</b> <?= htmlspecialchars($resource['description'] ?? '-') ?></li>
                            <!-- 필요시 추가 필드 -->
                        </ul>
                        <button id="toggle-meta-btn" class="btn btn-sm btn-outline-secondary mt-2">더보기</button>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="/resources" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> 목록으로
                </a>
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
        const metaList = document.getElementById('db-meta-list');
        const toggleBtn = document.getElementById('toggle-meta-btn');
        let expanded = false;
        toggleBtn.addEventListener('click', function() {
            expanded = !expanded;
            if (expanded) {
                metaList.style.maxHeight = '1000px';
                toggleBtn.textContent = '접기';
            } else {
                metaList.style.maxHeight = '120px';
                toggleBtn.textContent = '더보기';
            }
        });
    });
    </script>
</body>
</html>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 