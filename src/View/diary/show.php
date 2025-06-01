<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h2 class="card-title"><?= htmlspecialchars($diary['title']) ?></h2>
                        
                        <?php 
                        // Debug information
                        error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
                        error_log("Diary user_id: " . ($diary['user_id'] ?? 'not set'));
                        error_log("Diary data: " . print_r($diary, true));
                        
                        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($diary['user_id']) && $diary['user_id'] == $_SESSION['user_id']): ?>
                            <div class="btn-group">
                                <a href="/diary/<?= $diary['id'] ?>/edit" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i> <?= __('diary.edit') ?>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteDiary(<?= $diary['id'] ?>)">
                                    <i class="fas fa-trash"></i> <?= __('diary.delete') ?>
                                </button>
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
                                <form id="commentForm" onsubmit="return submitComment(event)" class="needs-validation" novalidate>
                                    <input type="hidden" name="diary_id" value="<?= $diary['id'] ?>">
                                    <?php if (isset($_SESSION['csrf_token'])): ?>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <?php else: ?>
                                        <?php
                                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                                        ?>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="content" rows="3" required 
                                                  minlength="1" maxlength="1000" 
                                                  placeholder="<?= __('diary.comment_placeholder') ?>"></textarea>
                                        <div class="invalid-feedback">
                                            <?= __('diary.comment_required') ?>
                                        </div>
                                        <div class="form-text text-end">
                                            <span class="char-count">0</span>/1000
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> <?= __('diary.comment_submit') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <?= __('diary.login_required_comment') ?>
                            <a href="/login" class="alert-link"><?= __('auth.login') ?></a>
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
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // 버튼 비활성화 및 로딩 표시
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 처리중...';
    
    fetch('/diary/comment', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // 성공 메시지 표시
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-3';
            successAlert.innerHTML = data.message || '댓글이 등록되었습니다.';
            form.parentNode.insertBefore(successAlert, form.nextSibling);
            
            // 폼 초기화
            form.reset();
            
            // 새 댓글 추가
            if (data.comment) {
                const commentsContainer = document.getElementById('comments');
                const noCommentsMessage = commentsContainer.querySelector('.text-center.text-muted');
                if (noCommentsMessage) {
                    noCommentsMessage.remove();
                }
                
                const commentHtml = `
                    <div class="card mb-3" id="comment-${data.comment.id}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="${data.comment.profile_image}" 
                                         class="rounded-circle me-2" width="32" height="32" 
                                         alt="${data.comment.author_name}">
                                    <div>
                                        <div class="fw-bold">
                                            ${data.comment.author_name}
                                        </div>
                                        <div class="text-muted small">
                                            ${new Date(data.comment.created_at).toLocaleString()}
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-link text-danger btn-sm" 
                                        onclick="deleteComment(${data.comment.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="comment-content">
                                ${data.comment.content}
                            </div>
                        </div>
                    </div>
                `;
                
                commentsContainer.insertAdjacentHTML('afterbegin', commentHtml);
            }
            
            // 3초 후 성공 메시지 제거
            setTimeout(() => {
                successAlert.remove();
            }, 3000);
        } else {
            // 에러 메시지 표시
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = data.error || '댓글 등록에 실패했습니다.';
            form.parentNode.insertBefore(errorAlert, form.nextSibling);
            
            // 3초 후 에러 메시지 제거
            setTimeout(() => {
                errorAlert.remove();
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // 에러 메시지 표시
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger mt-3';
        errorAlert.innerHTML = '댓글 등록 중 오류가 발생했습니다.';
        form.parentNode.insertBefore(errorAlert, form.nextSibling);
        
        // 3초 후 에러 메시지 제거
        setTimeout(() => {
            errorAlert.remove();
        }, 3000);
    })
    .finally(() => {
        // 버튼 상태 복원
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
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