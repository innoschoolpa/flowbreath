<!DOCTYPE html>
<html lang="<?= get_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('site_name') ?></title>
    
    <!-- Preload critical fonts -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/fonts/bootstrap-icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/fonts.css" rel="stylesheet">
    <style>
        /* Add font-display: swap to all font declarations */
        @font-face {
            font-family: 'Bootstrap Icons';
            font-display: swap;
            src: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/fonts/bootstrap-icons.woff2') format('woff2');
        }
        
        @font-face {
            font-family: 'Font Awesome';
            font-display: swap;
            src: url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/webfonts/fa-solid-900.woff2') format('woff2');
        }
        
        body { background: #f8f9fa; }
        .navbar { background: #2d3e50; }
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
        .hero-section { background: linear-gradient(135deg, #3498db, #0056b3); color: #fff; padding: 3rem 0 2rem 0; text-align: center; }
        .search-box { max-width: 500px; margin: 2rem auto 0 auto; box-shadow: 0 2px 12px rgba(52,152,219,0.10); border-radius: 18px; background: #fff; padding: 1.5rem 1rem; }
        .search-box .form-control { border-radius: 12px 0 0 12px; }
        .search-box .btn { border-radius: 0 12px 12px 0; }
        .card-resource {
            border: none;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: transform 0.18s, box-shadow 0.18s;
            min-height: 370px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: stretch;
        }
        .card-resource:hover {
            transform: translateY(-4px) scale(1.025);
            box-shadow: 0 8px 24px rgba(52,152,219,0.18);
        }
        .ratio iframe {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(52,152,219,0.10);
        }
        .card-title a {
            color: #222;
            font-weight: 600;
        }
        .card-title a:hover {
            color: #3498db;
            text-decoration: underline;
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
            font-size: 0.93em;
            color: #888;
        }
        .card-text {
            margin-bottom: 1.2rem;
            flex-grow: 1;
        }
        .row.g-4 { --bs-gutter-x: 2rem; --bs-gutter-y: 2rem; }
        @media (max-width: 991px) {
            .col-lg-4, .col-lg-6 { flex: 0 0 100%; max-width: 100%; }
        }
        @media (max-width: 767px) {
            .card-resource { min-height: 320px; }
            .search-box { padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/"><?= __('site_name') ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/breathing"><?= __('menu_breathing_exercises') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/resources"><?= __('menu_resources') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tags"><?= __('menu_tags') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/api/docs"><?= __('menu_api_guide') ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a href="/language/switch/ko" class="btn btn-outline-light btn-sm me-2 <?php if(isset($_SESSION['lang']) && $_SESSION['lang']==='ko') echo 'active'; ?>">한국어</a>
                    </li>
                    <li class="nav-item me-3">
                        <a href="/language/switch/en" class="btn btn-outline-light btn-sm <?php if(isset($_SESSION['lang']) && $_SESSION['lang']==='en') echo 'active'; ?>">English</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="/profile"><i class="fa fa-user"></i> <?= __('login') ?></a></li>
                        <li class="nav-item ms-2"><a class="btn btn-outline-light" href="/logout"><?= __('logout') ?></a></li>
                    <?php else: ?>
                        <li class="nav-item ms-2"><a class="btn btn-primary me-2" href="/login"><?= __('login') ?></a></li>
                        <li class="nav-item ms-3"><a class="btn btn-outline-primary" href="/register"><?= __('signup') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <div class="text-center mb-5">
            <h1 class="display-4"><?= __('search_title') ?></h1>
            <p class="lead"><?= __('search_subtitle') ?></p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="<?= __('search_placeholder') ?>">
                        <button class="btn btn-primary" type="button"><?= __('search') ?></button>
                    </div>
                </div>
            </div>
        </div>

        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= __('recent_resources') ?></h2>
                <a href="/resources" class="btn btn-outline-primary"><?= __('view_all') ?></a>
            </div>
            <!-- Recent resources will be loaded here -->
        </section>
    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p><?= __('footer_description') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><?= __('footer_copyright') ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 