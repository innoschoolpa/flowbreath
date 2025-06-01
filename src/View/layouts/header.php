<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../helpers/lang.php';

// Get current language
$currentLang = $_SESSION['lang'] ?? 'ko';

// Get current page URL
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Default meta description
$metaDescription = $currentLang === 'ko' 
    ? 'FlowBreath.io - 단전 호흡과 기 수련을 위한 최고의 플랫폼. 단전 호흡법, 기 수련, 복식 호흡, 4-7-8 호흡법, 박스 호흡 등 다양한 호흡 운동을 제공합니다. 건강, 명상, 치료를 위한 완벽한 호흡 솔루션.'
    : 'FlowBreath.io - The ultimate platform for dantian breathing and qi training. Practice dantian breathing, qi cultivation, diaphragmatic breathing, 4-7-8 breathing, box breathing, and more. Your complete breathing solution for health, meditation, and therapy.';

// Default meta keywords
$metaKeywords = $currentLang === 'ko'
    ? '단전 호흡, 기 수련, 단전 호흡법, 기 공, 호흡 운동, 복식 호흡, 4-7-8 호흡법, 박스 호흡, 명상, 건강, 치료, FlowBreath'
    : 'dantian breathing, qi training, dantian breathing method, qigong, breathing exercise, diaphragmatic breathing, 4-7-8 breathing, box breathing, meditation, health, therapy, FlowBreath';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FlowBreath - 호흡 운동' ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $metaDescription ?>">
    <meta name="keywords" content="<?= $metaKeywords ?>">
    <meta name="author" content="FlowBreath">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">

    <!-- Open Graph Tags for Social Media -->
    <meta property="og:title" content="<?= $title ?? 'FlowBreath - 호흡 운동' ?>">
    <meta property="og:description" content="<?= $metaDescription ?>">
    <meta property="og:image" content="/assets/images/og-image.jpg">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="FlowBreath">
    <meta property="og:locale" content="<?= $currentLang === 'ko' ? 'ko_KR' : 'en_US' ?>">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?? 'FlowBreath - 호흡 운동' ?>">
    <meta name="twitter:description" content="<?= $metaDescription ?>">
    <meta name="twitter:image" content="/assets/images/og-image.jpg">

    <!-- Schema.org markup for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "FlowBreath",
        "url": "<?= htmlspecialchars($currentUrl) ?>",
        "description": <?= json_encode($metaDescription) ?>,
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= htmlspecialchars($currentUrl) ?>?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-30RDJ93Z7Z"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-30RDJ93Z7Z', {
            'send_page_view': true,
            'cookie_flags': 'SameSite=None;Secure',
            'anonymize_ip': true,
            'allow_google_signals': false,
            'allow_ad_personalization_signals': false,
            'page_location': '<?= htmlspecialchars($currentUrl) ?>',
            'page_title': '<?= htmlspecialchars($title ?? 'FlowBreath - 호흡 운동') ?>',
            'page_referrer': document.referrer
        });
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            display: inline-flex;
            align-items: center;
            background: linear-gradient(90deg, #1e40af 60%, #3b82f6 100%);
            color: #e2e8f0;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.12);
            border: 1px solid #3b82f6;
            transition: all 0.3s ease;
            margin-bottom: 0.3rem;
        }
        .tag-badge:hover {
            background: linear-gradient(90deg, #2563eb 60%, #1d4ed8 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
            text-decoration: none;
        }
        .tag-badge i {
            margin-right: 0.5rem;
            font-size: 0.95em;
            color: #93c5fd;
        }
        .tag-count {
            background: rgba(37, 99, 235, 0.15);
            color: #93c5fd;
            padding: 0.22rem 0.7rem;
            border-radius: 12px;
            font-size: 0.82em;
            margin-left: 0.7rem;
            font-weight: 400;
        }
        .popular-tags .tag-badge {
            background: linear-gradient(90deg, #1e40af 60%, #3b82f6 100%);
            color: #e2e8f0;
            border: 1px solid #3b82f6;
        }
        .popular-tags .tag-badge:hover {
            background: linear-gradient(90deg, #2563eb 60%, #1d4ed8 100%);
            color: #fff;
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
            --input-bg: rgba(255, 255, 255, 0.1);
            --input-border: var(--border-color);
            --input-focus-bg: rgba(255, 255, 255, 0.15);
            --input-focus-border: var(--accent-color);
        }
        body {
            background-color: var(--background-color) !important;
            color: var(--text-color) !important;
        }
        .card, .card-body, .card-title, .card-header, .card-footer {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
            border-color: var(--border-color) !important;
        }
        .form-control {
            background-color: var(--input-bg) !important;
            border-color: var(--input-border) !important;
            color: var(--text-color) !important;
        }
        .form-control:focus {
            background-color: var(--input-focus-bg) !important;
            border-color: var(--input-focus-border) !important;
            color: var(--text-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25) !important;
        }
        .form-control::placeholder {
            color: var(--text-color) !important;
            opacity: 0.5 !important;
        }
        .form-label {
            color: var(--text-color) !important;
        }
        .btn-primary {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: var(--text-color) !important;
        }
        .btn-primary:hover {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            color: var(--text-color) !important;
        }
        .btn-outline-secondary {
            color: var(--text-color) !important;
            border-color: var(--secondary-color) !important;
        }
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color) !important;
            color: var(--background-color) !important;
        }
        .navbar, .footer {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
        }
        .dropdown-menu {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
        }
        .dropdown-item {
            color: var(--text-color) !important;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: var(--input-focus-bg) !important;
            color: var(--accent-color) !important;
        }
        .badge.bg-light {
            background-color: var(--secondary-color) !important;
            color: var(--text-color) !important;
        }
        .text-muted {
            color: #94a3b8 !important;
        }
        .alert {
            background-color: #1e293b !important;
            color: var(--text-color) !important;
            border-color: var(--border-color) !important;
        }
        .rounded-circle {
            background-color: var(--card-bg) !important;
        }
        .input-group-text {
            background-color: var(--input-bg) !important;
            color: var(--text-color) !important;
            border-color: var(--input-border) !important;
        }
        .table {
            color: var(--text-color) !important;
        }
        .table th, .table td {
            background-color: var(--card-bg) !important;
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
                        <a class="nav-link" href="/diary"><?= __('nav.diary') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tags"><?= __('nav.tags') ?></a>
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
</body>
</html> 