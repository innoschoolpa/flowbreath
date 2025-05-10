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
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
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
  flex: 2 1 400px;
  min-width: 300px;
  max-width: 600px;
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
  .resource-search-form { flex-direction: column; gap: 0.7rem; }
  .resource-search-form .form-group { min-width: 0; max-width: 100%; }
}
</style>
<div class="container py-4" style="max-width:1200px;">
  <div class="resource-search-box mb-4">
    <form action="/resources" method="GET" class="resource-search-form">
      <div class="form-group keyword">
        <input type="text" name="keyword" class="form-control" placeholder="검색어를 입력하세요 (제목, 내용)" value="<?= e($keyword ?? '') ?>">
      </div>
      <div class="form-group tags">
        <select name="tags[]" class="form-select" multiple data-placeholder="태그 선택">
          <?php foreach ($all_tags as $tag): ?>
            <option value="<?= e($tag['id']) ?>" <?= in_array($tag['id'], $selected_tags ?? []) ? 'selected' : '' ?>><?= e($tag['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group sort">
        <select name="sort" class="form-select">
          <option value="created_desc" <?= ($sort ?? '') === 'created_desc' ? 'selected' : '' ?>>최신순</option>
          <option value="created_asc" <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>>오래된순</option>
          <option value="title_asc" <?= ($sort ?? '') === 'title_asc' ? 'selected' : '' ?>>제목순</option>
          <option value="views_desc" <?= ($sort ?? '') === 'views_desc' ? 'selected' : '' ?>>조회수순</option>
          <option value="rating_desc" <?= ($sort ?? '') === 'rating_desc' ? 'selected' : '' ?>>평점순</option>
          <option value="relevance" <?= ($sort ?? '') === 'relevance' ? 'selected' : '' ?>>관련도순</option>
        </select>
      </div>
      <div class="form-group type">
        <select name="type" class="form-select">
          <option value="">전체 유형</option>
          <?php foreach ($types as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= ($type ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (isset($user) && $user['is_admin']): ?>
      <div class="form-group is_public">
        <select name="is_public" class="form-select">
          <option value="">공개 여부 (전체)</option>
          <option value="1" <?= ($is_public ?? '') === '1' ? 'selected' : '' ?>>공개만</option>
          <option value="0" <?= ($is_public ?? '') === '0' ? 'selected' : '' ?>>비공개만</option>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group button">
        <button type="submit" class="btn btn-primary">검색</button>
      </div>
    </form>
  </div>

  <!-- Error/Loading/No Result -->
  <?php if (isset($error) && $error && $error !== 'unset'): ?>
    <div class="d-flex justify-content-center align-items-center my-5">
      <div class="alert alert-danger d-flex align-items-center gap-2" style="font-size:1.1em;">
        <i class="fas fa-exclamation-triangle fa-lg me-2"></i> <?= e($error) ?>
      </div>
    </div>
  <?php elseif (empty($resources)): ?>
    <div class="d-flex justify-content-center align-items-center my-5">
      <div class="alert alert-warning d-flex align-items-center gap-2" style="font-size:1.1em;">
        <i class="fas fa-search fa-lg me-2"></i> 검색 결과가 없습니다. 다른 조건으로 시도해보세요.
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
                <span class="resource-type"><?= e($types[$resource['type']] ?? $resource['type']) ?></span>
              <?php endif; ?>
              <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                <span class="badge bg-secondary me-1 mb-1">#<?= e(is_array($tag) ? $tag['name'] : $tag) ?></span>
              <?php endforeach; ?>
            </div>
            <h5 class="card-title mb-2">
                <a href="/resources/view/<?= e($resource['id']) ?>" class="text-decoration-none text-dark">
                    <?= e($resource['title']) ?>
                </a>
            </h5>
            <p class="card-text flex-grow-1 mb-2">
                <a href="/resources/view/<?= e($resource['id']) ?>" class="text-decoration-none text-body">
                    <?= e(mb_strimwidth(strip_tags($resource['content'] ?? ''), 0, 80, '...')) ?>
                </a>
            </p>
          </div>
          <div class="resource-meta mb-1 mt-1">
            <span>작성자: <?= e($resource['author_name'] ?? '익명') ?></span>
            <span class="mx-2">|</span>
            <span>작성일: <?= e(date('Y-m-d', strtotime($resource['created_at'] ?? ''))) ?></span>
            <?php if (isset($resource['view_count'])): ?>
              <span class="mx-2">|</span>
              <span>조회수: <?= e($resource['view_count']) ?></span>
            <?php endif; ?>
            <?php if (isset($resource['rating'])): ?>
              <span class="mx-2">|</span>
              <span>평점: <?= number_format($resource['rating'], 1) ?></span>
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
            <a class="page-link" href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">이전</a>
          </li>
        <?php endif; ?>
        <li class="page-item disabled">
          <span class="page-link" style="background:#f8f9fa; color:#333; border:none; min-width:70px; text-align:center;"> <?= ($current_page ?? 1) ?> / <?= $total_pages ?> </span>
        </li>
        <?php if (($current_page ?? 1) < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="/resources?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">다음</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('select[name="tags[]"]').select2({ placeholder: "태그 선택", allowClear: true });
  document.querySelector('.search-form').addEventListener('submit', function() {
    document.getElementById('loading-spinner').classList.remove('d-none');
  });
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 