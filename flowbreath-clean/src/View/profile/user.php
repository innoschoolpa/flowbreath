<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- 프로필 정보 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if (isset($profile_user['profile_image']) && $profile_user['profile_image']): ?>
                        <img src="<?= htmlspecialchars($profile_user['profile_image']) ?>" alt="Profile" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa fa-user-circle mb-3" style="font-size: 150px; color: #6c757d;"></i>
                    <?php endif; ?>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($profile_user['name']) ?></h4>
                    
                    <?php if (isset($profile_user['bio']) && $profile_user['bio']): ?>
                        <p class="mb-3"><?= nl2br(htmlspecialchars($profile_user['bio'])) ?></p>
                    <?php endif; ?>
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

        <!-- 공개 리소스 목록 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">공개 리소스</h5>

                    <?php if (empty($resources)): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open mb-3" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="text-muted">아직 공개된 리소스가 없습니다.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>제목</th>
                                        <th>조회수</th>
                                        <th>작성일</th>
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
                                            <td><?= number_format($resource['view_count']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($resource['created_at'])) ?></td>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 