<div class="col-md-6 col-lg-4 mb-4">
    <div class="card card-resource h-100">
        <div class="card-body">
            <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title'] ?? '') ?></h5>
            <div class="resource-meta mb-2">
                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                <i class="fa fa-calendar"></i> <?= htmlspecialchars(isset($resource['created_at']) ? substr($resource['created_at'], 0, 10) : '') ?>
            </div>
            <p class="card-text mb-2">
                <?php
                $content = $resource['content'] ?? '';
                if (is_string($content)) {
                    echo htmlspecialchars(mb_strimwidth(strip_tags($content), 0, 80, '...'));
                }
                ?>
            </p>
            <div class="mb-2">
                <?php 
                $tags = [];
                if (isset($resource['tags'])) {
                    if (is_array($resource['tags'])) {
                        $tags = $resource['tags'];
                    } elseif (is_string($resource['tags'])) {
                        // 쉼표로 구분된 태그 문자열 처리
                        $tags = array_filter(array_map('trim', explode(',', $resource['tags'])));
                    }
                }
                
                foreach ($tags as $tag): 
                    if (!empty($tag)):
                ?>
                    <span class="tag-badge">#<?= htmlspecialchars($tag) ?></span>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
            <?php if (isset($resource['id'])): ?>
                <a href="/resources/view/<?= htmlspecialchars($resource['id']) ?>" class="btn btn-outline-primary btn-sm">
                    <?= $language->get('common.read_more') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div> 