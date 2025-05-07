<!-- 댓글 섹션 -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><?= __('comment.write_comment') ?></h5>
    </div>
    <div class="card-body">
        <?php if (is_logged_in()): ?>
            <form action="/comments" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
                
                <div class="mb-3">
                    <textarea name="content" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_public" id="is_public" checked>
                        <label class="form-check-label" for="is_public"><?= __('comment.is_public') ?></label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= __('comment.submit') ?></button>
            </form>
        <?php else: ?>
            <p class="text-muted"><?= __('auth.login_required') ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- 댓글 목록 -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><?= __('comment.write_comment') ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($comments)): ?>
            <p class="text-muted"><?= __('comment.no_comments') ?></p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment mb-3">
                    <div class="d-flex">
                        <img src="<?= htmlspecialchars($comment['profile_image'] ?? '/images/default-profile.png') ?>" 
                             alt="" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?= htmlspecialchars($comment['user_name']) ?></h6>
                                <small class="text-muted">
                                    <?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?>
                                </small>
                            </div>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            
                            <?php if (is_logged_in() && ($_SESSION['user_id'] === $comment['user_id'] || is_admin())): ?>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary edit-comment" 
                                            data-id="<?= $comment['id'] ?>"
                                            data-content="<?= htmlspecialchars($comment['content']) ?>">
                                        <?= __('comment.edit_comment') ?>
                                    </button>
                                    <form action="/comments/<?= $comment['id'] ?>/delete" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('<?= __('comment.delete_confirm') ?>')">
                                            <?= __('comment.delete_comment') ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if ($total_comments > count($comments)): ?>
                <button class="btn btn-link load-more-comments" data-page="1">
                    <?= __('comment.load_more') ?>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 댓글 수정 모달 -->
<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('comment.edit_comment') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCommentForm" action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_public" id="edit_is_public" checked>
                            <label class="form-check-label" for="edit_is_public"><?= __('comment.is_public') ?></label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('comment.cancel') ?></button>
                <button type="submit" form="editCommentForm" class="btn btn-primary"><?= __('comment.update') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 댓글 수정 모달
    const editModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
    const editForm = document.getElementById('editCommentForm');
    const editContent = editForm.querySelector('textarea[name="content"]');
    const editIsPublic = editForm.querySelector('input[name="is_public"]');
    
    document.querySelectorAll('.edit-comment').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const content = this.dataset.content;
            
            editForm.action = `/comments/${id}`;
            editContent.value = content;
            editModal.show();
        });
    });
    
    // 더보기 버튼
    const loadMoreButton = document.querySelector('.load-more-comments');
    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function() {
            const page = parseInt(this.dataset.page) + 1;
            const resourceId = <?= $resource['id'] ?>;
            
            fetch(`/api/comments?resource_id=${resourceId}&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    // 댓글 목록에 추가
                    const commentsContainer = document.querySelector('.card-body');
                    data.comments.forEach(comment => {
                        // 댓글 HTML 생성 및 추가
                    });
                    
                    this.dataset.page = page;
                    if (data.has_more === false) {
                        this.remove();
                    }
                });
        });
    }
});
</script> 