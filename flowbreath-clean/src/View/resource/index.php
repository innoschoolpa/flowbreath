<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <!-- 검색 및 필터링 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="keyword" class="form-control" placeholder="검색어를 입력하세요" value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <select name="tags" class="form-select" multiple>
                        <?php foreach ($tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $tag_ids) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">검색</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 리소스 목록 -->
    <div class="row">
        <?php foreach ($resources as $resource): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="/resource/view/<?= $resource['id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($resource['title']) ?>
                            </a>
                            <?php if ($resource['is_private']): ?>
                                <span class="badge bg-warning">비공개</span>
                            <?php endif; ?>
                        </h5>
                        <p class="card-text">
                            <a href="/resource/view/<?= $resource['id'] ?>" class="text-decoration-none text-body">
                                <?= htmlspecialchars($resource['summary']) ?>
                            </a>
                        </p>
                        <div class="mb-2">
                            <?php if (!empty($resource['tags'])): ?>
                                <?php foreach (explode(',', $resource['tags']) as $tag): ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            <span>작성자: <?= htmlspecialchars($resource['author_name']) ?></span>
                            <span class="mx-2">|</span>
                            <span>작성일: <?= date('Y-m-d', strtotime($resource['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&tags=<?= implode(',', $tag_ids) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 