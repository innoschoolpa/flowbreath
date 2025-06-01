<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4"><?= __('profile.edit_title') ?></h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form action="/profile/update" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <!-- 프로필 이미지 -->
                        <div class="mb-4 text-center">
                            <div class="position-relative d-inline-block">
                                <img src="<?= htmlspecialchars($user['profile_image'] ?? '/assets/images/default-avatar.png') ?>" 
                                     alt="<?= __('profile.profile_image') ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <label for="profile_image" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle" style="width: 32px; height: 32px;">
                                    <i class="bi bi-camera"></i>
                                </label>
                            </div>
                            <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">
                        </div>

                        <!-- 이름 -->
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= __('profile.name') ?> *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                            <div class="invalid-feedback">
                                <?= __('profile.name_required') ?>
                            </div>
                        </div>

                        <!-- 이메일 (읽기 전용) -->
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('profile.email') ?></label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            <?php if ($user['google_id']): ?>
                                <div class="form-text">
                                    <i class="bi bi-google text-danger"></i> 
                                    <?= __('profile.google_connected') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 비밀번호 변경 (Google 계정이 아닌 경우만) -->
                        <?php if (!$user['google_id']): ?>
                            <div class="mb-3">
                                <label for="current_password" class="form-label"><?= __('profile.current_password') ?></label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label"><?= __('profile.new_password') ?></label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8">
                                <div class="form-text"><?= __('profile.password_hint') ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password_confirm" class="form-label"><?= __('profile.confirm_password') ?></label>
                                <input type="password" class="form-control" id="new_password_confirm" 
                                       name="new_password_confirm">
                            </div>
                        <?php endif; ?>

                        <!-- 알림 설정 -->
                        <div class="mb-4">
                            <h5 class="mb-3"><?= __('profile.notifications') ?></h5>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="notify_comments" 
                                       name="notify_comments" value="1" 
                                       <?= $user['notify_comments'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notify_comments">
                                    <?= __('profile.notify_comments') ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify_updates" 
                                       name="notify_updates" value="1" 
                                       <?= $user['notify_updates'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notify_updates">
                                    <?= __('profile.notify_updates') ?>
                                </label>
                            </div>
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-between">
                            <a href="/" class="btn btn-outline-secondary">
                                <?= __('cancel') ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= __('save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 계정 삭제 -->
            <div class="card mt-4 border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger"><?= __('profile.delete_account') ?></h5>
                    <p class="card-text"><?= __('profile.delete_warning') ?></p>
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="confirmDeleteAccount()">
                        <?= __('profile.delete_account') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 계정 삭제 확인 모달 -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><?= __('profile.delete_account') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><?= __('profile.delete_confirm') ?></p>
                <p class="text-danger"><?= __('profile.delete_permanent') ?></p>
            </div>
            <div class="modal-footer">
                <form action="/profile/delete" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <?= __('profile.delete_account') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// 프로필 이미지 미리보기
document.getElementById('profile_image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

// 비밀번호 확인
const form = document.querySelector('form');
form.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirm');
    
    if (newPassword && confirmPassword && newPassword.value !== confirmPassword.value) {
        e.preventDefault();
        alert('<?= __('profile.password_mismatch') ?>');
    }
});

// 계정 삭제 확인
function confirmDeleteAccount() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 