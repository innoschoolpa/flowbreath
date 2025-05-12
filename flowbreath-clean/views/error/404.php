<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>페이지를 찾을 수 없습니다 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="error-page">
            <h1>404</h1>
            <h2>페이지를 찾을 수 없습니다</h2>
            <p><?= htmlspecialchars($message ?? '요청하신 페이지가 존재하지 않거나 이동되었을 수 있습니다.') ?></p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">홈으로 돌아가기</a>
                <a href="javascript:history.back()" class="btn btn-secondary">이전 페이지로</a>
            </div>
        </div>
    </div>
</body>
</html> 