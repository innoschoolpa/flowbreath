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
        <!-- 프로필 정보 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if (isset($user['profile_image']) && $user['profile_image']): ?>
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa fa-user-circle mb-3" style="font-size: 150px; color: #6c757d;"></i>
                    <?php endif; ?>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    
                    <?php if (isset($user['bio']) && $user['bio']): ?>
                        <p class="mb-3"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
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
                    <?php if (!empty($user['social_links'])): ?>
                        <div class="social-links mb-3">
                            <?php foreach (json_decode($user['social_links'], true) as $platform => $url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                                    <i class="fab fa-<?= strtolower($platform) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a href="/settings" class="btn btn-outline-primary">
                        <i class="fa fa-cog me-2"></i>설정
                    </a>
                </div>
            </div>

            <!-- 활동 통계 -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">활동 통계</h5>
                    <div class="row text-center">
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_resources']) ?></h4>
                            <small class="text-muted">리소스</small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_likes']) ?></h4>
                            <small class="text-muted">좋아요</small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_views']) ?></h4>
                            <small class="text-muted">조회수</small>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= number_format($stats['total_comments']) ?></h4>
                            <small class="text-muted">댓글</small>
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
                                            <a href="/resources/<?= $activity['id'] ?>"><?= htmlspecialchars($activity['title']) ?></a>
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
                                        <a href="/resources/<?= $resource['id'] ?>"><?= htmlspecialchars($resource['title']) ?></a>
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
                            <i class="fa fa-plus me-2"></i>새 리소스
                        </a>
                    </div>

                    <?php if (empty($resources)): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open mb-3" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="text-muted">아직 등록한 리소스가 없습니다.</p>
                            <a href="/resources/create" class="btn btn-primary mt-3">
                                <i class="fa fa-plus me-2"></i>첫 리소스 등록하기
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
                                                <?php if ($resource['is_public']): ?>
                                                    <span class="badge bg-success">공개</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">비공개</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($resource['view_count']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($resource['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/resources/<?= $resource['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteResource(<?= $resource['id'] ?>)">
                                                        <i class="fa fa-trash"></i>
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

<script>
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