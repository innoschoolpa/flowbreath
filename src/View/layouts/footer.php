<footer class="footer mt-5 py-4 bg-dark text-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><?= $language->get('common.site_name') ?></h5>
                <p class="text-muted"><?= $language->get('footer.description') ?></p>
            </div>
            <div class="col-md-4 mb-3">
                <h5><?= $language->get('footer.quick_links') ?></h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-muted"><?= $language->get('common.home') ?></a></li>
                    <li><a href="/resources" class="text-muted"><?= $language->get('resources.title') ?></a></li>
                    <li><a href="/tags" class="text-muted"><?= $language->get('resources.tags') ?></a></li>
                    <li><a href="/api/docs" class="text-muted">API <?= $language->get('common.documentation') ?></a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5><?= $language->get('footer.contact') ?></h5>
                <ul class="list-unstyled">
                    <li><a href="mailto:support@flowbreath.io" class="text-muted"><i class="fa fa-envelope me-2"></i>support@flowbreath.io</a></li>
                    <li><a href="https://github.com/flowbreath" class="text-muted" target="_blank"><i class="fab fa-github me-2"></i>GitHub</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4">
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= $language->get('common.site_name') ?>. <?= $language->get('footer.all_rights_reserved') ?></p>
        </div>
    </div>
</footer> 