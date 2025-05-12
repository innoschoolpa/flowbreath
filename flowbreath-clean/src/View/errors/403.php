<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'ko' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 접근 거부</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h1 class="display-1 text-danger">403</h1>
                <h2 class="mb-4">접근이 거부되었습니다</h2>
                <p class="lead mb-4">죄송합니다. 이 페이지에 접근할 권한이 없습니다.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/" class="btn btn-primary">홈으로</a>
                    <a href="javascript:history.back()" class="btn btn-secondary">이전 페이지</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 