<?php
// 페이지 제목 설정
$title = $language->get('common.site_name') . ' - ' . $language->get('home.hero.title');

// 공통 레이아웃 포함
require_once __DIR__ . '/layouts/header.php';
?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.hero-section {
    background: linear-gradient(135deg, var(--background-color), #1e3a8a);
    padding: 6rem 0;
    margin-bottom: 4rem;
}

.search-box .form-control {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--border-color);
    color: var(--text-color);
}

.search-box .form-control:focus {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: var(--accent-color);
    color: var(--text-color);
    box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
}

.search-box .form-control::placeholder {
    color: var(--secondary-color);
}

.card-resource {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card-resource:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.card-resource .card-title {
    color: var(--text-color);
}

.card-resource .card-text {
    color: var(--text-color);
    opacity: 0.9;
}

.resource-meta {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.tag-badge {
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, #223046 60%, #334155 100%);
    color: #cbd5e1;
    padding: 0.45rem 1.1rem;
    border-radius: 999px;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(30, 41, 59, 0.08);
    border: 1px solid #334155;
    transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
}

.tag-badge i {
    margin-right: 0.5rem;
    font-size: 0.95em;
}

.tag-badge:hover {
    background: linear-gradient(90deg, #334155 60%, #223046 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 18px rgba(30, 41, 59, 0.18);
    text-decoration: none;
}

.tag-count {
    background: rgba(255,255,255,0.10);
    color: #cbd5e1;
    padding: 0.22rem 0.7rem;
    border-radius: 12px;
    font-size: 0.82em;
    margin-left: 0.6rem;
    font-weight: 400;
}

.btn-warning {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
    color: var(--text-color);
}

.btn-warning:hover {
    background-color: #0284c7;
    border-color: #0284c7;
    color: var(--text-color);
}

.btn-outline-primary {
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: var(--text-color);
}

.btn-link {
    color: var(--accent-color);
    text-decoration: none;
}

.btn-link:hover {
    color: #0284c7;
}

.alert-warning {
    background-color: rgba(234, 179, 8, 0.1);
    border-color: rgba(234, 179, 8, 0.2);
    color: #fbbf24;
}

h1, h2, h3, h4, h5, h6 {
    color: var(--text-color);
}

.text-dark {
    color: var(--text-color) !important;
}

.text-decoration-none {
    text-decoration: none !important;
}

.popular-tags {
    background: linear-gradient(135deg, #1e293b 60%, #0f172a 100%);
    border-radius: 20px;
    padding: 2.5rem 2.2rem 2.2rem 2.2rem;
    border: 1.5px solid #334155;
    box-shadow: 0 4px 24px rgba(59, 130, 246, 0.09);
    margin-top: 3rem;
}

.popular-tags h5 {
    font-size: 1.35rem;
    font-weight: 700;
    margin-bottom: 1.2rem;
    color: #3b82f6;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.popular-tags h5::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 48px;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6 60%, #60a5fa 100%);
    border-radius: 2px;
}

.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1.1rem;
}
</style>

<!-- 히어로 섹션 -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3"><?= $language->get('home.hero.title') ?></h1>
        <p class="lead mb-4"><?= $language->get('home.hero.subtitle') ?></p>
        <form class="search-box" method="get" action="/">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="q" placeholder="<?= $language->get('home.hero.search_placeholder') ?>" value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i> <?= $language->get('common.search') ?></button>
            </div>
        </form>
    </div>
</section>

<div class="container mt-5">
    <?php if ($searchQuery !== ''): ?>
        <h4 class="mb-4">
            '<?= htmlspecialchars($searchQuery) ?>' <?= $language->get('common.search') ?>
        </h4>
        <div class="row">
            <?php if (empty($searchResults)): ?>
                <div class="col-12"><div class="alert alert-warning"><?= $language->get('home.recent_resources.no_results') ?></div></div>
            <?php else: foreach ($searchResults as $resource): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-resource h-100">
                        <?php
                        // Extract YouTube video ID from URL
                        $youtubeId = null;
                        if (!empty($resource['link'])) {
                            $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                            if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                                $youtubeId = $matches[1];
                            }
                        }
                        
                        // Display video if found
                        if ($youtubeId): ?>
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0&rel=0" 
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title']) ?></h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,180,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="/resources/view/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm"><?= $language->get('common.read_more') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= $language->get('home.recent_resources.title') ?></h4>
            <a href="/resources" class="btn btn-link"><?= $language->get('common.view_all') ?> <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row">
            <?php foreach ($recentResources as $resource): ?>
                <div class="col-md-6 col-lg-6 mb-4">
                    <div class="card card-resource h-100">
                        <?php
                        // Extract YouTube video ID from URL
                        $youtubeId = null;
                        if (!empty($resource['link'])) {
                            $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                            if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                                $youtubeId = $matches[1];
                            }
                        }
                        
                        // Display video if found
                        if ($youtubeId): ?>
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0&rel=0" 
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($resource['title']) ?>
                                </a>
                            </h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,280,'...')) ?>
                                </a>
                            </p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="popular-tags">
            <h5><i class="fa fa-fire"></i> <?= $language->get('home.popular_tags.title') ?></h5>
            <div class="tags-container">
                <?php foreach ($popularTags as $tag): ?>
                    <a href="/resources?tags[]=<?= $tag['id'] ?>" class="tag-badge" style="font-size:1.05rem;">
                        <i class="fa fa-hashtag" style="color:#60a5fa; margin-right:0.4rem;"></i>
                        <span style="font-weight:600; color:#e0e7ef;"> <?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?> </span>
                        <?php if (isset($tag['count'])): ?>
                            <span class="tag-count" style="background:rgba(59,130,246,0.13); color:#60a5fa; margin-left:0.7rem;"> <?= $tag['count'] ?> </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 