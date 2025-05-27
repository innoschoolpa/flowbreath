<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// DB에서 최신 사용자 정보 불러오기
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../Models/Resource.php';
require_once __DIR__ . '/../../Core/Database.php';
$db = \App\Core\Database::getInstance();
$userModel = new \App\Models\User($db);
$resourceModel = new \App\Models\Resource($db);
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
    'popular_resources' => [],
    'public_resources' => 0
];

// 개선된 쿼리로 내 리소스 불러오기
$lang = $_SESSION['lang'] ?? 'ko';
$resources = $resourceModel->findByUserIdWithDetails($user['id'], $lang);
error_log("Resources data: " . json_encode($resources));

$stats['total_resources'] = $resourceModel->countByUserId($user['id']);
$stats['public_resources'] = $resourceModel->countPublicByUserId($user['id']);
?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --hover-bg: rgba(255, 255, 255, 0.1);
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.card-title {
    color: var(--text-color);
}

h1, h2, h3, h4, h5, h6 {
    color: var(--text-color);
}

.text-muted {
    color: var(--secondary-color) !important;
}

.list-group-item {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

.list-group-item:hover {
    background-color: var(--hover-bg);
}

.table {
    color: var(--text-color);
}

.table thead th {
    border-bottom-color: var(--border-color);
    color: var(--secondary-color);
}

.table td {
    border-top-color: var(--border-color);
}

.table-hover tbody tr:hover {
    background-color: var(--hover-bg);
}

.modal-content {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

.modal-header {
    border-bottom-color: var(--border-color);
}

.modal-footer {
    border-top-color: var(--border-color);
}

.form-control, .form-select {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-color);
}

.form-control:focus, .form-select:focus {
    background-color: var(--card-bg);
    border-color: var(--accent-color);
    color: var(--text-color);
}

.btn-outline-secondary {
    color: var(--secondary-color);
    border-color: var(--secondary-color);
}

.btn-outline-secondary:hover {
    background-color: var(--secondary-color);
    color: var(--text-color);
}

.progress {
    background-color: var(--border-color);
}

.progress-bar {
    background-color: var(--accent-color);
}

.social-links .btn-outline-secondary {
    color: var(--text-color);
    border-color: var(--border-color);
}

.social-links .btn-outline-secondary:hover {
    background-color: var(--hover-bg);
    border-color: var(--accent-color);
}

/* 프로필 이미지 아이콘 색상 */
.fa-user-circle {
    color: var(--secondary-color) !important;
}

/* 활동 아이콘 색상 */
.text-primary {
    color: var(--accent-color) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.text-danger {
    color: var(--error-color) !important;
}

/* 링크 색상 */
a {
    color: var(--accent-color);
}

a:hover {
    color: #0284c7;
}

/* 테이블 링크 */
.table a {
    color: var(--text-color);
    text-decoration: none;
}

.table a:hover {
    color: var(--accent-color);
}

/* 모달 닫기 버튼 */
.btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card {
        margin-bottom: 1rem;
    }
}

.table.my-resource-table,
.table.my-resource-table thead th,
.table.my-resource-table tbody tr,
.table.my-resource-table td,
.table.my-resource-table th,
.table.my-resource-table .badge,
.table.my-resource-table .btn-outline-primary,
.table.my-resource-table .btn-outline-danger {
    background-color: #1e293b !important;
    color: #f1f5f9 !important;
    border-color: #334155 !important;
}
.table.my-resource-table thead th {
    background-color: #223046 !important;
    color: #60a5fa !important;
    border-bottom: 2px solid #334155 !important;
}
.table.my-resource-table tbody tr:hover {
    background-color: rgba(59,130,246,0.08) !important;
}
.table.my-resource-table .badge {
    background: #334155 !important;
    color: #e2e8f0 !important;
}
.table.my-resource-table .btn-outline-primary {
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
}
.table.my-resource-table .btn-outline-primary:hover {
    background: #3b82f6 !important;
    color: #fff !important;
}
.table.my-resource-table .btn-outline-danger {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
}
.table.my-resource-table .btn-outline-danger:hover {
    background: #ef4444 !important;
    color: #fff !important;
}

.profile-header, .profile-header h4, .profile-header p, .profile-header .mb-3, .profile-header .profile-name, .profile-header .profile-email, .profile-header .mb-1, .profile-header .mb-3, .profile-header .fw-bold, .profile-header ul.list-unstyled li {
    color: #fff !important;
}
.profile-header .text-muted {
    color: #cbd5e1 !important;
}
.profile-header .text-primary {
    color: #60a5fa !important;
}
</style>

<div class="container py-5">
    <div class="row">
        <!-- 프로필 정보 -->
        <div class="col-md-4">
            <div class="card profile-header">
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
                    $total = 5;
                    $checklist = [
                        'profile_image' => !empty($user['profile_image']),
                        'name' => !empty($user['name']),
                        'bio' => !empty($user['bio']),
                        'social_links' => !empty($user['social_links']),
                        'resource' => ($stats['total_resources'] ?? 0) > 0,
                    ];
                    foreach ($checklist as $item) {
                        if ($item) $completion++;
                    }
                    $percentage = ($completion / $total) * 100;
                    ?>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">프로필 완성도</span>
                            <span class="text-primary fw-bold"><?= round($percentage) ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%"
                                 aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <ul class="list-unstyled mt-2 mb-0" style="font-size:0.97em;">
                            <li>
                                <?= $checklist['profile_image'] ? '✅' : '⬜' ?> 프로필 이미지
                            </li>
                            <li>
                                <?= $checklist['name'] ? '✅' : '⬜' ?> 이름
                            </li>
                            <li>
                                <?= $checklist['bio'] ? '✅' : '⬜' ?> 자기소개
                            </li>
                            <li>
                                <?= $checklist['social_links'] ? '✅' : '⬜' ?> 소셜 링크
                            </li>
                            <li>
                                <?= $checklist['resource'] ? '✅' : '⬜' ?> 리소스 등록
                            </li>
                        </ul>
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
                            <h4 class="mb-1">
                                <?= number_format($stats['total_resources']) ?>
                                <span class="fs-6 text-muted">/ <?= number_format($stats['public_resources'] ?? 0) ?> 공개</span>
                            </h4>
                            <small class="text-muted">전체 리소스</small>
                            <div class="progress mt-2" style="height: 6px;">
                                <?php
                                $public = (int)($stats['public_resources'] ?? 0);
                                $total = (int)($stats['total_resources'] ?? 1);
                                $percent = $total > 0 ? ($public / $total) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">공개 <?= $public ?> / 비공개 <?= $total - $public ?></small>
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
                            <table class="table my-resource-table table-hover align-middle">
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
                                                <a href="/resources/view/<?= $resource['id'] ?>">
                                                    <?= htmlspecialchars($resource['title'] ?? '') ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge">
                                                    <?= ($resource['visibility'] ?? 'public') === 'public' ? '공개' : '비공개' ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($resource['view_count'] ?? 0) ?></td>
                                            <td><?= date('Y-m-d', strtotime($resource['created_at'] ?? 'now')) ?></td>
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