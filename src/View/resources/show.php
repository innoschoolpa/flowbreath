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

<div class="container mt-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo htmlspecialchars($resource['title']); ?></h2>
        <div>
            <?php if (is_admin()): ?>
                <a href="/resources/create" class="btn btn-success me-2">리소스 등록</a>
            <?php endif; ?>
            <a href="/resources/edit/<?php echo $resource['resource_id']; ?>" class="btn btn-primary me-2">수정</a>
            <form action="/resources/delete/<?php echo $resource['resource_id']; ?>" method="POST" class="d-inline">
                <button type="submit" class="btn btn-danger" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- <h1 class="mb-4"><?php echo htmlspecialchars($resource['title'] ?? ''); ?></h1> -->
            
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">기본 정보</h5>
                    <dl class="row">
                        <dt class="col-sm-3">유형</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($resource['source_type'] ?? ''); ?></dd>
                        
                        <dt class="col-sm-3">저자/창작자</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($resource['author_creator'] ?? ''); ?></dd>
                        
                        <dt class="col-sm-3">출판/발행 정보</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['publication_info'] ?? '')); ?></dd>
                        
                        <dt class="col-sm-3">URL</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($resource['url'])): ?>
                                <a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($resource['url']); ?>
                                </a>
                                <?php
                                // 유튜브 URL에서 영상 ID 추출 함수
                                function extractYoutubeId($url) {
                                    if (preg_match('/(?:youtube\\.com\\/(?:[^\\/\\n\\s]+\\/\\S+\\/|(?:v|e(?:mbed)?|shorts)\\/|.*[?&]v=)|youtu\\.be\\/)([\\w-]{11})/', $url, $matches)) {
                                        return $matches[1];
                                    }
                                    return null;
                                }
                                $youtubeId = extractYoutubeId($resource['url']);
                                ?>
                                <?php if ($youtubeId): ?>
                                    <div class="mt-3 mb-3">
                                        <div class="ratio ratio-16x9">
                                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtubeId); ?>" title="YouTube video preview" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Content -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">내용</h5>
                    <div class="markdown-content">
                        <?php
                        $content = $resource['content'] ?? '';
                        if (is_html($content)) {
                            echo $content;
                        } else {
                            echo markdown_to_html($content);
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Evaluation -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">평가</h5>
                    <dl class="row">
                        <dt class="col-sm-3">신뢰성</dt>
                        <dd class="col-sm-9">
                            <?php echo htmlspecialchars($resource['reliability'] ?? ''); ?>
                            <?php if (!empty($resource['reliability_rationale'])): ?>
                                <div class="mt-2"><?php echo nl2br(htmlspecialchars($resource['reliability_rationale'])); ?></div>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-3">유용성</dt>
                        <dd class="col-sm-9">
                            <?php echo htmlspecialchars($resource['usefulness'] ?? ''); ?>
                            <?php if (!empty($resource['usefulness_context'])): ?>
                                <div class="mt-2"><?php echo nl2br(htmlspecialchars($resource['usefulness_context'])); ?></div>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-3">관점/편향</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['perspective_bias'] ?? '')); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Analysis -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">분석</h5>
                    <dl class="row">
                        <dt class="col-sm-3">강점</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['strengths'] ?? '')); ?></dd>
                        
                        <dt class="col-sm-3">약점/한계</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['weaknesses_limitations'] ?? '')); ?></dd>
                        
                        <dt class="col-sm-3">FlowBreath 연관성</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['flowbreath_relevance'] ?? '')); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Reflection -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">성찰</h5>
                    <dl class="row">
                        <dt class="col-sm-3">성찰/인사이트</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['reflection_insights'] ?? '')); ?></dd>
                        
                        <dt class="col-sm-3">적용 아이디어</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($resource['application_ideas'] ?? '')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">태그</h5>
                    <?php if (!empty($tags)): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($tag['tag_name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">태그가 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Resources -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">관련 리소스</h5>
                    <?php if (!empty($related_resources)): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($related_resources as $related): ?>
                                <li class="mb-2">
                                    <a href="/resources/show/<?php echo $related['resource_id']; ?>">
                                        <?php echo htmlspecialchars($related['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">관련 리소스가 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">메타데이터</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">등록일</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($resource['date_added'] ?? ''); ?></dd>
                        
                        <dt class="col-sm-4">수정일</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($resource['last_updated'] ?? ''); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-4">
        <a href="/resources" class="btn btn-secondary">목록으로</a>
    </div>
</div> 