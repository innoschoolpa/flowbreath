<?php
// src/View/error/500.php
if (http_response_code() !== 500) {
    http_response_code(500);
}
$page_title = "500 - 서버 내부 오류";
include __DIR__ . '/../layout/header.php';
?>

<div class="error-page container" style="text-align: center; padding: 50px;">
    <h1><i class="fas fa-server" style="color: #dc3545;"></i> 500</h1>
    <h2>서버 내부 오류</h2>
    <p>요청을 처리하는 중 예상치 못한 문제가 발생했습니다.<br>잠시 후 다시 시도해 주시기 바랍니다.</p>
    <p>문제가 지속될 경우 관리자에게 문의해주세요.</p>
    <p style="margin-top: 30px;">
        <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
    </p>
</div>

<?php
include __DIR__ . '/../layout/footer.php';
?>