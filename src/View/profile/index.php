<?php require_once __DIR__ . '/../layouts/header.php'; ?>

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

                    <a href="/settings" class="btn btn-outline-primary">
                        <i class="fa fa-cog me-2"></i>설정
                    </a>
                </div>
            </div>

            <!-- 통계 정보 -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">활동 통계</h5>
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-1"><?= $stats['total_resources'] ?></h4>
                            <small class="text-muted">리소스</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1"><?= $stats['total_likes'] ?></h4>
                            <small class="text-muted">좋아요</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1"><?= $stats['total_views'] ?></h4>
                            <small class="text-muted">조회수</small>
                        </div>
                    </div>
                </div>
            </div>
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
                                        <th>좋아요</th>
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
                                            <td><?= number_format($resource['like_count']) ?></td>
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