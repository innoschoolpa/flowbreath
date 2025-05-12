<?php require APP_PATH . '/View/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">비밀번호 재설정</div>
                <div class="card-body">
                    <?php if (isset($_SESSION['auth_error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo htmlspecialchars($_SESSION['auth_error']);
                            unset($_SESSION['auth_error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['auth_success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo htmlspecialchars($_SESSION['auth_success']);
                            unset($_SESSION['auth_success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/password/email">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label for="email">이메일</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="form-text text-muted">
                                가입하신 이메일 주소를 입력하시면, 비밀번호 재설정 링크를 보내드립니다.
                            </small>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">재설정 링크 받기</button>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="/login">로그인 페이지로 돌아가기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/View/layouts/footer.php'; ?> 