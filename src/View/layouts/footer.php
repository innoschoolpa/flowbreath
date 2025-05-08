<footer class="footer mt-5 py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">
            &copy; <?= date('Y') ?> FlowBreath.io
            <?php if (isset($language) && $language): ?>
                | <?= $language->get('footer.slogan') ?>
            <?php else: ?>
                | 호흡 건강을 위한 최고의 자료 플랫폼
            <?php endif; ?>
        </span>
    </div>
</footer> 