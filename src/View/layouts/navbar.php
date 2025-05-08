<?php
// src/View/layout/navbar.php
// 네비게이션 바 뷰 파일

// 설정 파일 로드 (서버 환경에 맞게 경로 수정)
$configPath = dirname(dirname(dirname(__DIR__))) . '/config/app.php';
if (!file_exists($configPath)) {
    // 대체 경로 시도
    $configPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/config/app.php';
}
$config = require $configPath;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $config['base_url']; ?>">FlowBreath.io</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $config['base_url']; ?>">홈</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $config['base_url']; ?>/about">소개</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $config['base_url']; ?>/dashboard">대시보드</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $config['base_url']; ?>/logout">로그아웃</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $config['base_url']; ?>/login">로그인</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $config['base_url']; ?>/register">회원가입</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>