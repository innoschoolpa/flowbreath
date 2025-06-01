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
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title">
                                    <a href="/diary/<?= htmlspecialchars($diary['id'] ?? '') ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($diary['title'] ?? '') ?>
                                    </a>
                                </h5>
                                <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($diary['user_id']) && $diary['user_id'] == $_SESSION['user_id']): ?>
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
                            
                            <div class="card-text mb-3">
                                <?= htmlspecialchars(mb_substr(strip_tags($diary['content'] ?? ''), 0, 100)) ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $diary['profile_image'] ?? '/assets/images/default-avatar.png' ?>" 
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
                                                onclick="toggleLike(<?= $diary['id'] ?>)">
                                            <i class="far fa-heart"></i>
                                            <span class="like-count"><?= (int)($diary['like_count'] ?? 0) ?></span>
                                        </button>
                                    <?php else: ?>
                                        <a href="/login" class="btn btn-link text-muted me-3">
                                            <i class="far fa-heart"></i>
                                            <span class="like-count"><?= (int)($diary['like_count'] ?? 0) ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/diary/<?= $diary['id'] ?>" class="text-muted">
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
                            <input type="text" class="form-control" name="q" 
                                   placeholder="<?= __('diary.search_placeholder') ?>">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="tags" 
                                   placeholder="<?= __('diary.tags_placeholder') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __('diary.date_range') ?></label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="start_date">
                                <span class="input-group-text">~</span>
                                <input type="date" class="form-control" name="end_date">
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
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 