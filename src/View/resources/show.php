<?php
// src/View/resources/show.php

// Generate meta description from content
$description = !empty($resource['content']) ? 
    substr(strip_tags(is_html($resource['content']) ? $resource['content'] : markdown_to_html($resource['content'])), 0, 160) . '...' : 
    'Resource details page';

// Get the first image URL from content if exists, otherwise use default
$imageUrl = !empty($resource['featured_image']) ? $resource['featured_image'] : '/assets/images/default-resource.jpg';

// Get absolute URL for the current page
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<!-- SEO Meta Tags -->
<head>
    <title><?php echo htmlspecialchars($resource['title']); ?> - FlowBreath Resources</title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo !empty($tags) ? htmlspecialchars(implode(', ', array_column($tags, 'tag_name'))) : ''; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($resource['author_creator']); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">

    <!-- Open Graph Tags for Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($resource['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($imageUrl); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="FlowBreath Resources">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($resource['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($imageUrl); ?>">

    <!-- Schema.org markup for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?php echo htmlspecialchars($resource['title']); ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo htmlspecialchars($resource['author_creator']); ?>"
        },
        "datePublished": "<?php echo htmlspecialchars($resource['date_added']); ?>",
        "dateModified": "<?php echo htmlspecialchars($resource['last_updated']); ?>",
        "description": "<?php echo htmlspecialchars($description); ?>",
        "image": "<?php echo htmlspecialchars($imageUrl); ?>"
    }
    </script>
</head>

<!-- Social Share Buttons -->
<div class="social-share-buttons mb-4">
    <button class="btn btn-primary me-2" onclick="shareOnFacebook()">
        <i class="fab fa-facebook-f"></i> Share on Facebook
    </button>
    <button class="btn btn-info me-2" onclick="shareOnTwitter()">
        <i class="fab fa-twitter"></i> Share on Twitter
    </button>
    <button class="btn btn-success me-2" onclick="shareOnLinkedIn()">
        <i class="fab fa-linkedin-in"></i> Share on LinkedIn
    </button>
    <button class="btn btn-secondary" onclick="copyShareLink()">
        <i class="fas fa-link"></i> Copy Link
    </button>
</div>

<script>
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank');
}

function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`, '_blank');
}

function copyShareLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => {
            alert('Link copied to clipboard!');
        })
        .catch(err => {
            console.error('Failed to copy link: ', err);
        });
}
</script>

<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/resources">자료 목록</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($resource['title']) ?></li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <h1 class="card-title h2 mb-4"><?= htmlspecialchars($resource['title']) ?></h1>
            
            <div class="resource-meta mb-4">
                <span class="me-3">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($resource['user_name'] ?? '익명') ?>
                </span>
                <span class="me-3">
                    <i class="fas fa-calendar"></i> <?= date('Y-m-d H:i', strtotime($resource['created_at'])) ?>
                </span>
                <span>
                    <i class="fas fa-eye"></i> <?= number_format($resource['view_count'] ?? 0) ?>
                </span>
            </div>

            <?php if (!empty($resource['tags'])): ?>
                <div class="mb-4">
                    <?php foreach ($resource['tags'] as $tag): ?>
                        <a href="/resources?tag=<?= urlencode($tag) ?>" class="tag-badge text-decoration-none">
                            #<?= htmlspecialchars($tag) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="resource-content mb-4">
                <?= nl2br(htmlspecialchars($resource['content'])) ?>
            </div>

            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] == $resource['user_id'] || $_SESSION['user']['is_admin'])): ?>
                <div class="btn-group">
                    <a href="/resources/edit/<?= $resource['id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> 수정
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 댓글 섹션 -->
    <div class="mt-5">
        <h3>댓글</h3>
        <div id="comments-container">
            <!-- 댓글은 JavaScript로 로드됩니다 -->
        </div>
        <?php if (isset($_SESSION['user'])): ?>
            <div class="card mt-3">
                <div class="card-body">
                    <form id="comment-form">
                        <div class="mb-3">
                            <textarea class="form-control" rows="3" placeholder="댓글을 작성하세요..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">댓글 작성</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                댓글을 작성하려면 <a href="/login">로그인</a>이 필요합니다.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] == $resource['user_id'] || $_SESSION['user']['is_admin'])): ?>
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">자료 삭제 확인</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    정말로 이 자료를 삭제하시겠습니까?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <form action="/resources/delete/<?= $resource['id'] ?>" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">삭제</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?> 