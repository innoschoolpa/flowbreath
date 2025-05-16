<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <!-- 검색 폼 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/resource/search" class="row g-3">
                <!-- 키워드 검색 -->
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" 
                               placeholder="제목, 내용, 요약에서 검색" 
                               value="<?= htmlspecialchars($keyword ?? '') ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> 검색
                        </button>
                    </div>
                </div>

                <!-- 태그 필터 -->
                <div class="col-md-6">
                    <select name="tags[]" class="form-select" multiple data-placeholder="태그 선택">
                        <?php foreach ($all_tags as $tag): ?>
                            <option value="<?= $tag['id'] ?>" 
                                <?= in_array($tag['id'], $selected_tag_ids ?? []) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 정렬 옵션 -->
                <div class="col-md-3">
                    <select name="sort" class="form-select">
                        <option value="created_desc" <?= ($sort ?? '') === 'created_desc' ? 'selected' : '' ?>>
                            최신순
                        </option>
                        <option value="created_asc" <?= ($sort ?? '') === 'created_asc' ? 'selected' : '' ?>>
                            오래된순
                        </option>
                        <option value="title_asc" <?= ($sort ?? '') === 'title_asc' ? 'selected' : '' ?>>
                            제목순
                        </option>
                    </select>
                </div>

                <!-- 검색 필터 -->
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="all" <?= ($filter ?? '') === 'all' ? 'selected' : '' ?>>
                            전체
                        </option>
                        <option value="public" <?= ($filter ?? '') === 'public' ? 'selected' : '' ?>>
                            공개만
                        </option>
                        <?php if (is_admin()): ?>
                            <option value="private" <?= ($filter ?? '') === 'private' ? 'selected' : '' ?>>
                                비공개만
                            </option>
                        <?php endif; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- 검색 결과 요약 -->
    <?php if (isset($keyword) || !empty($selected_tag_ids)): ?>
        <div class="alert alert-info mb-4">
            <strong>검색 결과:</strong>
            <?php if (!empty($keyword)): ?>
                "<?= htmlspecialchars($keyword) ?>" 검색어
            <?php endif; ?>
            <?php if (!empty($selected_tag_ids)): ?>
                <?php if (!empty($keyword)): ?> + <?php endif; ?>
                선택된 태그: 
                <?php 
                $selected_tag_names = array_map(function($tag_id) use ($all_tags) {
                    foreach ($all_tags as $tag) {
                        if ($tag['id'] == $tag_id) return $tag['name'];
                    }
                    return '';
                }, $selected_tag_ids);
                echo htmlspecialchars(implode(', ', $selected_tag_names));
                ?>
            <?php endif; ?>
            (총 <?= $total_count ?>개)
        </div>
    <?php endif; ?>

    <!-- 검색 결과 목록 -->
    <?php if (!empty($resources)): ?>
        <div class="row">
            <?php foreach ($resources as $resource): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/resource/view/<?= $resource['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($resource['title']) ?>
                                </a>
                                <?php if ($resource['visibility'] === 'private'): ?>
                                    <span class="badge bg-warning">비공개</span>
                                <?php endif; ?>
                            </h5>
                            <p class="card-text"><?= htmlspecialchars($resource['summary']) ?></p>
                            <div class="mb-2">
                                <?php if (!empty($resource['tags'])): ?>
                                    <?php foreach ($resource['tags'] as $tag): ?>
                                        <span class="badge bg-secondary me-1"><?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
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
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $query_string = http_build_query($query_params);
                    ?>
                    
                    <!-- 이전 페이지 -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= $query_string ?>">
                                이전
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 페이지 번호 -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= $query_string ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- 다음 페이지 -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= $query_string ?>">
                                다음
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-warning">
            검색 결과가 없습니다. 다른 검색어나 필터를 시도해보세요.
        </div>
    <?php endif; ?>
</div>

<!-- Select2 초기화 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 초기화
    $('select[name="tags[]"]').select2({
        placeholder: "태그를 선택하세요",
        allowClear: true,
        width: '100%'
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 