<?php
// YouTube 동영상 ID 추출
$youtubeId = null;
if (!empty($resource['link'])) {
    $youtubeId = extractYoutubeId($resource['link']);
}

// null 값 처리
$title = $resource['title'] ?? '';
$username = $resource['username'] ?? '익명';
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
            <h5 class="card-title"><?= htmlspecialchars($title) ?></h5>

            <p class="card-text">
                <?= formatContent($content, !empty($youtubeId)) ?>
            </p>
            
            <?php if (!empty($resource['tags'])): ?>
                <div class="mb-2">
                    <?php foreach ($resource['tags'] as $tag): ?>
                        <a href="/resources?tags[]=<?= is_array($tag) ? ($tag['id'] ?? '') : '' ?>" class="tag-badge">
                            <i class="fa fa-hashtag"></i>
                            <span><?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="badge bg-primary"><?= htmlspecialchars($type) ?></span>
                <a href="/resources/view/<?= $id ?>" class="btn btn-outline-primary btn-sm">
                    더보기
                </a>
            <p class="resource-meta">
                <?= htmlspecialchars($username) ?> · 
                <?= $createdAt ? date('Y-m-d', strtotime($createdAt)) : '' ?>
            </p>
            </div>
        </div>
    </div>
</div> 