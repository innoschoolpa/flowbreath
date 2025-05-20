<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <!-- 제목 및 메타 정보 -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="card-title"><?= htmlspecialchars($resource['title']) ?></h1>
                    <div class="text-muted">
                        <small>
                            작성자: <a href="/profile/<?= htmlspecialchars($resource['user_id']) ?>" class="text-decoration-none"><?= htmlspecialchars($resource['author_name']) ?></a> | 
                            작성일: <?= date('Y-m-d', strtotime($resource['created_at'])) ?> | 
                            조회수: <?= number_format($resource['view_count'] ?? 0) ?>
                            <?php if ($resource['updated_at'] && $resource['updated_at'] !== $resource['created_at']): ?>
                                | 수정일: <?= date('Y-m-d', strtotime($resource['updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div>
                    <?php if ($resource['visibility'] === 'private'): ?>
                        <span class="badge bg-warning"><?= __('resource.is_private') ?></span>
                    <?php endif; ?>
                    <?php if ($resource['is_pinned']): ?>
                        <span class="badge bg-info"><?= __('resource.is_pinned') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 태그 -->
            <?php if (!empty($resource['tags'])): ?>
                <div class="mb-4">
                    <?php foreach ($resource['tags'] as $tag): ?>
                        <a href="/resource/list?tags[]=<?= $tag['id'] ?>" class="badge bg-secondary text-decoration-none me-1">
                            <?= htmlspecialchars($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- URL -->
            <?php if (!empty($resource['url'])): ?>
                <div class="mb-4">
                    <strong><?= __('resource.url') ?>:</strong>
                    <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlspecialchars($resource['url']) ?>
                    </a>
                    
                    <?php
                    // Check if the URL is a YouTube URL
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $resource['url'], $matches)) {
                        $youtube_id = $matches[1];
                        ?>
                        <div class="mt-3">
                            <div class="ratio ratio-16x9">
                                <iframe 
                                    src="https://www.youtube.com/embed/<?= $youtube_id ?>" 
                                    title="YouTube video player" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- 요약 -->
            <div class="mb-4">
                <h5><?= __('resource.summary') ?></h5>
                <p class="card-text"><?= nl2br(htmlspecialchars($resource['summary'])) ?></p>
            </div>

            <!-- 내용 -->
            <?php if (!empty($resource['content'])): ?>
                <div class="mb-4">
                    <h5><?= __('resource.content') ?></h5>
                    <div class="formatted-content">
                        <?= $resource['content'] // HTML 내용은 이미 저장 시 필터링됨 ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 초기 인상 -->
            <?php if (!empty($resource['initial_impression'])): ?>
                <div class="mb-4">
                    <h5><?= __('resource.initial_impression') ?></h5>
                    <div class="formatted-content">
                        <?= $resource['initial_impression'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 개인적 연관성 -->
            <?php if (!empty($resource['personal_connection'])): ?>
                <div class="mb-4">
                    <h5><?= __('resource.personal_connection') ?></h5>
                    <div class="formatted-content">
                        <?= $resource['personal_connection'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 반영 및 통찰 -->
            <?php if (!empty($resource['reflection_insights'])): ?>
                <div class="mb-4">
                    <h5><?= __('resource.reflection_insights') ?></h5>
                    <div class="formatted-content">
                        <?= $resource['reflection_insights'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 적용 아이디어 -->
            <?php if (!empty($resource['application_ideas'])): ?>
                <div class="mb-4">
                    <h5><?= __('resource.application_ideas') ?></h5>
                    <div class="formatted-content">
                        <?= $resource['application_ideas'] ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 작업 버튼 -->
            <div class="mt-4 d-flex justify-content-between">
                <a href="/resource/list" class="btn btn-outline-secondary">
                    <?= __('back') ?>
                </a>
                <?php if (is_admin()): ?>
                    <div>
                        <a href="/resource/edit/<?= $resource['id'] ?>" class="btn btn-primary me-2">
                            <?= __('edit') ?>
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $resource['id'] ?>)">
                            <?= __('delete') ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 관련 리소스 -->
    <?php if (!empty($related_resources)): ?>
        <div class="mt-4">
            <h3><?= __('resource.related') ?></h3>
            <div class="row">
                <?php foreach ($related_resources as $related): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="/resource/view/<?= $related['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h5>
                                <p class="card-text"><?= htmlspecialchars($related['summary']) ?></p>
                                <?php if (!empty($related['tags'])): ?>
                                    <div class="mt-2">
                                        <?php foreach ($related['tags'] as $tag): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($tag['name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('resource.delete') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><?= __('resource.messages.delete_confirm') ?></p>
                <p class="text-danger"><?= __('resource.messages.delete_warning') ?></p>
            </div>
            <div class="modal-footer">
                <form action="/resource/delete/<?= $resource['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <?= __('delete') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 서식 있는 내용 스타일 -->
<style>
.formatted-content {
    line-height: 1.6;
}
.formatted-content p {
    margin-bottom: 1rem;
}
.formatted-content h1, 
.formatted-content h2, 
.formatted-content h3, 
.formatted-content h4, 
.formatted-content h5, 
.formatted-content h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}
.formatted-content ul, 
.formatted-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}
.formatted-content table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}
.formatted-content table th,
.formatted-content table td {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
}
.formatted-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}
.formatted-content blockquote {
    margin: 1rem 0;
    padding: 1rem;
    border-left: 4px solid #dee2e6;
    background-color: #f8f9fa;
}
</style>

<script>
function confirmDelete(resourceId) {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 