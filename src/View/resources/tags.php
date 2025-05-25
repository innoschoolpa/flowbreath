<?php
// src/View/resources/tags.php
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --hover-bg: rgba(255, 255, 255, 0.1);
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.card-title a {
    color: var(--accent-color);
    transition: color 0.2s ease-in-out;
}

.card-title a:hover {
    color: #0284c7;
}

.card-text {
    color: var(--text-color);
    opacity: 0.9;
}

.badge.bg-primary {
    background-color: var(--accent-color) !important;
    color: var(--text-color);
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    border-color: var(--warning-color);
    color: var(--warning-color);
}

h1 {
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: 2rem;
}

/* 태그 카드 내부 여백 조정 */
.card-body {
    padding: 1.5rem;
}

/* 태그 이름 스타일 */
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

/* 리소스 개수 뱃지 스타일 */
.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.375rem;
}

/* 반응형 그리드 간격 조정 */
.row {
    margin: -0.75rem;
}

.col {
    padding: 0.75rem;
}

@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>

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
                                <p class="card-text flex-grow-1"><?= htmlspecialchars($tag['description']) ?></p>
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