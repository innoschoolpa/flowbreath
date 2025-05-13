<?php
/**
 * views/resources/index.php
 * 리소스 목록 페이지
 */
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_SESSION['lang'] === 'en' ? 'Resource List' : '리소스 목록' ?> - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?= $_SESSION['lang'] === 'en' ? 'Resource List' : '리소스 목록' ?></h1>
            <?php if (is_admin()): ?>
            <a href="/resources/create" class="btn btn-primary">
                <?= $_SESSION['lang'] === 'en' ? 'Add New Resource' : '새 리소스 추가' ?>
            </a>
            <?php endif; ?>
        </header>

        <!-- 검색 및 필터 폼 -->
        <form action="/resources" method="GET" class="search-form">
            <div class="search-group">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>" 
                       placeholder="<?= $_SESSION['lang'] === 'en' ? 'Enter search term' : '검색어를 입력하세요' ?>" 
                       class="search-input">
                
                <!-- 리소스 타입 필터 -->
                <select name="type" class="search-select">
                    <option value=""><?= $_SESSION['lang'] === 'en' ? 'Resource Type' : '리소스 유형' ?></option>
                    <?php foreach ($types as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($type ?? '') === $value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <!-- 태그 필터 -->
                <select name="tags[]" multiple class="search-select">
                    <?php foreach ($all_tags as $tag): ?>
                    <option value="<?= $tag['id'] ?>" 
                            <?= in_array($tag['id'], $selected_tags ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tag['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <!-- 정렬 옵션 -->
                <select name="sort" class="search-select">
                    <option value="created_desc" <?= ($sort ?? '') === 'created_desc' ? 'selected' : '' ?>>
                        <?= $_SESSION['lang'] === 'en' ? 'Latest' : '최신순' ?>
                    </option>
                    <option value="views_desc" <?= ($sort ?? '') === 'views_desc' ? 'selected' : '' ?>>
                        <?= $_SESSION['lang'] === 'en' ? 'Most Viewed' : '조회순' ?>
                    </option>
                    <option value="rating_desc" <?= ($sort ?? '') === 'rating_desc' ? 'selected' : '' ?>>
                        <?= $_SESSION['lang'] === 'en' ? 'Highest Rated' : '평점순' ?>
                    </option>
                </select>

                <button type="submit" class="btn btn-search">
                    <?= $_SESSION['lang'] === 'en' ? 'Search' : '검색' ?>
                </button>
            </div>
        </form>

        <!-- 리소스 목록 -->
        <div class="resource-list">
            <?php if (empty($resources)): ?>
            <p class="no-results">
                <?= $_SESSION['lang'] === 'en' ? 'No results found.' : '검색 결과가 없습니다.' ?>
            </p>
            <?php else: ?>
                <?php foreach ($resources as $resource): ?>
                <article class="resource-card">
                    <div class="resource-header">
                        <h2 class="resource-title">
                            <a href="/resources/view/<?= $resource['id'] ?>">
                                <?= htmlspecialchars($resource['title']) ?>
                            </a>
                        </h2>
                        <span class="resource-type">
                            <?= htmlspecialchars($types[$resource['type']] ?? '') ?>
                        </span>
                    </div>
                    
                    <div class="resource-meta">
                        <span class="resource-date">
                            <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                        </span>
                        <span class="resource-views">
                            <i class="icon-eye"></i> <?= number_format($resource['view_count'] ?? 0) ?>
                        </span>
                        <span class="resource-rating">
                            <i class="icon-star"></i> <?= number_format($resource['rating'] ?? 0, 1) ?>
                        </span>
                        <?php if (!empty($resource['tags'])): ?>
                        <div class="resource-tags">
                            <?php foreach ($resource['tags'] as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <p class="resource-description">
                        <?= htmlspecialchars($resource['description'] ?? '') ?>
                    </p>

                    <?php if (is_admin()): ?>
                    <div class="resource-actions">
                        <a href="/resources/edit/<?= $resource['id'] ?>" class="btn btn-edit">
                            <?= $_SESSION['lang'] === 'en' ? 'Edit' : '수정' ?>
                        </a>
                        <form action="/resources/<?= $resource['id'] ?>" method="POST" class="delete-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-delete" 
                                    onclick="return confirm('<?= $_SESSION['lang'] === 'en' ? 'Are you sure you want to delete this resource?' : '정말 삭제하시겠습니까?' ?>')">
                                <?= $_SESSION['lang'] === 'en' ? 'Delete' : '삭제' ?>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
            <a href="?page=<?= $current_page - 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>&type=<?= urlencode($type ?? '') ?>&sort=<?= urlencode($sort ?? '') ?>" 
               class="btn btn-page">
                <?= $_SESSION['lang'] === 'en' ? 'Previous' : '이전' ?>
            </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword ?? '') ?>&type=<?= urlencode($type ?? '') ?>&sort=<?= urlencode($sort ?? '') ?>" 
               class="btn btn-page <?= $i === $current_page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?= $current_page + 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>&type=<?= urlencode($type ?? '') ?>&sort=<?= urlencode($sort ?? '') ?>" 
               class="btn btn-page">
                <?= $_SESSION['lang'] === 'en' ? 'Next' : '다음' ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html> 