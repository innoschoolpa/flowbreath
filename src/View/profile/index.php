<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// DB에서 최신 사용자 정보 불러오기
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../Core/Database.php';
$db = \App\Core\Database::getInstance();
$userModel = new \App\Models\User($db);
$userData = $userModel->findById((int)$_SESSION['user_id']);

// 사용자 데이터가 없거나 null인 경우 기본값 설정
$user = [
    'id' => $_SESSION['user_id'],
    'name' => $userData['name'] ?? $_SESSION['user_name'] ?? __('profile.name'),
    'email' => $userData['email'] ?? $_SESSION['user_email'] ?? '',
    'profile_image' => $userData['profile_image'] ?? null,
    'bio' => $userData['bio'] ?? '',
    'social_links' => $userData['social_links'] ?? ''
];

// 디버그 로그 추가
error_log("User data from DB: " . json_encode($userData));
error_log("Final user data: " . json_encode($user));

// 활동 통계 초기화
$stats = [
    'total_resources' => 0,
    'total_likes' => 0,
    'total_views' => 0,
    'total_comments' => 0,
    'recent_activity' => [],
    'popular_resources' => []
];

// 리소스 목록 초기화
$resources = [];
?>

<div class="container py-5">
    <div class="row">
        <!-- 프로필 정보 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (isset($user['profile_image']) && $user['profile_image']): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= __('profile.profile_image') ?>" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fa fa-user-circle" style="font-size: 150px; color: #6c757d;"></i>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" data-bs-toggle="modal" data-bs-target="#profileImageModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    
                    <?php if (isset($user['bio']) && $user['bio']): ?>
                        <p class="mb-3"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-3"><?= __('profile.edit.bio_placeholder') ?></p>
                    <?php endif; ?>

                    <!-- 프로필 완성도 -->
                    <?php
                    $completion = 0;
                    $total = 0;
                    
                    // 프로필 이미지
                    if (!empty($user['profile_image'])) {
                        $completion += 20;
                    }
                    $total += 20;
                    
                    // 이름
                    if (!empty($user['name'])) {
                        $completion += 20;
                    }
                    $total += 20;
                    
                    // 자기소개
                    if (!empty($user['bio'])) {
                        $completion += 20;
                    }
                    $total += 20;
                    
                    // 소셜 미디어 링크
                    if (!empty($user['social_links'])) {
                        $completion += 20;
                    }
                    $total += 20;
                    
                    // 활동 통계
                    if ($stats['total_resources'] > 0) {
                        $completion += 20;
                    }
                    $total += 20;
                    
                    $percentage = ($completion / $total) * 100;
                    ?>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">프로필 완성도</span>
                            <span class="text-primary"><?= round($percentage) ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%" 
                                 aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- 소셜 미디어 링크 -->
                    <div class="social-links mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#socialLinksModal">
                            <i class="fas fa-plus"></i> 소셜 링크 추가
                        </button>
                        <?php if (!empty($user['social_links'])): ?>
                            <?php foreach (json_decode($user['social_links'], true) as $platform => $url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                                    <i class="fab fa-<?= strtolower($platform) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#profileEditModal">
                            <i class="fas fa-edit me-2"></i><?= __('profile.edit.title') ?>
                        </button>
                        <a href="/settings" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i><?= __('profile.edit.account_settings') ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 활동 통계 -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= __('profile.stats.title') ?></h5>
                    <div class="row text-center">
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_resources']) ?></h4>
                            <small class="text-muted"><?= __('profile.stats.resources') ?></small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_likes']) ?></h4>
                            <small class="text-muted"><?= __('profile.stats.likes') ?></small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_views']) ?></h4>
                            <small class="text-muted"><?= __('profile.stats.views') ?></small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_comments']) ?></h4>
                            <small class="text-muted"><?= __('profile.stats.comments') ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 최근 활동 -->
            <?php if (!empty($stats['recent_activity'])): ?>
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">최근 활동</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['recent_activity'] as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <?php if ($activity['type'] === 'resource'): ?>
                                            <i class="fas fa-file-alt text-primary me-2"></i>
                                            <a href="/resources/view/<?= $activity['id'] ?>"><?= htmlspecialchars($activity['title']) ?></a>
                                        <?php else: ?>
                                            <i class="fas fa-comment text-success me-2"></i>
                                            <?= htmlspecialchars(mb_substr($activity['title'], 0, 50)) ?>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted"><?= date('Y-m-d', strtotime($activity['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 인기 리소스 -->
            <?php if (!empty($stats['popular_resources'])): ?>
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">인기 리소스</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['popular_resources'] as $resource): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <a href="/resources/view/<?= $resource['id'] ?>"><?= htmlspecialchars($resource['title']) ?></a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-heart text-danger"></i> <?= number_format($resource['like_count']) ?>
                                        <i class="fas fa-comment text-success ms-2"></i> <?= number_format($resource['comment_count']) ?>
                                        <i class="fas fa-eye text-primary ms-2"></i> <?= number_format($resource['total_views']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- 리소스 목록 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">내 리소스</h5>
                        <a href="/resources/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>새 리소스
                        </a>
                    </div>

                    <?php if (empty($resources)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open mb-3" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="text-muted">아직 등록한 리소스가 없습니다.</p>
                            <a href="/resources/create" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>첫 리소스 등록하기
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>제목</th>
                                        <th>상태</th>
                                        <th>조회수</th>
                                        <th>작성일</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resources as $resource): ?>
                                        <tr>
                                            <td>
                                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($resource['title']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= $resource['visibility'] === 'public' ? '공개' : '비공개' ?>
                                            </td>
                                            <td><?= number_format($resource['view_count']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($resource['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/resources/<?= $resource['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteResource(<?= $resource['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 프로필 이미지 수정 모달 -->
<div class="modal fade" id="profileImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('profile.edit.image_change') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileImageForm" action="/profile/update-image" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profileImage" class="form-label"><?= __('profile.edit.select_image') ?></label>
                        <input type="file" class="form-control" id="profileImage" name="profile_image" accept="image/*">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary"><?= __('profile.edit.change') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 프로필 수정 모달 -->
<div class="modal fade" id="profileEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">프로필 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileEditForm" action="/profile/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">이름</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">자기소개</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio']) ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">저장하기</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 소셜 링크 추가 모달 -->
<div class="modal fade" id="socialLinksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">소셜 링크 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="socialLinksForm" action="/profile/update-social" method="POST">
                    <div class="mb-3">
                        <label for="platform" class="form-label">플랫폼</label>
                        <select class="form-select" id="platform" name="platform" required>
                            <option value="">선택하세요</option>
                            <option value="github">GitHub</option>
                            <option value="twitter">Twitter</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="instagram">Instagram</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">추가하기</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// 프로필 수정 폼 제출
document.getElementById('profileEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/profile/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 메시지 표시
            alert(data.message);
            // 페이지 새로고침
            window.location.reload();
        } else {
            // 오류 메시지 표시
            alert(data.error || '프로필 업데이트 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('프로필 업데이트 중 오류가 발생했습니다.');
    });
});

// 프로필 이미지 업로드
document.getElementById('profileImageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/profile/update-image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '이미지 업로드에 실패했습니다.');
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
    });
});

// 소셜 링크 추가
document.getElementById('socialLinksForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/profile/update-social', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '소셜 링크 추가에 실패했습니다.');
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
    });
});

// 프로필 이미지 미리보기
document.getElementById('profileImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.profile-image-preview');
            if (preview) {
                preview.src = e.target.result;
            }
        }
        reader.readAsDataURL(file);
    }
});

// 리소스 삭제
function deleteResource(id) {
    if (confirm('정말로 이 리소스를 삭제하시겠습니까?')) {
        fetch(`/resources/${id}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || '리소스 삭제에 실패했습니다.');
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다.');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 