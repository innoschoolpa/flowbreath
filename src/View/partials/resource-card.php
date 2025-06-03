<?php
// YouTube 동영상 ID 추출
$youtubeId = null;
if (!empty($resource['link'])) {
    $youtubeId = extractYoutubeId($resource['link']);
}

// null 값 처리
$title = $resource['title'] ?? '';
$authorName = $resource['author_name'] ?? '익명';
$authorImage = $resource['author_image'] ?? '/assets/images/default-avatar.png';
$createdAt = $resource['created_at'] ?? '';
$content = $resource['content'] ?? $resource['description'] ?? '';
$type = $resource['type'] ?? '';
$id = $resource['id'] ?? '';
?>

<div class="col-md-4 mb-4">
    <div class="card card-resource h-100">
        <?php if ($youtubeId): ?>
            <div class="ratio ratio-16x9 mb-3">
                <iframe 
                    src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0&rel=0" 
                    title="YouTube video player"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="width: 100%; height: 100%;">
                </iframe>
            </div>
        <?php endif; ?>
        
        <div class="card-body">
            <h5 class="card-title">
                <a href="/resources/view/<?= $id ?>" class="text-decoration-none"><?= htmlspecialchars($title) ?></a>
            </h5>

            <p class="card-text">
                <?= formatContent($content, !empty($youtubeId)) ?>
            </p>
            
            <?php if (!empty($resource['tags'])): ?>
                <div class="mb-2">
                    <?php foreach ($resource['tags'] as $tag): ?>
                        <a href="/resources?tags[]=<?= htmlspecialchars($tag) ?>" class="tag-badge">
                            <i class="fa fa-hashtag"></i>
                            <span><?= htmlspecialchars($tag) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="badge bg-primary"><?= htmlspecialchars($type) ?></span>
                <div class="resource-meta d-flex align-items-center">
                    <img src="<?= htmlspecialchars($authorImage) ?>" 
                         alt="<?= htmlspecialchars($authorName) ?>의 프로필" 
                         class="rounded-circle me-2"
                         style="width: 24px; height: 24px; object-fit: cover;">
                    <span>
                        <?= htmlspecialchars($authorName) ?> · 
                        <?= $createdAt ? date('Y-m-d', strtotime($createdAt)) : '' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div> 