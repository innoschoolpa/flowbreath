<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>서버 오류 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="error-page">
            <h1>500</h1>
            <h2>서버 오류가 발생했습니다</h2>
            <p><?= htmlspecialchars($message ?? '요청을 처리하는 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.') ?></p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
                <a href="javascript:history.back()" class="btn btn-secondary">이전 페이지로</a>
            </div>
        </div>
    </div>
</body>
</html> 