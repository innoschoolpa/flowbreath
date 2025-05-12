<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'profile_image' => $_SESSION['user_avatar'] ?? null,
    'bio' => $_SESSION['user_bio'] ?? '',
    'social_links' => $_SESSION['user_social_links'] ?? '',
];
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <?php if (isset($user['profile_image']) && $user['profile_image']): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fa fa-user-circle" style="font-size: 150px; color: #6c757d;"></i>
                        <?php endif; ?>
                        <div class="mt-3">
                            <h4><?= htmlspecialchars($user['name']) ?></h4>
                            <p class="text-muted mb-1"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fa fa-user me-2"></i>프로필
                        </a>
                        <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa fa-lock me-2"></i>비밀번호
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fa fa-bell me-2"></i>알림 설정
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content">
                <!-- 프로필 설정 -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">프로필 설정</h5>
                            <form id="profileForm" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">프로필 이미지</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                    <div class="form-text">최대 5MB, JPEG, PNG, GIF 형식만 가능</div>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">이름</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">자기소개</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                </div>

                                <!-- 소셜 미디어 링크 -->
                                <div class="mb-3">
                                    <label class="form-label">소셜 미디어 링크</label>
                                    <?php
                                    $socialLinks = !empty($user['social_links']) ? json_decode($user['social_links'], true) : [];
                                    $platforms = [
                                        'github' => 'GitHub',
                                        'twitter' => 'Twitter',
                                        'linkedin' => 'LinkedIn',
                                        'facebook' => 'Facebook',
                                        'instagram' => 'Instagram'
                                    ];
                                    foreach ($platforms as $platform => $label):
                                    ?>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text">
                                                <i class="fab fa-<?= $platform ?>"></i>
                                            </span>
                                            <input type="url" 
                                                   class="form-control" 
                                                   name="social_links[<?= $platform ?>]" 
                                                   placeholder="<?= $label ?> URL"
                                                   value="<?= htmlspecialchars($socialLinks[$platform] ?? '') ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="submit" class="btn btn-primary">저장</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 비밀번호 설정 -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">비밀번호 변경</h5>
                            <form id="passwordForm">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">현재 비밀번호</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">새 비밀번호</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">새 비밀번호 확인</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <button type="submit" class="btn btn-primary">비밀번호 변경</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 알림 설정 -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">알림 설정</h5>
                            <form id="notificationsForm">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= ($user['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email_notifications">이메일 알림</label>
                                    </div>
                                    <div class="form-text">새로운 소식이나 업데이트를 이메일로 받습니다.</div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="push_notifications" name="push_notifications" <?= ($user['push_notifications'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="push_notifications">푸시 알림</label>
                                    </div>
                                    <div class="form-text">브라우저 푸시 알림을 받습니다.</div>
                                </div>

                                <button type="submit" class="btn btn-primary">설정 저장</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 프로필 업데이트
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/settings/update-profile', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('프로필이 업데이트되었습니다.');
                location.reload();
            } else {
                alert(data.error || '프로필 업데이트에 실패했습니다.');
            }
        } catch (error) {
            alert('오류가 발생했습니다.');
        }
    });

    // 비밀번호 변경
    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/settings/update-password', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('비밀번호가 변경되었습니다.');
                this.reset();
            } else {
                alert(data.error || '비밀번호 변경에 실패했습니다.');
            }
        } catch (error) {
            alert('오류가 발생했습니다.');
        }
    });

    // 알림 설정 업데이트
    document.getElementById('notificationsForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/settings/update-notifications', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('알림 설정이 업데이트되었습니다.');
            } else {
                alert(data.error || '알림 설정 업데이트에 실패했습니다.');
            }
        } catch (error) {
            alert('오류가 발생했습니다.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 