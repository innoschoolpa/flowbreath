<?php
// src/View/resources/list.php
?>
<div class="container py-4">
  <div class="d-flex flex-wrap align-items-center mb-3 gap-2">
    <form class="flex-grow-1 d-flex gap-2" method="get" action="/resources">
      <input type="text" name="q" class="form-control" placeholder="키워드(제목, 저자, 요약 등)" value="<?= e($_GET['q'] ?? '') ?>">
      <select name="type" class="form-select w-auto">
        <option value="">전체 유형</option>
        <?php foreach ($types as $type): ?>
          <option value="<?= e($type) ?>" <?= (($_GET['type'] ?? '') === $type) ? 'selected' : '' ?>><?= e($type) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit">검색</button>
    </form>
    <a href="/resources/create" class="btn btn-success ms-auto">리소스 등록</a>
  </div>
  <div class="row g-3">
    <?php foreach ($resources as $resource): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-2"><?= e($resource['title']) ?></h5>
            <span class="badge bg-info mb-2"><?= e($resource['type'] ?? '기타') ?></span>
            <p class="card-text text-truncate"><?= e($resource['summary'] ?? mb_strimwidth(strip_tags($resource['content'] ?? ''),0,80,'...')) ?></p>
            <div class="mb-2">
              <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                <span class="badge bg-secondary">#<?= e(is_array($tag) ? ($tag['name'] ?? $tag[0] ?? '') : $tag) ?></span>
              <?php endforeach; ?>
            </div>
            <a href="/resources/<?= e($resource['id']) ?>" class="btn btn-outline-primary btn-sm">자세히 보기</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div> 