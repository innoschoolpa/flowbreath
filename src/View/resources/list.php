<?php $language = $language ?? \App\Core\Language::getInstance(); ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.text-gray-600 {
    color: var(--text-color) !important;
    opacity: 0.8;
}

.text-gray-900 {
    color: var(--text-color) !important;
}

.text-gray-500 {
    color: var(--text-color) !important;
    opacity: 0.6;
}

.bg-white {
    background-color: var(--card-bg) !important;
}

.border-gray-200 {
    border-color: var(--border-color) !important;
}

.bg-gray-100 {
    background-color: rgba(255, 255, 255, 0.1) !important;
}

.bg-yellow-100 {
    background-color: rgba(245, 158, 11, 0.1) !important;
}

.bg-red-100 {
    background-color: rgba(239, 68, 68, 0.1) !important;
}

.border-yellow-400 {
    border-color: rgba(245, 158, 11, 0.3) !important;
}

.border-red-400 {
    border-color: rgba(239, 68, 68, 0.3) !important;
}

.text-yellow-700 {
    color: var(--warning-color) !important;
}

.text-red-700 {
    color: var(--error-color) !important;
}

.hover\:bg-gray-50:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
}

.bg-yellow-300 {
    background-color: var(--warning-color) !important;
}

.bg-yellow-400 {
    background-color: var(--warning-color) !important;
}

.bg-yellow-500 {
    background-color: #d97706 !important;
}

.text-black {
    color: var(--text-color) !important;
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

.border {
    border-color: var(--border-color) !important;
}

.border-t {
    border-top-color: var(--border-color) !important;
}

.hover\:text-blue-600:hover {
    color: var(--accent-color) !important;
}

.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2) !important;
}

.hover\:shadow-2xl:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
}

.hero-section {
  background: linear-gradient(135deg, #1e293b 60%, #2563eb 100%);
  border-radius: 0 0 32px 32px;
  box-shadow: 0 8px 32px rgba(37,99,235,0.08);
  margin-bottom: 2.5rem;
}
.search-box input {
  background: rgba(255,255,255,0.08);
  color: #fff;
  border: 1px solid #334155;
}
.search-box input::placeholder {
  color: #94a3b8;
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
.card {
  background: #1e293b;
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
@media (max-width: 991px) {
  .col-lg-4, .col-lg-6 { flex: 0 0 100%; max-width: 100%; }
}
@media (max-width: 767px) {
  .hero-section { padding: 2rem 0 1rem 0; }
  .card { min-height: 320px; }
  .search-box { padding: 1rem 0.5rem; }
}

.dropdown-menu {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.dropdown-item {
    color: var(--text-color) !important;
    background-color: var(--card-bg) !important;
}

.dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: var(--text-color) !important;
}

.dropdown-item.active {
    background-color: var(--primary-color) !important;
    color: white !important;
}

/* Add styles for form-select */
.form-select {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
    border-color: var(--border-color) !important;
}

.form-select option {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

img.rounded-circle { font-size: 0; color: transparent; object-fit: cover; width: 40px; height: 40px; }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3"><?= $language->get('home.hero.title') ?></h1>
        <p class="lead mb-4"><?= $language->get('home.hero.subtitle') ?></p>
        <form class="search-box" method="get" action="/resources">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="q" placeholder="<?= $language->get('home.hero.search_placeholder') ?>" value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i> <?= $language->get('common.search') ?></button>
            </div>
        </form>
    </div>
</section>

<!-- Add Resource Button (centered) -->
<?php if (isset($user) && $user): ?>
  <div class="d-flex justify-content-center mb-4">
    <a href="/resources/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> <?= $language->get('resources.create') ?>
    </a>
  </div>
<?php endif; ?>

<div class="container">
  <!-- Error/Loading/No Result -->
  <?php if (isset($error) && $error && $error !== 'unset'): ?>
    <div class="d-flex justify-content-center align-items-center my-4">
      <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
    </div>
  <?php elseif (empty($resources)): ?>
    <div class="d-flex justify-content-center align-items-center my-4">
      <div class="alert alert-warning d-flex align-items-center"><i class="fas fa-search me-2"></i><?= $language->get('resources.no_results') ?></div>
    </div>
  <?php endif; ?>

  <!-- Resource Cards -->
  <div class="row g-4">
    <?php foreach ($resources as $resource): ?>
      <?php
      $videoId = null;
      $hasYoutubeLink = false;
      $youtube_pattern = '/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=|live\\/)|youtu\\.be\\/)([^"&?\\/\\s]{11})/';
      
      // Check for YouTube link in link field
      if (!empty($resource['link'])) {
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
      $contentLength = $hasYoutubeLink ? 130 : 400; // Longer content for non-video resources
      
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
            <div class="ratio ratio-16x9">
              <iframe src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=0" class="rounded-top" allowfullscreen></iframe>
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
            <div class="mt-auto">
              <div class="d-flex align-items-center mb-2" style="min-height: 40px;">
                <img src="<?= !empty($resource['profile_image']) ? htmlspecialchars($resource['profile_image']) : '/assets/images/default-avatar.png' ?>"
                     class="rounded-circle me-2" width="40" height="40" alt="프로필">
                <div>
                  <div class="fw-bold" style="font-size:1.1rem;"><?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?></div>
                  <div class="text-muted small" style="font-size:0.95rem; margin-top:0.3rem;">
                    <?= htmlspecialchars(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if (($total_pages ?? 1) > 1): ?>
    <div class="d-flex justify-content-center align-items-center mt-5 gap-2">
      <?php if (($current_page ?? 1) > 1): ?>
        <a href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" class="btn btn-outline-primary px-4 py-2">
          <?= $language->get('common.prev') ?>
        </a>
      <?php endif; ?>
      <span class="px-4 py-2 bg-dark text-white rounded">
        <?= ($current_page ?? 1) ?> / <?= $total_pages ?>
      </span>
      <?php if (($current_page ?? 1) < $total_pages): ?>
        <a href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" class="btn btn-outline-primary px-4 py-2">
          <?= $language->get('common.next') ?>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<div id="editor" class="editor-container"></div>

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