<!DOCTYPE html>
<html lang="<?= isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $language->get('common.site_name') ?> - <?= $language->get('home.hero.title') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">FlowBreath.io</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/resources"><?= $language->get('resources.title') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/tags"><?= $language->get('resources.tags') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/api/docs">API 안내</a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a href="/language/switch/ko" class="btn btn-outline-light btn-sm me-2 <?php if(isset($_SESSION['lang']) && $_SESSION['lang']==='ko') echo 'active'; ?>">한국어</a>
                </li>
                <li class="nav-item me-3">
                    <a href="/language/switch/en" class="btn btn-outline-light btn-sm <?php if(isset($_SESSION['lang']) && $_SESSION['lang']==='en') echo 'active'; ?>">English</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="/profile"><i class="fa fa-user"></i> <?= $language->get('common.profile') ?></a></li>
                    <li class="nav-item ms-2"><a class="btn btn-outline-light" href="/logout"><?= $language->get('common.logout') ?></a></li>
                <?php else: ?>
                    <li class="nav-item ms-2"><a class="btn btn-primary me-2" href="/login"><?= $language->get('common.login') ?></a></li>
                    <li class="nav-item ms-3"><a class="btn btn-outline-primary" href="/register"><?= $language->get('common.register') ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3"><?= $language->get('home.hero.title') ?></h1>
        <p class="lead mb-4"><?= $language->get('home.hero.subtitle') ?></p>
        <form class="search-box" method="get" action="/">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="q" placeholder="<?= $language->get('home.hero.search_placeholder') ?>" value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i> <?= $language->get('common.search') ?></button>
            </div>
        </form>
    </div>
</section>

<div class="container mt-5">
    <?php if ($searchQuery !== ''): ?>
        <h4 class="mb-4">
            '<?= htmlspecialchars($searchQuery) ?>' <?= $language->get('common.search') ?>
        </h4>
        <div class="row">
            <?php if (empty($searchResults)): ?>
                <div class="col-12"><div class="alert alert-warning"><?= $language->get('home.recent_resources.no_results') ?></div></div>
            <?php else: foreach ($searchResults as $resource): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-resource h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($resource['title'] ?? $resource['original_title'] ?? '') ?>
                                </a>
                            </h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content'] ?? $resource['original_content'] ?? ''),0,80,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= $language->get('home.recent_resources.title') ?></h4>
            <a href="/resources" class="btn btn-link"><?= $language->get('common.view_all') ?> <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($recentResources as $resource): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card card-resource h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <?php
                            // 유튜브 미리보기: link 또는 content에서 추출
                            $youtubeId = null;
                            if (!empty($resource['link'])) {
                                if (preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^"&?\\/\\s]{11})/', $resource['link'], $matches)) {
                                    $youtubeId = $matches[1];
                                }
                            }
                            if (!$youtubeId && !empty($resource['content'])) {
                                if (preg_match('/https?:\/\/(www\.)?(youtube\\.com|youtu\\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                                    if (preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^"&?\\/\\s]{11})/', $ytMatch[0], $matches)) {
                                        $youtubeId = $matches[1];
                                    }
                                }
                            }
                            if ($youtubeId): ?>
                                <div class="ratio ratio-16x9 mb-3 rounded-3 overflow-hidden shadow-sm" style="max-width:320px; max-height:180px; margin:auto;">
                                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>"
                                            title="YouTube 미리보기"
                                            style="width:100%; height:100%; min-height:120px; border-radius:12px; object-fit:cover;"
                                            allowfullscreen></iframe>
                                </div>
                            <?php endif; ?>
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($resource['title'] ?? $resource['original_title'] ?? '') ?>
                                </a>
                            </h5>
                            <div class="resource-meta mb-2 small text-muted">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-3 flex-grow-1"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content'] ?? $resource['original_content'] ?? ''),0,120,'...')) ?></p>
                            <div>
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="popular-tags mt-5">
            <h5 class="mb-3"><?= $language->get('home.popular_tags.title') ?></h5>
            <?php foreach ($popularTags as $tag): ?>
                <a href="/resources?tags[]=<?= $tag['id'] ?>" class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer mt-5">
    <div class="container text-center">
        <div class="mb-2"><?= $language->get('footer.copyright', ['year' => date('Y')]) ?></div>
        <div><?= $language->get('footer.description') ?></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 