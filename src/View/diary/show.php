<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h2 class="card-title"><?= htmlspecialchars($diary['title']) ?></h2>
                        
                        <?php if ($diary['user_id'] == ($_SESSION['user_id'] ?? null)): ?>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="/diary/<?= $diary['id'] ?>/edit">
                                            <i class="fas fa-edit"></i> <?= __('diary.edit') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" 
                                           onclick="deleteDiary(<?= $diary['id'] ?>)">
                                            <i class="fas fa-trash"></i> <?= __('diary.delete') ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <img src="<?= $diary['profile_image'] ?? '/assets/images/default-avatar.png' ?>" 
                             class="rounded-circle me-2" width="40" height="40" 
                             alt="<?= htmlspecialchars($diary['author_name']) ?>">
                        <div>
                            <div class="fw-bold">
                                <?= htmlspecialchars($diary['author_name']) ?>
                            </div>
                            <div class="text-muted small">
                                <?= date('Y-m-d H:i', strtotime($diary['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="diary-content mb-4">
                        <?= $diary['content'] ?>
                    </div>

                    <?php if (!empty($diary['tags'])): ?>
                        <div class="mb-4">
                            <?php foreach ($diary['tags'] as $tag): ?>
                                <a href="/diary/search?tags=<?= urlencode($tag) ?>" 
                                   class="badge bg-light text-dark text-decoration-none me-1">
                                    #<?= htmlspecialchars($tag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center mb-4">
                        <button class="btn btn-link text-muted me-3" onclick="toggleLike(<?= $diary['id'] ?>)">
                            <i class="<?= $diary['is_liked'] ? 'fas' : 'far' ?> fa-heart"></i>
                            <span class="like-count"><?= $diary['like_count'] ?></span>
                        </button>
                        <span class="text-muted">
                            <i class="far fa-comment"></i>
                            <span><?= $diary['comment_count'] ?></span>
                        </span>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?= __('diary.comment') ?></h5>
                                <form id="commentForm" onsubmit="return submitComment(event)">
                                    <input type="hidden" name="diary_id" value="<?= $diary['id'] ?>">
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <?= __('diary.comment_submit') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="comments">
                        <?php if (empty($diary['comments'])): ?>
                            <div class="text-center text-muted">
                                <?= __('diary.no_comments') ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($diary['comments'] as $comment): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $comment['profile_image'] ?? '/assets/images/default-avatar.png' ?>" 
                                                     class="rounded-circle me-2" width="32" height="32" 
                                                     alt="<?= htmlspecialchars($comment['author_name']) ?>">
                                                <div>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($comment['author_name']) ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($comment['user_id'] == ($_SESSION['user_id'] ?? null)): ?>
                                                <button class="btn btn-link text-danger btn-sm" 
                                                        onclick="deleteComment(<?= $comment['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="comment-content">
                                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLike(diaryId) {
    fetch(`/diary/${diaryId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeButton = event.currentTarget;
            const likeCount = likeButton.querySelector('.like-count');
            const icon = likeButton.querySelector('i');
            
            if (icon.classList.contains('far')) {
                icon.classList.replace('far', 'fas');
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
            } else {
                icon.classList.replace('fas', 'far');
                likeCount.textContent = parseInt(likeCount.textContent) - 1;
            }
        }
    });
}

function deleteDiary(diaryId) {
    if (confirm('<?= __('diary.delete_confirm') ?>')) {
        fetch(`/diary/${diaryId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/diary';
            }
        });
    }
}

function submitComment(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('/diary/comment', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || '<?= __('diary.comment_error') ?>');
        }
    })
    .catch(error => {
        alert('<?= __('diary.comment_error') ?>');
    });
    
    return false;
}

function deleteComment(commentId) {
    if (confirm('<?= __('diary.comment_delete_confirm') ?>')) {
        fetch(`/diary/comment/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 