<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// src/View/resources/index.php
// 유튜브 ID 추출 함수 (shorts 지원)
if (!function_exists('extractYoutubeId')) {
    function extractYoutubeId($url) {
        if (preg_match('/(?:youtube\\.com\\/(?:.*[?&]v=|shorts\\/)|youtu\\.be\\/)([\\w-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
// 유튜브 링크 판별 함수(중복 선언 방지)
if (!function_exists('is_youtube_url')) {
    function is_youtube_url($url) {
        return preg_match('/(youtube\\.com\\/watch\\?v=|youtu\\.be\\/|youtube\\.com\\/shorts\\/)/i', $url);
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<div class="container">
    <h1 class="mb-4">리소스 목록</h1>
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="키워드(제목, 저자, 요약 등)" value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                <?php if (!empty($keyword)): ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearKeyword()" style="padding: 0.375rem 0.75rem;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-3">
            <select name="source_type" class="form-select">
                <option value="">전체 유형</option>
                <option value="Website" <?php if (($source_type ?? '') === 'Website') echo 'selected'; ?>>웹사이트</option>
                <option value="Paper" <?php if (($source_type ?? '') === 'Paper') echo 'selected'; ?>>논문</option>
                <option value="Book" <?php if (($source_type ?? '') === 'Book') echo 'selected'; ?>>책</option>
                <option value="Video" <?php if (($source_type ?? '') === 'Video') echo 'selected'; ?>>비디오</option>
                <option value="Podcast" <?php if (($source_type ?? '') === 'Podcast') echo 'selected'; ?>>팟캐스트</option>
                <option value="Personal Experience" <?php if (($source_type ?? '') === 'Personal Experience') echo 'selected'; ?>>개인 경험</option>
                <option value="Other" <?php if (($source_type ?? '') === 'Other') echo 'selected'; ?>>기타</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="tag_ids[]" class="form-select" id="tagSelect" multiple>
                <?php foreach (($all_tags ?? []) as $tag): ?>
                    <option value="<?php echo $tag['tag_id']; ?>"
                        <?php if (!empty($selected_tag_ids) && in_array($tag['tag_id'], $selected_tag_ids)) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($tag['tag_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">검색</button>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($resources as $resource): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2">
                            <a href="/resources/view/<?= htmlspecialchars($resource['id'] ?? '') ?>" class="text-decoration-none text-dark">
                                <?= htmlspecialchars($resource['title'] ?? '') ?>
                            </a>
                        </h5>
                        <span class="badge bg-secondary mb-2"><?= htmlspecialchars($resource['source_type'] ?? '') ?></span>
                        <p class="card-text flex-grow-1">
                            <a href="/resources/view/<?= htmlspecialchars($resource['id'] ?? '') ?>" class="text-decoration-none text-body">
                            <?php
                            $preview = '';
                            if (!empty($resource['content'])) {
                                $preview = mb_strimwidth(strip_tags($resource['content']), 0, 100, '...');
                            } elseif (!empty($resource['summary'])) {
                                $preview = $resource['summary'];
                            }
                            echo htmlspecialchars($preview);
                            ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<a href="/resources/create" class="btn btn-success">리소스 등록</a>
<?php if ($total_pages > 1): ?>
<nav aria-label="페이지네이션">
  <ul class="pagination justify-content-center">
    <?php if ($current_page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?<?php
          $params = $_GET;
          $params['page'] = $current_page - 1;
          echo http_build_query($params);
        ?>">이전</a>
      </li>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item<?php if ($i == $current_page) echo ' active'; ?>">
        <a class="page-link" href="?<?php
          $params = $_GET;
          $params['page'] = $i;
          echo http_build_query($params);
        ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
    <?php if ($current_page < $total_pages): ?>
      <li class="page-item">
        <a class="page-link" href="?<?php
          $params = $_GET;
          $params['page'] = $current_page + 1;
          echo http_build_query($params);
        ?>">다음</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tagSelect = document.getElementById('tagSelect');
    if (tagSelect) {
        tagSelect.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'OPTION') {
                e.preventDefault();
                const option = e.target;
                option.selected = !option.selected;
                tagSelect.focus();
            }
        });
    }
});

function clearKeyword() {
    const keywordInput = document.querySelector('input[name="keyword"]');
    keywordInput.value = '';
    keywordInput.focus();
}
</script>
<style>
.resource-text-vertical {
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 95%;
    min-width: 0;
}
.resource-title-fixed {
    width: 650px;
    min-width: 120px;
    max-width: 650px;
    flex: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
    vertical-align: middle;
}
.resource-summary-fixed {
    width: 670px;
    min-width: 120px;
    max-width: 670px;
    flex: none;
    overflow: hidden;
    display: block;
    vertical-align: middle;
}
.summary-clamp {
    font-size: 0.95rem;
    line-height: 1.4;
    max-height: 2.8em;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    white-space: normal;
    word-break: break-all;
}
ul.list-group.mb-4 {
    min-height: 685px; /* 필요에 따라 조정 */
}
.list-group-item.d-flex {
    align-items: center !important;
}
.d-flex.gap-2.ms-3 {
    align-self: flex-start;
}
.resource-link-block {
    display: block;
    cursor: pointer;
    transition: background 0.1s;
}
.resource-link-block:hover {
    background: #f8f9fa;
    text-decoration: none;
}
</style>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 