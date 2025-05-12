<?php $language = $language ?? \App\Core\Language::getInstance(); ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php if (isset(
    $error) && $error) { dd($error); } ?>
<style>
body { background: #f7fcfc; }
.resource-search-box {
  background: #fff;
  border-radius: 1.2rem;
  box-shadow: 0 2px 16px #0001;
  padding: 2.2rem 2rem 1.2rem 2rem;
  margin-bottom: 2.5rem;
  max-width: 1100px;
  margin-left: auto;
  margin-right: auto;
}
.resource-search-form {
  display: flex;
  flex-wrap: nowrap;
  gap: 0.7rem;
  align-items: center;
}
.resource-search-form .form-group {
  flex: 1 1 220px;
  min-width: 180px;
}
.resource-search-form .form-group.tags {
  min-width: 120px;
  max-width: 180px;
  flex: 0 1 180px;
}
.resource-search-form .form-group.keyword {
  flex: 1 1 220px;
  min-width: 180px;
  max-width: 320px;
}
.resource-search-form .form-group.sort,
.resource-search-form .form-group.type,
.resource-search-form .form-group.is_public {
  min-width: 120px;
  flex: 0 1 120px;
}
.resource-search-form .form-group.button {
  min-width: 110px;
  flex: 0 1 110px;
}
.resource-search-form .btn {
  width: 100%;
  font-weight: 600;
  padding: 0.6em 0;
}
.resource-card {
  border: none;
  border-radius: 1.2rem;
  box-shadow: 0 2px 16px #007bff11;
  transition: box-shadow 0.2s, transform 0.2s;
  background: #fff;
  margin-bottom: 0.75rem;
  padding: 0.6rem 0.65rem 0.55rem 0.65rem;
  min-height: 100px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.resource-card:hover {
  box-shadow: 0 6px 32px #007bff33;
  transform: translateY(-4px) scale(1.025);
}
.resource-card .card-title {
  font-weight: 700;
  font-size: 1.18rem;
  margin-bottom: 0.5rem;
  color: #222;
}
.resource-card .badge {
  font-size: 0.85em;
  margin-bottom: 0.2em;
}
.resource-card .card-text {
  min-height: 2.5em;
  color: #444;
  margin-bottom: 0;
}
.resource-meta {
  color: #888;
  font-size: 0.97em;
  margin-bottom: 0;
  margin-top: 0;
}
.resource-type {
  font-size: 0.92em;
  color: #fff;
  background: #6c63ff;
  border-radius: 0.5em;
  padding: 0.2em 0.7em;
  margin-right: 0.5em;
  display: inline-block;
}
#loading-spinner { min-height: 80px; }
@media (max-width: 900px) {
  .resource-search-form { flex-direction: column; gap: 0.7rem; flex-wrap: wrap; }
  .resource-search-form .form-group { min-width: 0; max-width: 100%; }
  .form-group.button { flex-direction: row !important; justify-content: flex-start !important; gap: 0.7rem !important; }
  .form-group.button .btn { width: 100%; min-width: 0; }
}
</style>
<div class="container py-4" style="max-width:1200px;">
  <div class="resource-search-box mb-4">
    <form action="/resources" method="GET" class="resource-search-form">
      <div class="form-group keyword">
        <input type="text" name="keyword" class="form-control" placeholder="<?= $language->get('common.search') ?>" value="<?= htmlspecialchars($keyword ?? '') ?>">
      </div>
      <div class="form-group tags">
        <select name="tags[]" class="form-select" multiple data-placeholder="태그 선택">
          <?php foreach ($all_tags as $tag): ?>
            <option value="<?= htmlspecialchars($tag['id']) ?>" <?= in_array($tag['id'], $selected_tags ?? []) ? 'selected' : '' ?>><?= htmlspecialchars($tag['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group sort">
        <select name="sort" class="form-select">
          <option value="latest" <?= ($sort ?? '') === 'latest' ? 'selected' : '' ?>><?= $language->get('resources.sort.latest') ?></option>
          <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>><?= $language->get('resources.sort.oldest') ?></option>
          <option value="title" <?= ($sort ?? '') === 'title' ? 'selected' : '' ?>><?= $language->get('resources.sort.title') ?></option>
          <option value="views" <?= ($sort ?? '') === 'views' ? 'selected' : '' ?>><?= $language->get('resources.sort.views') ?></option>
          <option value="rating" <?= ($sort ?? '') === 'rating' ? 'selected' : '' ?>><?= $language->get('resources.sort.rating') ?></option>
          <option value="relevance" <?= ($sort ?? '') === 'relevance' ? 'selected' : '' ?>><?= $language->get('resources.sort.relevance') ?></option>
        </select>
      </div>
      <div class="form-group type">
        <select name="type" class="form-select">
          <option value=""><?= $language->get('resources.type.all') ?></option>
          <?php foreach ($types as $key => $label): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= ($type ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (isset($user) && $user['is_admin']): ?>
      <div class="form-group is_public">
        <select name="is_public" class="form-select">
          <option value=""><?= $language->get('resources.visibility.all') ?></option>
          <option value="1" <?= ($is_public ?? '') === '1' ? 'selected' : '' ?>><?= $language->get('resources.visibility.public') ?></option>
          <option value="0" <?= ($is_public ?? '') === '0' ? 'selected' : '' ?>><?= $language->get('resources.visibility.private') ?></option>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group button d-flex flex-row align-items-center gap-2" style="min-width:180px;">
        <button type="submit" class="btn btn-primary px-3 rounded-3" style="height:40px;min-width:90px;font-size:1rem;font-weight:500;">
          <?= $language->get('common.search') ?>
        </button>
        <?php if (isset($user) && $user['id']): ?>
          <a href="/resources/create" class="btn btn-success px-3 rounded-3 d-flex align-items-center justify-content-center" style="height:40px;min-width:110px;font-size:1rem;font-weight:500;white-space:nowrap;">
            <i class="fas fa-plus me-1"></i><?= $language->get('resources.create') ?>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Error/Loading/No Result -->
  <?php if (isset($error) && $error && $error !== 'unset'): ?>
    <div class="d-flex justify-content-center align-items-center my-5">
      <div class="alert alert-danger d-flex align-items-center gap-2" style="font-size:1.1em;">
        <i class="fas fa-exclamation-triangle fa-lg me-2"></i> <?= htmlspecialchars($error) ?>
      </div>
    </div>
  <?php elseif (empty($resources)): ?>
    <div class="d-flex justify-content-center align-items-center my-5">
      <div class="alert alert-warning d-flex align-items-center gap-2" style="font-size:1.1em;">
        <i class="fas fa-search fa-lg me-2"></i> <?= $language->get('resources.no_results') ?>
      </div>
    </div>
  <?php endif; ?>

  <div id="loading-spinner" class="text-center my-4 d-none">
    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <!-- Resource Cards -->
  <div class="row row-cols-1 g-4">
    <?php foreach ($resources as $resource): ?>
      <div class="col">
        <div class="resource-card h-100">
          <div>
            <div class="mb-2">
              <?php if (!empty($resource['type'])): ?>
                <span class="resource-type"><?= htmlspecialchars($types[$resource['type']] ?? $resource['type']) ?></span>
              <?php endif; ?>
              <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                <span class="badge bg-secondary me-1 mb-1">#<?= htmlspecialchars(is_array($tag) ? $tag['name'] : $tag) ?></span>
              <?php endforeach; ?>
            </div>
            <h5 class="card-title mb-2">
                <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>" class="text-decoration-none text-dark">
                    <?= htmlspecialchars($resource['title']) ?>
                </a>
            </h5>
            <?php if (!empty($resource['link'])): ?>
                <?php
                $videoId = null;
                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $resource['link'], $matches)) {
                    $videoId = $matches[1];
                }
                if ($videoId): ?>
                    <div class="ratio ratio-16x9 mb-2" style="max-width:320px; max-height:180px; margin:auto;">
                        <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" 
                                title="YouTube video" 
                                allowfullscreen
                                style="width:100%; height:100%; min-height:120px;"></iframe>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <p class="card-text flex-grow-1 mb-2">
                <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>" class="text-decoration-none text-body">
                    <?php
                    $preview = '';
                    if (!empty($resource['content'])) {
                        $plain = strip_tags($resource['content']);
                        $preview = mb_strimwidth($plain, 0, 120, '...');
                    }
                    echo htmlspecialchars($preview);
                    ?>
                </a>
            </p>
          </div>
          <div class="resource-meta mb-1 mt-1">
            <span><?= $language->get('resources.author') ?>: <?= htmlspecialchars($resource['author_name'] ?? $language->get('common.anonymous')) ?></span>
            <span class="mx-2">|</span>
            <span><?= $language->get('resources.date') ?>: <?= htmlspecialchars(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?></span>
            <?php if (isset($resource['view_count'])): ?>
              <span class="mx-2">|</span>
              <span><?= $language->get('resources.views') ?>: <?= htmlspecialchars($resource['view_count']) ?></span>
            <?php endif; ?>
            <?php if (isset($resource['rating'])): ?>
              <span class="mx-2">|</span>
              <span><?= $language->get('resources.rating') ?>: <?= number_format($resource['rating'], 1) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if (($total_pages ?? 1) > 1): ?>
    <nav aria-label="Page navigation" class="mt-5">
      <ul class="pagination justify-content-center">
        <?php if (($current_page ?? 1) > 1): ?>
          <li class="page-item">
            <a class="page-link" href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>"><?= $language->get('common.prev') ?></a>
          </li>
        <?php endif; ?>
        <li class="page-item disabled">
          <span class="page-link" style="background:#f8f9fa; color:#333; border:none; min-width:70px; text-align:center;"> <?= ($current_page ?? 1) ?> / <?= $total_pages ?> </span>
        </li>
        <?php if (($current_page ?? 1) < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>"><?= $language->get('common.next') ?></a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('select[name="tags[]"]').select2({ placeholder: "<?= $language->get('resources.tags_placeholder') ?>", allowClear: true });
  document.querySelector('.search-form').addEventListener('submit', function() {
    document.getElementById('loading-spinner').classList.remove('d-none');
  });
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 