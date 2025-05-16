<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowBreath - 호흡 운동</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: #2d3e50;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
            color: #fff !important;
        }
        .nav-link {
            color: rgba(255,255,255,.85) !important;
            padding: 0.5rem 1rem !important;
            white-space: nowrap;
        }
        .nav-link:hover {
            color: #fff !important;
        }
        #breathingCircle {
            transition: all 1s ease-in-out;
        }
        .navbar-nav {
            flex-wrap: nowrap;
        }
        .navbar .container {
            flex-wrap: nowrap;
        }
        .navbar-collapse {
            flex-basis: auto;
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
                        <a class="nav-link" href="/breathing">호흡 운동</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/resources">자료</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tags">태그</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/api/docs">API 안내</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/language/switch/ko">한국어</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/language/switch/en">English</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">로그인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">회원가입</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">로그인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">회원가입</a>
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

    <main class="py-4">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 