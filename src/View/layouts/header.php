<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
        .profile-image-container {
            width: 32px;
            height: 32px;
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        .dropdown-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .dropdown-header strong {
            font-size: 0.95rem;
            color: #2d3e50;
            margin-bottom: 0.25rem;
        }
        .user-email {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
            word-break: break-all;
            line-height: 1.2;
        }
        .user-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dropdown-menu {
            min-width: 200px;
        }
        .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .dropdown-item i {
            width: 20px;
            text-align: center;
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
                    <?php
                    // DB에서 최신 사용자 정보 불러오기
                    require_once __DIR__ . '/../../Models/User.php';
                    require_once __DIR__ . '/../../Core/Database.php';
                    $db = \App\Core\Database::getInstance();
                    $userModel = new \App\Models\User($db);
                    $userData = $userModel->findById((int)$_SESSION['user_id']);
                    
                    // DB에서 가져온 사용자 정보 사용
                    $userAvatar = $userData['profile_image'] ?? null;
                    $userName = $userData['name'] ?? '사용자';
                    $userEmail = $userData['email'] ?? '';
                    $isValidAvatar = $userAvatar && filter_var($userAvatar, FILTER_VALIDATE_URL);
                    
                    // 암호화된 데이터 복호화 시도
                    try {
                        require_once __DIR__ . '/../../Core/Encryption.php';
                        $encryption = new \App\Core\Encryption();
                        
                        // 이름 복호화
                        if (strlen($userName) > 50) {
                            error_log("Attempting to decrypt name: " . substr($userName, 0, 20) . "...");
                            $decryptedName = $encryption->decrypt($userName);
                            if ($decryptedName) {
                                error_log("Successfully decrypted name: " . $decryptedName);
                                $userName = $decryptedName;
                            } else {
                                error_log("Failed to decrypt name");
                            }
                        }
                        
                        // 이메일 복호화
                        if (strlen($userEmail) > 50) {
                            error_log("Attempting to decrypt email: " . substr($userEmail, 0, 20) . "...");
                            $decryptedEmail = $encryption->decrypt($userEmail);
                            if ($decryptedEmail && filter_var($decryptedEmail, FILTER_VALIDATE_EMAIL)) {
                                error_log("Successfully decrypted email: " . $decryptedEmail);
                                $userEmail = $decryptedEmail;
                            } else {
                                error_log("Failed to decrypt email or invalid email format");
                            }
                        }
                    } catch (\Exception $e) {
                        error_log("Data decryption error: " . $e->getMessage());
                    }
                    ?>
                    <li class="nav-item dropdown profile-dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-image-container">
                                <?php if ($isValidAvatar): ?>
                                    <img src="<?= htmlspecialchars($userAvatar) ?>" 
                                         alt="<?= htmlspecialchars($userName) ?>" 
                                         class="profile-image"
                                         onerror="this.onerror=null; this.src='/assets/images/default-avatar.png';">
                                <?php else: ?>
                                    <i class="fas fa-user-circle profile-icon"></i>
                                <?php endif; ?>
                            </div>
                            <span class="ms-2 user-name"><?= htmlspecialchars($userName) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileDropdown">
                            <li>
                                <div class="dropdown-header">
                                    <strong class="d-block"><?= htmlspecialchars($userName) ?></strong>
                                    <span class="text-muted small user-email"><?= htmlspecialchars($userEmail) ?></span>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>내 정보</a></li>
                            <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i>설정</a></li>
                            <li><a class="dropdown-item" href="/bookmarks"><i class="fas fa-bookmark me-2"></i>북마크</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/logout" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>로그아웃
                                    </button>
                                </form>
                            </li>
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