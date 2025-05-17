    </main>
    <footer class="footer">
        <div class="container text-center">
            <div class="mb-2">
                <?php if (isset($language) && $language): ?>
                    <?= $language->get('footer.copyright', ['year' => date('Y')]) ?>
                <?php else: ?>
                    &copy; <?= date('Y') ?> FlowBreath
                <?php endif; ?>
            </div>
            <div>
                <?php if (isset($language) && $language): ?>
                    <?= $language->get('footer.description') ?>
                <?php else: ?>
                    The best platform for breathing exercises and resources.
                <?php endif; ?>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 