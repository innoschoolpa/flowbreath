<?php
// 페이지 제목 설정
$title = $language->get('common.site_name') . ' - ' . $language->get('home.hero.title');

// DB 연결 관리
try {
    $db = \App\Core\Database::getInstance();
    $pdo = $db->getConnection();

    // 최근 리소스
    $resourceModel = new \App\Models\Resource();
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
    $recentResources = $resourceModel->getRecentPublic(6, $lang);

    // 검색 처리
    $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
    $searchResults = [];
    if ($searchQuery !== '') {
        try {
            $searchResults = $resourceModel->searchResources($searchQuery, 10, 0, $lang);
        } catch (Exception $e) {
            error_log("Search error: " . $e->getMessage());
            $searchResults = [];
        }
    }
} catch (\Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // 에러 발생 시 빈 결과 반환
    $recentResources = [];
    $searchResults = [];
    $searchQuery = '';
}

// 로그인 상태
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'profile_image' => $_SESSION['user_avatar'] ?? null,
    'bio' => $_SESSION['user_bio'] ?? '',
    'social_links' => $_SESSION['user_social_links'] ?? ''
] : null;

// 공통 레이아웃 포함
require_once __DIR__ . '/layouts/header.php';

function formatContent($content, $hasYoutubeLink) {
    if (empty($content)) {
        return '';
    }

    // First decode HTML entities
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remove all HTML attributes
    $content = preg_replace('/<([a-z][a-z0-9]*)[^>]*>/i', '<$1>', $content);
    
    // Remove HTML tags while preserving line breaks
    $content = strip_tags($content, '<br><p>');
    
    // Convert <br> and <p> tags to newlines
    $content = str_replace(['<br>', '<br/>', '<br />', '</p><p>'], "\n", $content);
    $content = str_replace(['<p>', '</p>'], '', $content);
    
    // Remove multiple consecutive newlines and trim whitespace
    $content = preg_replace('/\n\s*\n+/', "\n", $content);
    $content = trim($content);
    
    // Trim content to specified length
    if (mb_strlen($content, 'UTF-8') > $contentLength) {
        $content = mb_substr($content, 0, $contentLength, 'UTF-8');
        // Find the last space before the cutoff
        $lastSpace = mb_strrpos($content, ' ', 0, 'UTF-8');
        if ($lastSpace !== false) {
            $content = mb_substr($content, 0, $lastSpace, 'UTF-8');
        }
        $content .= '...';
    }
    
    // Convert newlines to <br> tags and escape HTML
    return nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
}

