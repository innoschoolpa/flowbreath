<?php
// src/View/home/index.php
// 홈페이지 뷰 파일

if (!function_exists('extractYoutubeIdHome')) {
    function extractYoutubeIdHome($url) {
        $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
        if (preg_match($youtube_pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

// 헤더 뷰 로드
load_view('layout/header', ['title' => $page_title ?? 'FlowBreath.io']);
?>
<style>
.hero-section {
    background: linear-gradient(135deg, #e0f7fa 0%, #fff 100%);
    padding: 60px 0 40px 0;
    text-align: center;
}
.hero-section h1 {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
.hero-section p {
    font-size: 1.25rem;
    color: #555;
    margin-bottom: 2rem;
}
.hero-section .btn-primary {
    font-size: 1.1rem;
    padding: 0.75rem 2.5rem;
    border-radius: 2rem;
}
.recent-resources {
    margin-top: 40px;
}
.card.resource-card {
    transition: box-shadow 0.2s, transform 0.2s;
    border-radius: 1rem;
}
.card.resource-card:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,0.12);
    transform: translateY(-4px) scale(1.02);
}
.card-title {
    font-size: 1.1rem;
    font-weight: 500;
}
.card-body.d-flex.flex-column {
    flex-grow: 1;
    min-height: 0;
}
.card-summary {
    font-size: 0.97rem;
    color: #444;
    line-height: 1.5;
    max-height: 15em;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 10;
    -webkit-box-orient: vertical;
    white-space: pre-line;
    word-break: break-all;
    flex-shrink: 0;
}
@media (max-width: 767px) {
    .hero-section h1 { font-size: 2rem; }
    .recent-resources { margin-top: 20px; }
}
</style>

<div class="hero-section">
    <div class="container">
        <h2>FlowBreath.io에 오신 것을 환영합니다</h2>
        <p>당신의 호흡을 관리하고, 몸과 마음의 건강을 개선하세요.<br>복식호흡, 횡격막호흡, 명상, 자기성장 등 다양한 자료와 경험을 함께 나눕니다.</p>
        <a href="/resources" class="btn btn-primary shadow">리소스 둘러보기</a>
    </div>
</div>

<div class="container recent-resources">
    <?php if (!empty($recentResources)): ?>
    <h2 class="mb-4 text-center fw-bold">최근 자료</h2>
    <div class="row g-4 justify-content-center">
        <?php foreach ($recentResources as $resource): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card resource-card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= htmlspecialchars($resource['profile_image'] ?? '/assets/images/default-avatar.png') ?>" 
                             alt="<?= htmlspecialchars($resource['author_name'] ?? 'Anonymous') ?>" 
                             class="rounded-circle me-2" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <h5 class="card-title mb-0"><?= htmlspecialchars($resource['title']) ?></h5>
                            <small class="text-muted">
                                <?= htmlspecialchars($resource['author_name'] ?? 'Anonymous') ?> · 
                                <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <?php
                    $videoId = null;
                    $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                    
                    // Check link field first
                    if (!empty($resource['link']) && preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $videoId = $matches[1];
                    }
                    
                    // If no video ID found in link, check content
                    if (!$videoId && !empty($resource['content'])) {
                        if (preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                            if (preg_match($youtube_pattern, $ytMatch[0], $matches)) {
                                $videoId = $matches[1];
                            }
                        }
                    }
                    ?>
                    <?php if ($videoId): ?>
                        <div class="ratio ratio-16x9 mb-2" style="max-width:320px; max-height:180px; margin:auto;">
                            <iframe 
                                src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=0" 
                                title="YouTube video player"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                style="width:100%; height:100%; min-height:120px;">
                            </iframe>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <?php if (!$videoId): ?>
                            <div class="card-summary mb-2 flex-grow-1">
                                <?php
                                $content = $resource['content'] ?? '';
                                if (empty($content)) {
                                    $content = $resource['summary'] ?? '';
                                }
                                if (is_html($content)) {
                                    echo $content;
                                } else {
                                    echo markdown_to_html($content);
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <p class="card-text mb-1">
                                <small class="text-muted">
                                    <?php echo date('Y-m-d', strtotime($resource['date_added'])); ?> 등록
                                </small>
                            </p>
                            <a href="/resources/view/<?php echo $resource['resource_id']; ?>" class="btn btn-outline-primary btn-sm">상세보기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php
// 푸터 뷰 로드
load_view('layout/footer');
?>
