<?php
// src/View/auth/login.php
// 로그인 폼 뷰
require_once __DIR__ . '/../layouts/header.php';
?>
<style>
    body {
        background-color: #192133;
        color: #fff;
    }
    .card.bg-dark, .card {
        background-color: #232f47 !important;
        color: #fff !important;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .form-control.bg-dark, .form-control {
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
    .btn-outline-light {
        color: #fff;
        border-color: #fff;
        background: transparent;
    }
    .btn-outline-light:hover {
        background: #fff;
        color: #232f47;
    }
    a { color: #6cb2ff; }
    a:hover { color: #3498db; }
</style>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><?= __('profile.login.title') ?></div>
                <div class="card-body">
                    <?php if (isset($_SESSION['auth_error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo htmlspecialchars($_SESSION['auth_error']);
                            unset($_SESSION['auth_error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/login">
                        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                        
                        <div class="form-group">
                            <label for="email"><?= __('profile.login.email') ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group mt-3">
                            <label for="password"><?= __('profile.login.password') ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember"><?= __('profile.login.remember_me') ?></label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary w-100"><?= __('profile.login.submit') ?></button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/register"><?= __('profile.login.register') ?></a>
                    </div>

                    <hr>

                    <!-- 소셜 로그인 버튼 -->
                    <a href="/auth/google" class="btn btn-outline-danger w-100 mt-2">
                        <i class="fab fa-google"></i> <?= __('profile.login.google_login') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/../layouts/footer.php';
?> 