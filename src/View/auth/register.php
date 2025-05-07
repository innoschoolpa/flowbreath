<?php
// src/View/auth/register.php
// 헤더 포함
load_view('layout/header', ['title' => $page_title ?? '회원가입']);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.register-hero {
    margin-top: 60px;
    margin-bottom: 30px;
    text-align: center;
}
.register-card {
    max-width: 420px;
    margin: 0 auto;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border-radius: 1.2rem;
    padding: 2.5rem 2rem 2rem 2rem;
    background: #fff;
}
.google-btn {
    font-weight: 500;
    border-radius: 2rem;
    border-width: 2px;
    /* margin-bottom: 1.2rem; */
    /* outline 제거, w-100, btn-danger는 클래스에서 처리 */
}
.google-btn img {
    width: 22px;
    margin-right: 8px;
    vertical-align: middle;
}
.form-label { font-weight: 500; }
.btn-primary { border-radius: 2rem; font-weight: 600; }
@media (max-width: 600px) {
    .register-card { padding: 1.5rem 0.5rem; }
}
</style>

<div class="register-hero">
    <h2 class="fw-bold mb-2">회원가입</h2>
    <p class="text-muted mb-0">FlowBreath.io에 오신 것을 환영합니다</p>
</div>
<div class="register-card">
    <?php if (!empty($google_login_url)): ?>
        <a href="<?php echo htmlspecialchars($google_login_url); ?>" class="btn btn-danger w-100 mb-2 google-btn">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo"> 구글로 계속하기
        </a>
        <div class="text-center text-muted mb-3" style="font-size:0.95rem;">또는 이메일로 가입</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/register" autocomplete="off">
        <div class="mb-3">
            <label for="username" class="form-label">사용자 이름</label>
            <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">이메일</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">비밀번호</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label">비밀번호 확인</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 mt-2">회원가입</button>
        <div class="mt-3 text-center">
            <a href="/login" class="text-decoration-none">이미 계정이 있으신가요? <b>로그인</b></a>
        </div>
    </form>
</div>
<?php
// 푸터 포함
load_view('layout/footer');
?>