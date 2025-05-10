<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">오류가 발생했습니다</h4>
        <p><?= isset($error_message) ? htmlspecialchars($error_message) : '알 수 없는 오류가 발생했습니다.' ?></p>
        <hr>
        <p class="mb-0">
            <a href="javascript:history.back()" class="alert-link">이전 페이지로 돌아가기</a>
            또는
            <a href="/" class="alert-link">홈으로 이동</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 