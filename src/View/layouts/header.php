<?php
// src/View/layout/header.php
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>FlowBreath</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #2d3e50; }
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
        .hero-section { background: linear-gradient(135deg, #3498db, #0056b3); color: #fff; padding: 3rem 0 2rem 0; text-align: center; }
        .search-box { max-width: 500px; margin: 2rem auto 0 auto; }
        .card-resource { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: box-shadow 0.2s; }
        .card-resource:hover { box-shadow: 0 4px 16px rgba(52,152,219,0.15); }
        .tag-badge { background: #3498db; color: #fff; border-radius: 20px; padding: 0.3em 1em; margin: 0.1em; font-size: 0.95em; }
        .popular-tags .tag-badge { background: #0056b3; }
        .resource-meta { color: #888; font-size: 0.95em; }
        .footer { background: #2d3e50; color: #fff; padding: 2rem 0; margin-top: 3rem; }
        .profile-dropdown .dropdown-toggle::after {
            display: none;
        }
        .profile-image {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile-icon {
            font-size: 32px;
            color: #fff;
        }
        .nav-link {
            color: rgba(255,255,255,.85) !important;
        }
        .nav-link:hover {
            color: #fff !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">FlowBreath.io</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/resources">자료</a></li>
                <li class="nav-item"><a class="nav-link" href="/tags">태그</a></li>
                <li class="nav-item"><a class="nav-link" href="/api/docs">API 안내</a></li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <div class="btn-group me-3">
                        <a href="/language/switch/ko" class="btn btn-outline-light btn-sm <?= ($_SESSION['lang'] ?? 'ko') === 'ko' ? 'active' : '' ?>">한국어</a>
                        <a href="/language/switch/en" class="btn btn-outline-light btn-sm <?= ($_SESSION['lang'] ?? 'ko') === 'en' ? 'active' : '' ?>">English</a>
                    </div>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (isset($_SESSION['user_avatar']) && $_SESSION['user_avatar']): ?>
                                <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Profile" class="profile-image">
                            <?php else: ?>
                                <i class="fas fa-user-circle profile-icon"></i>
                            <?php endif; ?>
                            <span class="ms-2"><?= htmlspecialchars($_SESSION['user_name'] ?? '내 정보') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>내 정보</a></li>
                            <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i>설정</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>로그아웃</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light me-2" href="/login">로그인</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light" href="/register">회원가입</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<main class="container py-4">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 