<?php $language = $language ?? \App\Core\Language::getInstance(); ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold mb-4">호흡을 위한 최고의 자료, FlowBreath.io</h1>
        <p class="text-gray-600 mb-8">호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.</p>
        
        <!-- Search Form -->
        <div class="container mx-auto mb-6">
            <div class="flex items-center justify-between gap-2 w-full">
                <form action="/resources" method="GET" class="flex flex-1 gap-2 items-center">
                    <input type="text" name="keyword" placeholder="자료, 태그, 키워드로 검색..."
                        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    <button type="submit" class="px-6 py-2 bg-yellow-400 text-gray-900 font-bold rounded-lg shadow hover:bg-yellow-500 transition-colors border border-yellow-500"
                        style="height:44px; min-width:90px; font-size:1.1rem;">
                        <i class="fas fa-search mr-1"></i>검색
                    </button>
                </form>
                <?php if (isset($user) && $user['id']): ?>
                    <a href="/resources/create"
                        class="px-6 py-2 bg-yellow-300 text-black rounded-lg hover:bg-yellow-400 transition-colors ml-4"
                        style="height:44px; min-width:110px; font-size:1.1rem; display:flex; align-items:center; justify-content:center; color:#222;">
                        <i class="fas fa-plus me-1"></i>자료 등록
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Error/Loading/No Result -->
    <?php if (isset($error) && $error && $error !== 'unset'): ?>
        <div class="flex justify-center items-center my-8">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        </div>
    <?php elseif (empty($resources)): ?>
        <div class="flex justify-center items-center my-8">
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded flex items-center">
                <i class="fas fa-search mr-2"></i>
                <?= $language->get('resources.no_results') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="hidden text-center my-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
    </div>

    <!-- Resource Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($resources as $resource): ?>
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-4 flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-2xl">
                <?php
                $videoId = null;
                if (!empty($resource['link'])) {
                    $youtube_pattern = '/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=|live\\/)|youtu\\.be\\/)([^"&?\\/\\s]{11})/';
                    if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $videoId = $matches[1];
                    }
                    if (!$videoId && !empty($resource['content'])) {
                        if (preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                            if (preg_match($youtube_pattern, $ytMatch[0], $matches)) {
                                $videoId = $matches[1];
                            }
                        }
                    }
                }
                ?>
                <?php if ($videoId): ?>
                    <div class="flex flex-row gap-4 items-start">
                        <div class="flex-shrink-0 w-64 max-w-full">
                            <div class="aspect-w-16 aspect-h-9 mb-0">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=0" 
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    class="w-full h-full rounded-lg">
                                </iframe>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="mb-2" style="font-size:1rem; font-weight:600;">
                                <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>" 
                                   class="text-gray-900 hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($resource['title'] ?? '') ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-2">
                                <?php
                                $preview = '';
                                if (!empty($resource['content'])) {
                                    $plain = strip_tags($resource['content']);
                                    $preview = mb_strimwidth($plain, 0, 200, '...');
                                }
                                echo htmlspecialchars($preview ?? '');
                                ?>
                            </p>
                            <?php if (!empty($resource['tags'])): ?>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="inline-block px-2 py-1 text-sm text-gray-600 bg-gray-100 rounded-full mr-1 mb-1">
                                        #<?= htmlspecialchars(is_array($tag) ? $tag['name'] : $tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="text-sm text-gray-500 mt-2 border-t pt-3 flex flex-wrap gap-4">
                                <span>
                                    <i class="fas fa-user mr-1"></i>
                                    <?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= htmlspecialchars(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?>
                                </span>
                                <?php if (isset($resource['view_count'])): ?>
                                    <span>
                                        <i class="fas fa-eye mr-1"></i>
                                        <?= htmlspecialchars($resource['view_count']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (isset($resource['rating'])): ?>
                                    <span>
                                        <i class="fas fa-star mr-1"></i>
                                        <?= number_format($resource['rating'], 1) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <h3 class="mb-2" style="font-size:1rem; font-weight:600;">
                        <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>" 
                           class="text-gray-900 hover:text-blue-600 transition-colors">
                            <?= htmlspecialchars($resource['title'] ?? '') ?>
                        </a>
                    </h3>
                    <p class="text-gray-600 mb-2">
                        <?php
                        $preview = '';
                        if (!empty($resource['content'])) {
                            $plain = strip_tags($resource['content']);
                            $preview = mb_strimwidth($plain, 0, 300, '...');
                        }
                        echo htmlspecialchars($preview ?? '');
                        ?>
                    </p>
                    <?php if (!empty($resource['tags'])): ?>
                    <div class="mb-2">
                        <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                            <span class="inline-block px-2 py-1 text-sm text-gray-600 bg-gray-100 rounded-full mr-1 mb-1">
                                #<?= htmlspecialchars(is_array($tag) ? $tag['name'] : $tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-sm text-gray-500 mt-2 border-t pt-3 flex flex-wrap gap-4">
                        <span>
                            <i class="fas fa-user mr-1"></i>
                            <?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar mr-1"></i>
                            <?= htmlspecialchars(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?>
                        </span>
                        <?php if (isset($resource['view_count'])): ?>
                            <span>
                                <i class="fas fa-eye mr-1"></i>
                                <?= htmlspecialchars($resource['view_count']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (isset($resource['rating'])): ?>
                            <span>
                                <i class="fas fa-star mr-1"></i>
                                <?= number_format($resource['rating'], 1) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="flex justify-center items-center mt-8 gap-2">
            <?php if (($current_page ?? 1) > 1): ?>
                <a href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                   class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 transition-colors">
                    <?= $language->get('common.prev') ?>
                </a>
            <?php endif; ?>
            
            <span class="px-4 py-2 bg-gray-100 rounded-lg">
                <?= ($current_page ?? 1) ?> / <?= $total_pages ?>
            </span>
            
            <?php if (($current_page ?? 1) < $total_pages): ?>
                <a href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                   class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 transition-colors">
                    <?= $language->get('common.next') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[action="/resources"]');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    searchForm.addEventListener('submit', function() {
        loadingSpinner.classList.remove('hidden');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 