<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/"><?= $language->get('common.site_name') ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/resources"><?= $language->get('resources.title') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/tags"><?= $language->get('resources.tags') ?></a></li>
                <li class="nav-item"><a class="nav-link" href="/api/docs">API 안내</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <a href="/language/switch/ko" class="btn btn-outline-light btn-sm me-2 <?= $language->getCurrentLang() === 'ko' ? 'active' : '' ?>">한국어</a>
                    <a href="/language/switch/en" class="btn btn-outline-light btn-sm <?= $language->getCurrentLang() === 'en' ? 'active' : '' ?>">English</a>
                </div>
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="/profile"><i class="fa fa-user"></i> <?= $language->get('common.profile') ?></a></li>
                        <li class="nav-item ms-2"><a class="btn btn-outline-light" href="/logout"><?= $language->get('common.logout') ?></a></li>
                    <?php else: ?>
                        <li class="nav-item ms-2"><a class="btn btn-primary me-2" href="/login"><?= $language->get('common.login') ?></a></li>
                        <li class="nav-item"><a class="btn btn-outline-primary" href="/register"><?= $language->get('common.register') ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav> 