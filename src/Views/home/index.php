<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/header.php'; ?>

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

.text-gray-600 {
    color: var(--text-color) !important;
    opacity: 0.8;
}

.text-gray-500 {
    color: var(--text-color) !important;
    opacity: 0.6;
}

.card {
    background: var(--card-bg);
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(30,64,175,0.10);
    border: none;
}

.card-title a {
    color: #60a5fa;
    text-decoration: none;
}

.card-title a:hover {
    color: #2563eb;
    text-decoration: underline;
}

.card-text {
    color: #cbd5e1;
}

.dark-tag-badge {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(90deg, #1e40af 60%, #3b82f6 100%);
    color: #e2e8f0;
    padding: 0.35em 1em;
    border-radius: 999px;
    font-size: 0.95em;
    font-weight: 500;
    margin: 0.1em 0.2em 0.1em 0;
    border: 1px solid #3b82f6;
    transition: background 0.2s, color 0.2s;
}

.dark-tag-badge:hover {
    background: linear-gradient(90deg, #2563eb 60%, #1d4ed8 100%);
    color: #fff;
    text-decoration: none;
}

input[type="text"] {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

input[type="text"]:focus {
    background-color: rgba(255, 255, 255, 0.15) !important;
    border-color: var(--accent-color) !important;
    box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.25) !important;
}

input[type="text"]::placeholder {
    color: var(--text-color) !important;
    opacity: 0.5;
}

@media (max-width: 991px) {
    .col-lg-4, .col-lg-6 { flex: 0 0 100%; max-width: 100%; }
}
</style>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8" style="color:#fff;">호흡을 위한 최고의 자료, FlowBreath.io</h1>
    <p class="text-lg mb-8" style="color:#cbd5e1;">호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.</p>

    <!-- 검색 폼 -->
    <div class="mb-12">
        <form action="/search" method="GET" class="flex gap-2">
            <input type="text" name="q" placeholder="검색어를 입력하세요" class="flex-1 px-4 py-2 border rounded-lg">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">검색</button>
        </form>
    </div>

    <!-- 최근 등록된 호흡 자료 -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold" style="color:#fff;">최근 등록된 호흡 자료</h2>
            <a href="/resources" class="text-blue-600 hover:text-blue-800">전체보기</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($recent_resources as $resource): ?>
            <?php
            $videoId = null;
            $hasYoutubeLink = false;
            
            // Check for YouTube link in link field
            if (!empty($resource['link'])) {
                $youtube_pattern = '/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=|live\\/)|youtu\\.be\\/)([^"&?\\/\\s]{11})/';
                if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                    $videoId = $matches[1];
                    $hasYoutubeLink = true;
                }
            }
            
            // Check for YouTube link in content if not found in link field
            if (!$hasYoutubeLink && !empty($resource['content'])) {
                if (preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                    if (preg_match($youtube_pattern, $ytMatch[0], $matches)) {
                        $videoId = $matches[1];
                        $hasYoutubeLink = true;
                    }
                }
            }
            
            // Determine content length based on YouTube link presence
            $contentLength = $hasYoutubeLink ? 130 : 500;
            
            // Prepare content with only line breaks preserved
            $content = strip_tags($resource['content'] ?? '');
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $content = mb_strimwidth($content, 0, $contentLength, '...');
            $content = nl2br(htmlspecialchars($content));
            ?>
            <div class="card h-100 shadow-lg border-0">
                <?php if ($videoId): ?>
                    <div class="ratio ratio-16x9">
                        <iframe 
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>?autoplay=0&rel=0" 
                            title="YouTube video player"
                            class="rounded-top"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2">
                        <a href="/resources/<?= $resource['id'] ?>">
                            <?= htmlspecialchars($resource['title']) ?>
                        </a>
                    </h5>
                    <p class="card-text mb-2">
                        <?= $content ?>
                    </p>
                    <div class="mt-auto d-flex justify-content-between align-items-center" style="color:#94a3b8; font-size:0.95em;">
                        <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($resource['user_name']) ?></span>
                        <span><i class="fas fa-calendar me-1"></i><?= date('Y-m-d', strtotime($resource['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 인기 태그 -->
    <div class="mt-12 bg-gradient-to-br from-[#1a1f2e] to-[#0f172a] rounded-2xl p-8 border border-[#334155] shadow-lg">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" class="inline-block text-[#3b82f6]"><circle cx="12" cy="12" r="10" fill="currentColor"/></svg>
            <span class="text-[#3b82f6]">인기 태그</span>
        </h2>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($popular_tags as $tag): ?>
            <a href="/resources?tags[]=<?= $tag['id'] ?>" class="dark-tag-badge">
                #<?= htmlspecialchars($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 호흡 기법 설명 -->
<div class="container mx-auto px-4 py-8">
    <div class="card p-6">
        <h2 class="text-2xl font-bold mb-4">호흡 기법의 이해</h2>
        <p class="mb-4">
            1. 공통점 복식 호흡과 단전 호흡은 기술적 기반과 생리적·정신적 효과에서 여러 공통점을 공유합니다. 호흡 방식 둘 다 횡격막을 적극적으로 사용해 하복부를 확장하며 깊고 느린 호흡을 수행합니다. 코로 천천히 흡입(4-5초)해 배를 부풀리고, 코나 입으로 천천히 호기(4-...
        </p>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/footer.php'; ?> 