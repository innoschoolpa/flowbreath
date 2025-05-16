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
        .search-box { max-width: 500px; margin: 2rem auto 0 auto; }
        .card-resource { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: box-shadow 0.2s; }
        .card-resource:hover { box-shadow: 0 4px 16px rgba(52,152,219,0.15); }
        .tag-badge { background: #3498db; color: #fff; border-radius: 20px; padding: 0.3em 1em; margin: 0.1em; font-size: 0.95em; }
        .popular-tags .tag-badge { background: #0056b3; }
        .resource-meta { color: #888; font-size: 0.95em; }
        .footer { background: #2d3e50; color: #fff; padding: 2rem 0; margin-top: 3rem; }
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
                            <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title']) ?></h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,80,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="/resources/view/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm"><?= $language->get('common.read_more') ?></a>
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
        <div class="row">
            <?php foreach ($recentResources as $resource): ?>
                <div class="col-md-6 col-lg-6 mb-4">
                    <div class="card card-resource h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title']) ?></h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,120,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="/resources/view/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm"><?= $language->get('common.read_more') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="popular-tags mt-5">
            <h5 class="mb-3"><?= $language->get('home.popular_tags.title') ?></h5>
            <?php foreach ($popularTags as $tag): ?>
                <a href="/tags/<?= urlencode($tag['name']) ?>" class="tag-badge">#<?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? '') : $tag) ?></a>
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