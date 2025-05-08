<?php
// src/View/error/500.php
if (http_response_code() !== 500) {
    http_response_code(500);
}
$page_title = "500 - 서버 내부 오류";
include __DIR__ . '/../layouts/header.php';
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height:60vh;">
  <div class="alert alert-danger text-center shadow-lg p-5 w-100" style="max-width:400px;">
    <div style="font-size:3rem;"><i class="fas fa-server"></i></div>
    <h1 class="mt-2">Error 500</h1>
    <p class="mb-2">Invalid response type<br>서버 내부 오류가 발생했습니다.<br>관리자에게 문의해 주세요.</p>
    <a href="/" class="btn btn-primary mt-3">홈으로 돌아가기</a>
  </div>
</div>
<?php
include __DIR__ . '/../layouts/footer.php';
?>