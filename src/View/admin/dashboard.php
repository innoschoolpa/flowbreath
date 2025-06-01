<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- 사이드바 -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin">
                            <i class="bi bi-speedometer2"></i>
                            <?= __('admin.dashboard') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users">
                            <i class="bi bi-people"></i>
                            <?= __('admin.users') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/resources">
                            <i class="bi bi-file-text"></i>
                            <?= __('admin.resources') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/tags">
                            <i class="bi bi-tags"></i>
                            <?= __('admin.tags') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/settings">
                            <i class="bi bi-gear"></i>
                            <?= __('admin.settings') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 메인 콘텐츠 -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- 통계 카드 -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('admin.total_users') ?></h5>
                            <p class="card-text display-6"><?= number_format($stats['total_users']) ?></p>
                            <small><?= __('admin.new_today', ['count' => $stats['new_users_today']]) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('admin.total_resources') ?></h5>
                            <p class="card-text display-6"><?= number_format($stats['total_resources']) ?></p>
                            <small><?= __('admin.new_today', ['count' => $stats['new_resources_today']]) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('admin.total_tags') ?></h5>
                            <p class="card-text display-6"><?= number_format($stats['total_tags']) ?></p>
                            <small><?= __('admin.most_used', ['tag' => $stats['most_used_tag']]) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('admin.storage_used') ?></h5>
                            <p class="card-text display-6"><?= $stats['storage_used'] ?></p>
                            <small><?= __('admin.total_files', ['count' => $stats['total_files']]) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 최근 활동 -->
            <div class="row">
                <!-- 최근 사용자 -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= __('admin.recent_users') ?></h5>
                            <a href="/admin/users" class="btn btn-sm btn-outline-primary">
                                <?= __('admin.view_all') ?>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= __('admin.name') ?></th>
                                            <th><?= __('admin.email') ?></th>
                                            <th><?= __('admin.joined_at') ?></th>
                                            <th><?= __('admin.actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= htmlspecialchars($user['profile_image'] ?? '/assets/images/default-avatar.png') ?>" 
                                                         alt="" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                    <?= htmlspecialchars($user['name']) ?>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 최근 리소스 -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= __('admin.recent_resources') ?></h5>
                            <a href="/admin/resources" class="btn btn-sm btn-outline-primary">
                                <?= __('admin.view_all') ?>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= __('admin.title') ?></th>
                                            <th><?= __('admin.author') ?></th>
                                            <th><?= __('admin.created_at') ?></th>
                                            <th><?= __('admin.actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_resources as $resource): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($resource['title']) ?>
                                                    <?php if (!$resource['is_public']): ?>
                                                        <span class="badge bg-warning">비공개</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($resource['author_name']) ?></td>
                                                <td><?= date('Y-m-d', strtotime($resource['created_at'])) ?></td>
                                                <td>
                                                    <a href="/admin/resources/<?= $resource['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 시스템 상태 -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><?= __('admin.system_status') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6><?= __('admin.php_version') ?></h6>
                                    <p class="text-muted"><?= PHP_VERSION ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6><?= __('admin.server_info') ?></h6>
                                    <p class="text-muted"><?= $_SERVER['SERVER_SOFTWARE'] ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6><?= __('admin.database_size') ?></h6>
                                    <p class="text-muted"><?= $stats['database_size'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 