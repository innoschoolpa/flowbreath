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

// 언어 설정
$lang = \Helpers\Language::getInstance();
$currentLang = $lang->getCurrentLanguage();

// 현재 URL에서 lang 파라미터 제거
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');

// 언어별 네비게이션 텍스트
$navTexts = [
    'ko' => [
        'home' => '홈',
        'about' => '소개',
        'admin' => '관리자',
        'admin_dashboard' => '대시보드',
        'admin_resources' => '리소스 관리',
        'admin_translations' => '번역 관리',
        'admin_users' => '사용자 관리',
        'dashboard' => '대시보드',
        'login' => '로그인',
        'logout' => '로그아웃',
        'register' => '회원가입'
    ],
    'en' => [
        'home' => 'Home',
        'about' => 'About',
        'admin' => 'Admin',
        'admin_dashboard' => 'Dashboard',
        'admin_resources' => 'Resources',
        'admin_translations' => 'Translations',
        'admin_users' => 'Users',
        'dashboard' => 'Dashboard',
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Register'
    ]
];

// 현재 언어에 맞는 네비게이션 텍스트 선택
$nav = $navTexts[$currentLang];
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
                    <a class="nav-link" href="<?php echo $config['base_url']; ?>"><?php echo $nav['home']; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $config['base_url']; ?>/about"><?php echo $nav['about']; ?></a>
                </li>
                <?php if (is_admin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <?php echo $nav['admin']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $config['base_url']; ?>/admin/dashboard"><?php echo $nav['admin_dashboard']; ?></a></li>
                        <li><a class="dropdown-item" href="<?php echo $config['base_url']; ?>/admin/resources"><?php echo $nav['admin_resources']; ?></a></li>
                        <li><a class="dropdown-item" href="<?php echo $config['base_url']; ?>/admin/translations"><?php echo $nav['admin_translations']; ?></a></li>
                        <li><a class="dropdown-item" href="<?php echo $config['base_url']; ?>/admin/users"><?php echo $nav['admin_users']; ?></a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <?php include __DIR__ . '/../components/language_switcher.php'; ?>
                
                <?php if (is_logged_in()): ?>
                    <a class="btn btn-outline-primary ms-2" href="<?php echo $config['base_url']; ?>/dashboard"><?php echo $nav['dashboard']; ?></a>
                    <a class="btn btn-outline-danger ms-2" href="<?php echo $config['base_url']; ?>/logout"><?php echo $nav['logout']; ?></a>
                <?php else: ?>
                    <a class="btn btn-outline-primary ms-2" href="<?php echo $config['base_url']; ?>/login"><?php echo $nav['login']; ?></a>
                    <a class="btn btn-primary ms-2" href="<?php echo $config['base_url']; ?>/register"><?php echo $nav['register']; ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>