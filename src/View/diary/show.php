<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="mb-3 text-end">
                <a href="/diary" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> 목록으로
                </a>
            </div>
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
                            <?php 
                            $tags = [];
                            if (is_array($diary['tags'])) {
                                $tags = $diary['tags'];
                            } else if (is_string($diary['tags'])) {
                                $tags = array_filter(array_map('trim', explode(',', $diary['tags'])));
                            }
                            foreach ($tags as $tag): 
                            ?>
                                <a href="/diary/search?tags=<?= urlencode($tag) ?>" 
                                   class="badge bg-light text-dark text-decoration-none me-1">
                                    #<?= htmlspecialchars($tag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center mb-4">
                        <button class="btn btn-link text-muted me-3" onclick="toggleLike(<?= $diary['id'] ?>, event)">
                            <i class="<?= $diary['is_liked'] ? 'fas' : 'far' ?> fa-heart"></i>
                            <span class="like-count"><?= $diary['like_count'] ?></span>
                        </button>
                        <span class="text-muted me-3">
                            <i class="far fa-comment"></i>
                            <span><?= $diary['comment_count'] ?></span>
                        </span>
                        <span class="text-muted">
                            <i class="far fa-eye"></i>
                            <span><?= $diary['view_count'] ?? 0 ?></span>
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
                                                  placeholder="댓글을 입력하세요. 마크다운 문법을 지원합니다."></textarea>
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

                    <div id="comments" class="comments-list">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-spinner fa-spin"></i> 댓글을 불러오는 중...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLike(diaryId, event) {
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    const icon = event.currentTarget.querySelector('i');
    const countElement = event.currentTarget.querySelector('.like-count');
    
    // 현재 상태 저장
    const isCurrentlyLiked = icon.classList.contains('fas');
    const currentCount = parseInt(countElement.textContent);
    
    // 즉시 UI 업데이트 (낙관적 업데이트)
    if (isCurrentlyLiked) {
        icon.classList.replace('fas', 'far');
        countElement.textContent = currentCount - 1;
    } else {
        icon.classList.replace('far', 'fas');
        countElement.textContent = currentCount + 1;
    }
    
    fetch(`/diary/${diaryId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 서버 응답에 따라 UI 업데이트
            if (data.liked) {
                icon.classList.replace('far', 'fas');
            } else {
                icon.classList.replace('fas', 'far');
            }
            countElement.textContent = data.like_count;
        } else {
            // 실패 시 원래 상태로 되돌림
            if (isCurrentlyLiked) {
                icon.classList.replace('far', 'fas');
                countElement.textContent = currentCount;
            } else {
                icon.classList.replace('fas', 'far');
                countElement.textContent = currentCount;
            }
            console.error('Error:', data.error);
        }
    })
    .catch(error => {
        // 에러 발생 시 원래 상태로 되돌림
        if (isCurrentlyLiked) {
            icon.classList.replace('far', 'fas');
            countElement.textContent = currentCount;
        } else {
            icon.classList.replace('fas', 'far');
            countElement.textContent = currentCount;
        }
        console.error('Error:', error);
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

async function submitComment(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // 기존 알림 메시지 제거
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    try {
        // 버튼 비활성화 및 로딩 표시
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 처리중...';
        
        const formData = new FormData(form);
        const response = await fetch('/diary/comment', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Response status:', response.status);
        console.log('Response data:', data);
        
        if (data.success) {
            // 성공 메시지 표시
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success';
            successAlert.textContent = data.message || '댓글이 등록되었습니다.';
            form.parentNode.insertBefore(successAlert, form);
            
            // 댓글 목록에 새 댓글 추가
            const commentsList = document.getElementById('comments');
            if (commentsList && data.comment) {
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
                                ${data.comment.can_delete ? `
                                    <div class="btn-group">
                                        <button class="btn btn-link text-primary btn-sm" 
                                                onclick="editComment(${data.comment.id}, '${data.comment.content.replace(/'/g, "\\'")}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-link text-danger btn-sm" 
                                                onclick="deleteComment(${data.comment.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="comment-content">
                                ${data.comment.content}
                            </div>
                        </div>
                    </div>
                `;
                commentsList.insertAdjacentHTML('afterbegin', commentHtml);
            }
            
            // 폼 초기화
            form.reset();
        } else {
            // 에러 메시지 표시
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger';
            errorAlert.textContent = data.error || '댓글 등록에 실패했습니다.';
            form.parentNode.insertBefore(errorAlert, form);
        }
    } catch (error) {
        console.error('Error:', error);
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger';
        errorAlert.textContent = '댓글 등록 중 오류가 발생했습니다.';
        form.parentNode.insertBefore(errorAlert, form);
    } finally {
        // 버튼 상태 복원
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        // 3초 후 알림 메시지 제거
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.remove());
        }, 3000);
    }
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
                const commentElement = document.getElementById(`comment-${commentId}`);
                if (commentElement) {
                    commentElement.remove();
                }
            }
        });
    }
}

function editComment(commentId, content) {
    const commentElement = document.getElementById(`comment-${commentId}`);
    const currentContent = commentElement.querySelector('.comment-content');
    const originalContent = currentContent.innerHTML;
    
    // 수정 폼 생성
    const editForm = document.createElement('form');
    editForm.className = 'edit-comment-form';
    editForm.innerHTML = `
        <div class="mb-2">
            <textarea class="form-control" rows="3" required minlength="1" maxlength="1000">${content}</textarea>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> 저장
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(${commentId}, '${originalContent.replace(/'/g, "\\'")}')">
                <i class="fas fa-times"></i> 취소
            </button>
        </div>
    `;
    
    // 폼 제출 이벤트 처리
    editForm.onsubmit = async (e) => {
        e.preventDefault();
        const newContent = editForm.querySelector('textarea').value.trim();
        
        if (!newContent) {
            alert('댓글 내용을 입력해주세요.');
            return;
        }

        // CSRF 토큰 가져오기
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        if (!csrfToken) {
            alert('보안 토큰이 없습니다. 페이지를 새로고침 후 다시 시도해주세요.');
            return;
        }
        
        try {
            const response = await fetch(`/diary/comment/${commentId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    content: newContent,
                    csrf_token: csrfToken
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentContent.innerHTML = newContent;
                commentElement.querySelector('.btn-group').style.display = 'block';
                editForm.remove();
            } else {
                alert(data.error || '댓글 수정에 실패했습니다.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('댓글 수정 중 오류가 발생했습니다.');
        }
    };
    
    // 기존 내용을 수정 폼으로 교체
    currentContent.innerHTML = '';
    currentContent.appendChild(editForm);
    commentElement.querySelector('.btn-group').style.display = 'none';
}

