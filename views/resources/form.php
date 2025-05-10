<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($resource) ? '리소스 수정' : '새 리소스 추가' ?> - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?= isset($resource) ? '리소스 수정' : '새 리소스 추가' ?></h1>
        </header>

        <form action="<?= isset($resource) ? "/resources/{$resource['id']}" : '/resources/store' ?>" method="post">
<?php if (isset($resource)): ?>
  <input type="hidden" name="_method" value="PUT">
<?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label for="title">제목 *</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($resource['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="url">원본 URL</label>
                <input type="url" id="url" name="url"
                       value="<?= htmlspecialchars($resource['url'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="source_type">소스 유형</label>
                <select id="source_type" name="source_type">
                    <option value="">선택하세요</option>
                    <option value="article" <?= ($resource['source_type'] ?? '') === 'article' ? 'selected' : '' ?>>기사</option>
                    <option value="book" <?= ($resource['source_type'] ?? '') === 'book' ? 'selected' : '' ?>>책</option>
                    <option value="video" <?= ($resource['source_type'] ?? '') === 'video' ? 'selected' : '' ?>>비디오</option>
                    <option value="other" <?= ($resource['source_type'] ?? '') === 'other' ? 'selected' : '' ?>>기타</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">상태</label>
                <select id="status" name="status">
                    <option value="draft" <?= ($resource['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>임시저장</option>
                    <option value="published" <?= ($resource['status'] ?? 'draft') === 'published' ? 'selected' : '' ?>>발행</option>
                </select>
            </div>

            <div class="form-group">
                <label for="summary">요약</label>
                <textarea id="summary" name="summary" rows="4"><?= htmlspecialchars($resource['summary'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="content">상세 내용</label>
                <textarea id="content" name="content" rows="10"><?= htmlspecialchars($resource['content'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="tags">태그 (쉼표로 구분)</label>
                <input type="text" id="tags" name="tags" 
                       value="<?= htmlspecialchars(implode(', ', $resource['tags'] ?? [])) ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_public" value="1" 
                           <?= ($resource['is_public'] ?? 1) ? 'checked' : '' ?>>
                    공개 리소스
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= isset($resource) ? '수정하기' : '저장하기' ?>
                </button>
                <a href="/resources" class="btn btn-secondary">취소</a>
            </div>
        </form>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html> 