<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= __('diary.title') ?></h2>
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <a href="/diary/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= __('diary.create') ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <?php if (!empty($diaries) && is_array($diaries)): ?>
                <?php foreach ($diaries as $diary): ?>
                    <?php if (!is_array($diary)) continue; ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">
                                    <a href="/diary/<?= htmlspecialchars($diary['id'] ?? '') ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($diary['title'] ?? '') ?>
                                    </a>
                                </h5>
                                <?php 
                                // Debug information
                                error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
                                error_log("Diary user_id: " . ($diary['user_id'] ?? 'not set'));
                                error_log("Diary data: " . print_r($diary, true));
                                
                                if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($diary['user_id']) && $diary['user_id'] == $_SESSION['user_id']): ?>
                                    <div class="btn-group">
                                        <a href="/diary/<?= $diary['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> <?= __('diary.edit') ?>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDiary(<?= $diary['id'] ?>)">
                                            <i class="fas fa-trash"></i> <?= __('diary.delete') ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-text mb-3">
                                <?= htmlspecialchars(mb_substr(strip_tags($diary['content'] ?? ''), 0, 100)) ?>
                            </div>

                            <?php if (!empty($diary['tags'])): ?>
                                <div class="mb-2">
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

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $diary['profile_image'] ?? '/assets/images/default-avatar.svg' ?>" 
                                         class="rounded-circle me-2" width="32" height="32" 
                                         alt="<?= htmlspecialchars($diary['author_name'] ?? '') ?>">
                                    <div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($diary['author_name'] ?? '') ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= isset($diary['created_at']) && $diary['created_at'] ? date('Y-m-d H:i', strtotime($diary['created_at'])) : '' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                                        <button class="btn btn-link text-muted me-3" 
                                                onclick="toggleLike(<?= $diary['id'] ?? 0 ?>)">
                                            <i class="far fa-heart"></i>
                                            <span class="like-count"><?= (int)($diary['like_count'] ?? 0) ?></span>
                                        </button>
                                    <?php else: ?>
                                        <a href="/login" class="btn btn-link text-muted me-3">
                                            <i class="far fa-heart"></i>
                                            <span class="like-count"><?= (int)($diary['like_count'] ?? 0) ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/diary/<?= $diary['id'] ?? '' ?>" class="text-muted">
                                        <i class="far fa-comment"></i>
                                        <span><?= (int)($diary['comment_count'] ?? 0) ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (count($diaries) >= 20): ?>
                    <div class="text-center mt-4">
                        <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-primary">
                            <?= __('pagination.next') ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <?= __('diary.no_diaries') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= __('diary.search') ?></h5>
                    <form action="/diary/search" method="GET">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="query" 
                                   placeholder="<?= __('diary.search_placeholder') ?>" value="<?= htmlspecialchars($query ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="tag" 
                                   placeholder="<?= __('diary.tags_placeholder') ?>" value="<?= htmlspecialchars($tag ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('diary.date_range') ?></label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
                                <span class="input-group-text">~</span>
                                <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <?= __('diary.search') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLike(diaryId) {
    if (!diaryId) return;
    
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
                window.location.reload();
            } else {
                alert(data.error || '<?= __('diary.delete_error') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('diary.delete_error') ?>');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 