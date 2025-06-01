    </main>
    <footer class="footer">
        <div class="container text-center">
            <div class="disclaimer mb-4">
                <p class="mb-2">본 사이트의 내용은 정보 공유를 위한 것이며, 어떠한 의학적 진단, 치료, 처방도 제공하지 않습니다. 건강상의 문제가 있거나 특정 수련법을 시작하기 전에는 반드시 전문 의료인과 상담하십시오</p>
                <p class="mb-2">The content on this site is for informational purposes only and does not provide any medical diagnosis, treatment, or prescription. If you have health concerns or before starting any specific practice, please consult with a qualified healthcare professional.</p>
            </div>
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