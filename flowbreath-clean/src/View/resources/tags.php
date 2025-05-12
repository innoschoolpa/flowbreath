<?php
// src/View/resources/tags.php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">태그 목록</h1>
    <?php if (!empty($tags)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($tags as $tag): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">
                                <a href="/resources?tags[]=<?= htmlspecialchars($tag['id']) ?>" class="text-decoration-none">
                                    #<?= htmlspecialchars($tag['name']) ?>
                                </a>
                            </h5>
                            <?php if (!empty($tag['description'])): ?>
                                <p class="card-text flex-grow-1"> <?= htmlspecialchars($tag['description']) ?> </p>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <span class="badge bg-primary">리소스 <?= $tag['resource_count'] ?? 0 ?>개</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4">등록된 태그가 없습니다.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 