<?php
/**
 * views/resources/show.php
 * 리소스 상세 보기 페이지
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resource['title']) ?> - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?= htmlspecialchars($resource['title']) ?></h1>
            <?php if (is_admin()): ?>
            <div class="admin-actions">
                <a href="/resources/edit/<?= $resource['id'] ?>" class="btn btn-edit">수정</a>
                <form action="/resources/<?= $resource['id'] ?>" method="POST" class="delete-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-delete" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                </form>
            </div>
            <?php endif; ?>
        </header>

        <article class="resource-detail">
            <div class="resource-meta">
                <span class="resource-date">
                    <?= date('Y-m-d', strtotime($resource['created_at'])) ?>
                </span>
                <?php if (!empty($tags)): ?>
                <div class="resource-tags">
                    <?php foreach ($tags as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="resource-content">
                <?php if (!empty($resource['summary'])): ?>
                <div class="resource-summary">
                    <h2>요약</h2>
                    <p><?= nl2br(htmlspecialchars($resource['summary'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($resource['content'])): ?>
                <div class="resource-main-content">
                    <h2>상세 내용</h2>
                    <div class="content-body">
                        <?= nl2br(htmlspecialchars($resource['content'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($resource['url'])): ?>
                <div class="resource-link">
                    <h2>원본 링크</h2>
                    <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialchars($resource['url']) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($related_resources)): ?>
            <div class="related-resources">
                <h2>관련 리소스</h2>
                <div class="related-list">
                    <?php foreach ($related_resources as $related): ?>
                    <div class="related-item">
                        <h3>
                            <a href="/resources/view/<?= $related['id'] ?>">
                                <?= htmlspecialchars($related['title']) ?>
                            </a>
                        </h3>
                        <?php if (!empty($related['summary'])): ?>
                        <p><?= htmlspecialchars($related['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </article>

        <div class="navigation">
            <a href="/resources" class="btn btn-back">목록으로 돌아가기</a>
        </div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html> 