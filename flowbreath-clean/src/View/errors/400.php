<?php
// src/View/error/404.php
// Controller에서 http_response_code(404)를 이미 설정했을 수 있음
if (http_response_code() !== 404) {
    http_response_code(404);
}
// $page_title 변수가 전달되지 않을 수 있으므로 기본값 설정
$page_title = "404 - 페이지를 찾을 수 없습니다";
// 헤더 파일 경로 수정 (현재 파일 기준)
include __DIR__ . '/../layout/header.php';
?>

<div class="error-page container" style="text-align: center; padding: 50px;">
    <h1><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> 404</h1>
    <h2>페이지를 찾을 수 없습니다.</h2>
    <p>요청하신 페이지가 존재하지 않거나, 이동되었거나, 삭제되었을 수 있습니다.<br>입력하신 주소가 정확한지 다시 한번 확인해주세요.</p>
    <p style="margin-top: 30px;">
        <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
        <?php // 필요하다면 다른 링크 추가 ?>
    </p>
</div>

<?php
// 푸터 파일 경로 수정 (현재 파일 기준)
include __DIR__ . '/../layout/footer.php';
?>