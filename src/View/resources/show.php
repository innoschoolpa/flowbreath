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
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="card-title">내용</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($resource['content'] ?? '')); ?></p>
                    </div>

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
                        <h5 class="card-title">메타 정보</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-user"></i> 작성자: <?php echo htmlspecialchars($resource['user_name'] ?? '알 수 없음'); ?></li>
                            <li><i class="fas fa-calendar"></i> 작성일: <?php echo isset($resource['created_at']) ? date('Y-m-d H:i', strtotime($resource['created_at'])) : '알 수 없음'; ?></li>
                            <li><i class="fas fa-eye"></i> 조회수: <?php echo number_format($resource['view_count'] ?? 0); ?></li>
                            <li><i class="fas fa-heart"></i> 좋아요: <?php echo number_format($resource['like_count'] ?? 0); ?></li>
                            <li><i class="fas fa-comments"></i> 댓글: <?php echo number_format($resource['comment_count'] ?? 0); ?></li>
                        </ul>
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
</body>
</html> 