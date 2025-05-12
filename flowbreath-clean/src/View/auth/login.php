<?php
// src/View/auth/login.php
// 로그인 폼 뷰
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">로그인</div>
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
                            <label for="email">이메일</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group mt-3">
                            <label for="password">비밀번호</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">로그인 상태 유지</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary w-100">로그인</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/register">회원가입</a>
                    </div>

                    <hr>

                    <!-- 소셜 로그인 버튼 -->
                    <a href="/auth/google" class="btn btn-outline-danger w-100 mt-2">
                        <i class="fab fa-google"></i> 구글로 로그인
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/../layouts/footer.php';
?> 