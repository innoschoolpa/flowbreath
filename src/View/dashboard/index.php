<?php
// src/View/dashboard/index.php
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5">
    <!-- 네비게이션 바 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary rounded mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/">FlowBreath.io</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/resources">리소스 관리</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">로그아웃</a>
                    </li>
                </ul>
            </div>
            <span class="navbar-text ms-3 text-white">
                <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?> 님
            </span>
        </div>
    </nav>

    <!-- 대시보드 카드 -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">등록된 리소스</h5>
                    <p class="display-4 fw-bold text-primary"><?php echo $resource_count ?? 0; ?></p>
                    <a href="/resources" class="btn btn-outline-primary btn-sm">리소스 관리</a>
                </div>
            </div>
        </div>
        <!-- 최근 리소스 카드 -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">최근 등록된 리소스</h5>
                    <?php if (!empty($recent_resources)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_resources as $res): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <a href="/resources/view/<?php echo $res['resource_id']; ?>">
                                            <?php echo htmlspecialchars($res['title']); ?>
                                        </a>
                                        <small class="text-muted ms-2"><?php echo htmlspecialchars($res['date_added']); ?></small>
                                    </span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($res['source_type']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">최근 등록된 리소스가 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 