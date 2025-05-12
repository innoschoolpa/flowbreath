<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>로그인</h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?>">
                <?php echo $_SESSION['flash_message']['message']; ?>
            </div>
        <?php endif; ?>

        <form action="/auth/login" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $this->generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">로그인</button>
                <a href="/auth/register" class="btn btn-link">회원가입</a>
            </div>
        </form>
    </div>
</body>
</html> 