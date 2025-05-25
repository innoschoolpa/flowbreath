<?php
// src/View/auth/register.php
// 헤더 포함
require_once __DIR__ . '/../layouts/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #192133;
        color: #fff;
    }
    .register-card, .card {
        background: #232f47 !important;
        color: #fff !important;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .form-control {
        background-color: #232f47 !important;
        color: #fff !important;
        border-color: #2d3e50;
    }
    .form-label { color: #fff; }
    .form-check-input:checked {
        background-color: #3498db;
        border-color: #3498db;
    }
    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
    }
    .btn-primary:hover {
        background-color: #217dbb;
        border-color: #217dbb;
    }
    .btn-outline-danger {
        color: #fff;
        border-color: #e74c3c;
        background: transparent;
    }
    .btn-outline-danger:hover {
        background: #e74c3c;
        color: #fff;
    }
    a { color: #6cb2ff; }
    a:hover { color: #3498db; }
    .register-hero {
        margin-top: 60px;
        margin-bottom: 30px;
        text-align: center;
    }
    .register-card {
        max-width: 420px;
        margin: 0 auto;
        padding: 2.5rem 2rem 2rem 2rem;
    }
    .google-btn {
        font-weight: 500;
        border-radius: 2rem;
        border-width: 2px;
    }
    .google-btn img {
        width: 22px;
        margin-right: 8px;
        vertical-align: middle;
    }
    @media (max-width: 600px) {
        .register-card { padding: 1.5rem 0.5rem; }
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">회원가입</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/register" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">사용자 이름</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <div class="invalid-feedback">
                                사용자 이름을 입력해주세요.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="invalid-feedback">
                                유효한 이메일 주소를 입력해주세요.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="8">
                            <div class="invalid-feedback">
                                비밀번호는 최소 8자 이상이어야 합니다.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">비밀번호 확인</label>
                            <input type="password" class="form-control" id="password_confirmation" 
                                   name="password_confirmation" required>
                            <div class="invalid-feedback">
                                비밀번호가 일치하지 않습니다.
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    <a href="/terms" target="_blank">이용약관</a>과 
                                    <a href="/privacy" target="_blank">개인정보처리방침</a>에 동의합니다.
                                </label>
                                <div class="invalid-feedback">
                                    이용약관에 동의해주세요.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">회원가입</button>
                        </div>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-2">또는 다음으로 계속하기</p>
                        <a href="/auth/google" class="btn btn-outline-danger">
                            <i class="fab fa-google"></i> Google로 계속하기
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <p class="mb-0">이미 계정이 있으신가요? <a href="/login">로그인</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            // Check if passwords match
            var password = document.getElementById('password')
            var confirmation = document.getElementById('password_confirmation')
            if (password.value !== confirmation.value) {
                confirmation.setCustomValidity('비밀번호가 일치하지 않습니다.')
                event.preventDefault()
                event.stopPropagation()
            } else {
                confirmation.setCustomValidity('')
            }

            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
// 푸터 포함
require_once __DIR__ . '/../layouts/footer.php';
?>