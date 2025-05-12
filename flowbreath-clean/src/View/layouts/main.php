<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/"><?= $this->lang('site.name') ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/"><?= $this->lang('site.home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/resources"><?= $this->lang('site.resources') ?></a>
                </li>
            </ul>
            <div class="d-flex">
                <a href="/language/switch/ko" class="btn btn-outline-primary me-2 <?= $this->lang('site.current_lang') === 'ko' ? 'active' : '' ?>">한국어</a>
                <a href="/language/switch/en" class="btn btn-outline-primary <?= $this->lang('site.current_lang') === 'en' ? 'active' : '' ?>">English</a>
            </div>
        </div>
    </div>
</nav> 