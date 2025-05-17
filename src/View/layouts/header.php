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
    <title><?= $title ?? 'FlowBreath - 호흡 운동' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: #2d3e50;
            padding: 1rem 0;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
            color: #fff !important;
            font-size: 1.5rem;
        }
        .nav-link {
            color: rgba(255,255,255,.85) !important;
            padding: 0.5rem 1rem !important;
            white-space: nowrap;
            font-size: 1rem;
        }
        .nav-link:hover {
            color: #fff !important;
        }
        .navbar-nav {
            flex-wrap: nowrap;
            align-items: center;
        }
        .navbar .container {
            flex-wrap: nowrap;
        }
        .navbar-collapse {
            flex-basis: auto;
        }
        .language-switch {
            margin-left: 1rem;
        }
        .language-switch .nav-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.9rem;
        }
        .auth-buttons {
            margin-left: 1rem;
        }
        .auth-buttons .nav-link {
            padding: 0.25rem 0.75rem !important;
            border-radius: 4px;
        }
        .auth-buttons .nav-link:last-child {
            background-color: rgba(255,255,255,0.1);
        }
        .auth-buttons .nav-link:last-child:hover {
            background-color: rgba(255,255,255,0.2);
        }
        .hero-section {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: #fff;
            padding: 3rem 0 2rem 0;
            text-align: center;
        }
        .search-box {
            max-width: 500px;
            margin: 2rem auto 0 auto;
        }
        .card-resource {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s;
        }
        .card-resource:hover {
            box-shadow: 0 4px 16px rgba(52,152,219,0.15);
        }
        .tag-badge {
            background: #3498db;
            color: #fff;
            border-radius: 20px;
            padding: 0.3em 1em;
            margin: 0.1em;
            font-size: 0.95em;
        }
        .popular-tags .tag-badge {
            background: #2ecc71;
        }
        .resource-meta {
            color: #888;
            font-size: 0.95em;
        }
        .footer {
            background: #2d3e50;
            color: #fff;
            padding: 2rem 0;
            margin-top: auto;
        }
        main {
            flex: 1;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">FlowBreath</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/breathing"><?= __('nav.breathing') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/resources"><?= __('nav.resources') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tags"><?= __('nav.tags') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/api/docs"><?= __('nav.api_docs') ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav language-switch">
                    <li class="nav-item">
                        <a class="nav-link" href="/language/switch/ko">한국어</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/language/switch/en">English</a>
                    </li>
                </ul>
                <ul class="navbar-nav auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/profile"><?= __('nav.my_info') ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout"><?= __('nav.logout') ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><?= __('nav.login') ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register"><?= __('nav.register') ?></a>
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

    <main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 