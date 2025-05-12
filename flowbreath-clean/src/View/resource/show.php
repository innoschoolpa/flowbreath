<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <!-- 리소스 상세 정보 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="card-title mb-0">
                    <?= htmlspecialchars($resource['title']) ?>
                    <?php if (!$resource['is_public']): ?>
                        <span class="badge bg-warning">비공개</span>
                    <?php endif; ?>
                </h2>
                <?php if (is_admin()): ?>
                    <div class="btn-group">
                        <a href="/resource/edit/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> 수정
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDelete(<?= $resource['id'] ?>)">
                            <i class="bi bi-trash"></i> 삭제
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 메타 정보 -->
            <div class="mb-3">
                <div class="text-muted small">
                    <span>작성자: <?= htmlspecialchars($resource['author_name']) ?></span>
                    <span class="mx-2">|</span>
                    <span>작성일: <?= date('Y-m-d', strtotime($resource['created_at'])) ?></span>
                    <?php if ($resource['updated_at']): ?>
                        <span class="mx-2">|</span>
                        <span>수정일: <?= date('Y-m-d', strtotime($resource['updated_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 태그 -->
            <?php if (!empty($tags)): ?>
                <div class="mb-3">
                    <?php foreach ($tags as $tag): ?>
                        <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 요약 -->
            <?php if (!empty($resource['summary'])): ?>
                <div class="mb-4">
                    <h5>요약</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($resource['summary'])) ?></p>
                </div>
            <?php endif; ?>

            <!-- URL -->
            <?php if (!empty($resource['url'])): ?>
                <div class="mb-4">
                    <h5>원본 링크</h5>
                    <a href="<?= htmlspecialchars($resource['url']) ?>" target="_blank" class="text-decoration-none">
                        <?= htmlspecialchars($resource['url']) ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- 상세 내용 -->
            <?php if (!empty($resource['content'])): ?>
                <div class="mb-4">
                    <h5>상세 내용</h5>
                    <div class="card-text">
                        <?php
                        if (is_html($resource['content'])) {
                            echo $resource['content'];
                        } else {
                            echo markdown_to_html($resource['content']);
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 추가 정보 -->
            <?php if (!empty($resource['initial_impression'])): ?>
                <div class="mb-4">
                    <h5>초기 인상</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($resource['initial_impression'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($resource['personal_connection'])): ?>
                <div class="mb-4">
                    <h5>개인적 연관성</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($resource['personal_connection'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($resource['reflection_insights'])): ?>
                <div class="mb-4">
                    <h5>반영 및 통찰</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($resource['reflection_insights'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($resource['application_ideas'])): ?>
                <div class="mb-4">
                    <h5>적용 아이디어</h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($resource['application_ideas'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 관련 리소스 -->
    <?php if (!empty($related_resources)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">관련 리소스</h4>
                <div class="row">
                    <?php foreach ($related_resources as $related): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/resource/view/<?= $related['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text small"><?= htmlspecialchars($related['summary']) ?></p>
                                    <?php if (!empty($related['tags'])): ?>
                                        <div class="mb-2">
                                            <?php foreach (explode(',', $related['tags']) as $tag): ?>
                                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 삭제 확인 모달 -->
<?php if (is_admin()): ?>
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">리소스 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 리소스를 삭제하시겠습니까?</p>
                <p class="text-danger">이 작업은 되돌릴 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <form action="/resource/delete/<?= $resource['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(resourceId) {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 