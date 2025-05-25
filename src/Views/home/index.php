<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/header.php'; ?>

<style>
.dark-tag-badge {
    display: inline-flex;
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
    margin-bottom: 0.3rem;
}
.dark-tag-badge:hover {
    background: linear-gradient(90deg, #334155 60%, #223046 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 18px rgba(30, 41, 59, 0.18);
    text-decoration: none;
}
</style>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">호흡을 위한 최고의 자료, FlowBreath.io</h1>
    <p class="text-lg mb-8">호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.</p>

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
            <h2 class="text-2xl font-bold">최근 등록된 호흡 자료</h2>
            <a href="/resources" class="text-blue-600 hover:text-blue-800">전체보기</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($recent_resources as $resource): ?>
            <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                <?php
                // Extract YouTube video ID from URL
                $youtubeId = null;
                if (!empty($resource['link'])) {
                    $youtube_pattern = '/(?:youtube\\.com\\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\\.be\/)([^"&?\/\\s]{11})/';
                    if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $youtubeId = $matches[1];
                    }
                }
                
                // Display video if found
                if ($youtubeId): ?>
                    <div class="mb-4">
                        <div class="relative pb-[56.25%] h-0">
                            <iframe 
                                src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0&rel=0" 
                                title="YouTube video player"
                                class="absolute top-0 left-0 w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                <?php endif; ?>
                <h3 class="text-xl font-semibold mb-2">
                    <a href="/resources/<?= $resource['id'] ?>" class="hover:text-blue-600">
                        <?= htmlspecialchars($resource['title']) ?>
                    </a>
                </h3>
                <div class="text-gray-600 mb-4">
                    <?= mb_strimwidth(strip_tags($resource['content']), 0, 150, '...') ?>
                </div>
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span><?= date('Y-m-d', strtotime($resource['created_at'])) ?></span>
                    <span><?= htmlspecialchars($resource['user_name']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 인기 태그 -->
    <div>
        <h2 class="text-2xl font-bold mb-6">인기 태그</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($popular_tags as $tag): ?>
            <a href="/resources?tags[]=<?= $tag['id'] ?>" class="dark-tag-badge">
                #<?= htmlspecialchars($tag['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/src/View/layouts/footer.php'; ?> 