// YouTube 동영상 ID 추출 함수
function extractYoutubeId($url) {
    $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
    if (preg_match($youtube_pattern, $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// YouTube Shorts 확인 함수
function isYoutubeShorts($url) {
    return strpos($url, '/shorts/') !== false;
}

class HomeController {
    public function index() {
        // ... existing code ...
        // ... existing code ...
        // ... existing code ...
    }
}

$language = $language ?? \App\Core\Language::getInstance();
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
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    line-height: 1.6;
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

.card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.card .card-title {
    color: var(--text-color);
}

.card .card-title a {
    color: var(--text-color);
    text-decoration: none;
}

.card .card-title a:hover {
    color: var(--accent-color);
}

.card .card-text {
    color: var(--text-color);
    opacity: 0.9;
}

.card .text-muted {
    color: var(--secondary-color) !important;
}

.resource-meta {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.tag-badge {
    display: inline-block;
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

.tag-badge:hover {
    background: linear-gradient(90deg, #2563eb 60%, #1d4ed8 100%);
    color: #fff;
}

.tag-badge i {
    margin-right: 0.5rem;
    font-size: 0.95em;
    color: #93c5fd;
}

.tag-count {
    background: rgba(37, 99, 235, 0.15);
    color: #93c5fd;
    padding: 0.22rem 0.7rem;
    border-radius: 12px;
    font-size: 0.82em;
    margin-left: 0.7rem;
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
    padding: 2.5rem 2.2rem;
    border: 1.5px solid #334155;
    box-shadow: 0 4px 24px rgba(59, 130, 246, 0.09);
    margin-top: 3rem;
}

.popular-tags h5 {
    font-size: 1.35rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #3b82f6;
    letter-spacing: 0.02em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.popular-tags h5 i {
    color: #60a5fa;
}

.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}
</style>

<!-- Hero Section -->
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
                <?php include __DIR__ . '/partials/resource-card.php'; ?>
            <?php endforeach; endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= $language->get('home.recent_resources.title') ?></h4>
            <a href="/resources" class="btn btn-link"><?= $language->get('common.view_all') ?> <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($recentResources as $resource): ?>
                <?php
                $videoId = null;
                $hasYoutubeLink = false;
                $isShorts = false;
                $youtube_pattern = '/(?:youtube\.com\/(?:shorts\/|(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/))|youtu\.be\/)([^"&?\/\s]{11})/';
                
                // Check for YouTube link in link field
                if (!empty($resource['link'])) {
                    if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $videoId = $matches[1];
                        $hasYoutubeLink = true;
                        $isShorts = strpos($resource['link'], '/shorts/') !== false;
                    }
                }
                
                // Check for YouTube link in content if not found in link field
                if (!$hasYoutubeLink && !empty($resource['content'])) {
                    if (preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                        if (preg_match($youtube_pattern, $ytMatch[0], $matches)) {
                            $videoId = $matches[1];
                            $hasYoutubeLink = true;
                            $isShorts = strpos($ytMatch[0], '/shorts/') !== false;
                        }
                    }
                }
                
                // Determine content length based on YouTube link presence
                $contentLength = $hasYoutubeLink ? 130 : 350; // Longer content for non-video resources
                
                // Prepare content with only line breaks preserved
                $content = strip_tags($resource['content'] ?? '');
                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                // Remove multiple consecutive newlines and normalize line breaks
                $content = preg_replace('/\r\n|\r|\n/', "\n", $content); // Normalize line endings
                $content = preg_replace('/\n\s*\n+/', "\n", $content); // Remove multiple consecutive newlines
                $content = trim($content); // Remove leading/trailing whitespace
                
                // Trim content to specified length
                if (mb_strlen($content, 'UTF-8') > $contentLength) {
                    $content = mb_substr($content, 0, $contentLength, 'UTF-8');
                    // Find the last space before the cutoff
                    $lastSpace = mb_strrpos($content, ' ', 0, 'UTF-8');
                    if ($lastSpace !== false) {
                        $content = mb_substr($content, 0, $lastSpace, 'UTF-8');
                    }
                    $content .= '...';
                }
                
                $content = nl2br(htmlspecialchars($content));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-lg border-0">
                        <?php if ($videoId): ?>
                            <div class="ratio <?= $isShorts ? 'ratio-9x16' : 'ratio-16x9' ?>">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=0<?= $isShorts ? '&controls=1&modestbranding=1&rel=0&showinfo=0' : '' ?>" 
                                    class="rounded-top"
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    style="<?= $isShorts ? 'max-width: 180px; max-height: 320px; margin: auto;' : '' ?>">
                                </iframe>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>">
                                    <?= htmlspecialchars($resource['title'] ?? '') ?>
                                </a>
                            </h5>
                            <p class="card-text mb-2">
                                <?= $content ?>
                            </p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <?php $tagName = is_array($tag) ? $tag['name'] : $tag; ?>
                                    <a href="/resources/tag/<?= urlencode($tagName) ?>" class="tag-badge">#<?= htmlspecialchars($tagName) ?></a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-center" style="color:#94a3b8; font-size:0.95em;">
                                <div class="d-flex align-items-center">
                                    <div class="profile-image-container me-2" style="width: 24px; height: 24px; border-radius: 50%; overflow: hidden; background-color: var(--card-bg);">
                                        <img src="<?= $resource['profile_image'] ?? '/assets/images/default-avatar.png' ?>" 
                                             alt="<?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?>의 프로필" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <span class="author-name"><?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?></span>
                                </div>
                                <span class="date-info"><i class="fas fa-calendar me-1"></i><?= htmlspecialchars(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?></span>
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
                    <a href="/resources/tag/<?= urlencode($tag['name']) ?>" class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 
