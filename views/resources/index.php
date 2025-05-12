<?php
/**
 * views/resources/index.php
 * 리소스 목록 페이지
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>리소스 목록 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>리소스 목록</h1>
            <?php if (is_admin()): ?>
            <a href="/resources/create" class="btn btn-primary">새 리소스 추가</a>
            <?php endif; ?>
        </header>

        <!-- 검색 폼 -->
        <form action="/resources" method="GET" class="search-form">
            <div class="search-group">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>" 
                       placeholder="검색어를 입력하세요" class="search-input">
                <select name="source_type" class="search-select">
                    <option value="">소스 유형</option>
                    <option value="article" <?= ($source_type ?? '') === 'article' ? 'selected' : '' ?>>기사</option>
                    <option value="book" <?= ($source_type ?? '') === 'book' ? 'selected' : '' ?>>책</option>
                    <option value="video" <?= ($source_type ?? '') === 'video' ? 'selected' : '' ?>>비디오</option>
                    <option value="other" <?= ($source_type ?? '') === 'other' ? 'selected' : '' ?>>기타</option>
                </select>
                <button type="submit" class="btn btn-search">검색</button>
            </div>
        </form>

        <!-- 리소스 목록 -->
        <div class="resource-list">
            <?php if (empty($resources)): ?>
            <p class="no-results">검색 결과가 없습니다.</p>
            <?php else: ?>
                <?php foreach ($resources as $resource): ?>
                <article class="resource-card">
                    <h2 class="resource-title">
                        <a href="/resources/view/<?= $resource['id'] ?>">
                            <?= htmlspecialchars($resource['title']) ?>
                        </a>
                    </h2>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                        </span>
                        <?php if (!empty($resource['tags'])): ?>
                        <div class="resource-tags">
                            <?php foreach ($resource['tags'] as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="resource-summary">
                        <?= htmlspecialchars($resource['summary'] ?? '') ?>
                    </p>
                    <?php if (is_admin()): ?>
                    <div class="resource-actions">
                        <a href="/resources/edit/<?= $resource['id'] ?>" class="btn btn-edit">수정</a>
                        <form action="/resources/<?= $resource['id'] ?>" method="POST" class="delete-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-delete" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>&source_type=<?= urlencode($source_type ?? '') ?>" 
               class="btn btn-page">이전</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&keyword=<?= urlencode($keyword ?? '') ?>&source_type=<?= urlencode($source_type ?? '') ?>" 
               class="btn btn-page <?= $i === $currentPage ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>&keyword=<?= urlencode($keyword ?? '') ?>&source_type=<?= urlencode($source_type ?? '') ?>" 
               class="btn btn-page">다음</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html> 