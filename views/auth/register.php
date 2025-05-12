<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>회원가입</h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?>">
                <?php echo $_SESSION['flash_message']['message']; ?>
            </div>
        <?php endif; ?>

        <form action="/auth/register" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $this->generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small class="form-text">비밀번호는 최소 6자 이상이어야 합니다.</small>
            </div>

            <div class="form-group">
                <label for="name">이름</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">회원가입</button>
                <a href="/auth/login" class="btn btn-link">로그인</a>
            </div>
        </form>
    </div>
</body>
</html> 