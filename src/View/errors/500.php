<?php
$title = '500 Internal Server Error';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #dc3545;
            margin: 0 0 20px;
            font-size: 2.5rem;
        }
        p {
            color: #6c757d;
            margin: 0 0 20px;
            font-size: 1.1rem;
        }
        .home-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .home-link:hover {
            background-color: #0b5ed7;
            color: white;
        }
        .error-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1><?php echo $title; ?></h1>
        <p>죄송합니다. 서버에서 오류가 발생했습니다.</p>
        <p>잠시 후 다시 시도해 주세요.</p>
        <?php if (isset($error) && $error): ?>
            <div class="error-details">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <a href="/" class="home-link">홈으로 돌아가기</a>
    </div>
</body>
</html> 