function cancelEdit(commentId, originalContent) {
    const commentElement = document.getElementById(`comment-${commentId}`);
    const currentContent = commentElement.querySelector('.comment-content');
    currentContent.innerHTML = originalContent;
    commentElement.querySelector('.btn-group').style.display = 'block';
}

// 페이지 로드 시 댓글 불러오기
document.addEventListener('DOMContentLoaded', function() {
    loadComments();
});

async function loadComments() {
    const commentsContainer = document.getElementById('comments');
    const diaryId = <?= $diary['id'] ?>;
    
    try {
        const response = await fetch(`/diary/${diaryId}/comments`);
        const data = await response.json();
        
        if (data.success) {
            if (data.comments.length === 0) {
                commentsContainer.innerHTML = '<div class="text-center text-muted">아직 댓글이 없습니다.</div>';
            } else {
                commentsContainer.innerHTML = data.comments.map(comment => `
                    <div class="card mb-3" id="comment-${comment.id}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="${comment.profile_image}" 
                                         class="rounded-circle me-2" width="32" height="32" 
                                         alt="${comment.author_name}">
                                    <div>
                                        <div class="fw-bold">
                                            ${comment.author_name}
                                        </div>
                                        <div class="text-muted small">
                                            ${new Date(comment.created_at).toLocaleString()}
                                        </div>
                                    </div>
                                </div>
                                ${comment.can_delete ? `
                                    <div class="btn-group">
                                        <button class="btn btn-link text-primary btn-sm" 
                                                onclick="editComment(${comment.id}, '${comment.content.replace(/'/g, "\\'")}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-link text-danger btn-sm" 
                                                onclick="deleteComment(${comment.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="comment-content">
                                ${comment.content}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } else {
            commentsContainer.innerHTML = '<div class="text-center text-danger">댓글을 불러오는데 실패했습니다.</div>';
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        commentsContainer.innerHTML = '<div class="text-center text-danger">댓글을 불러오는데 실패했습니다.</div>';
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 