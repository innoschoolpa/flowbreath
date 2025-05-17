<?php
declare(strict_types=1);

// 오류 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 프로젝트 루트 경로 정의
define('PROJECT_ROOT', dirname(__DIR__));

// Composer 오토로더 로드
require_once PROJECT_ROOT . '/vendor/autoload.php';

use App\Core\Language;
use App\Core\Router;
use App\Core\Application;
use App\Models\Resource;
use App\Models\Tag;

// 환경 설정 로드
try {
    if (file_exists(PROJECT_ROOT . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
        $dotenv->load();
    }
} catch (\Exception $e) {
    error_log('Error loading environment variables: ' . $e->getMessage());
}

// 데이터베이스 연결
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    die('Database connection failed');
}

// 언어 설정 초기화
$language = Language::getInstance();

// API 상태 확인 함수
function checkApiStatus(string $endpoint): array {
    $ch = curl_init("https://flowbreath.io" . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode === 200 ? 'active' : 'inactive',
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// 최근 공개 리소스 가져오기
$resourceModel = new Resource($pdo);
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
$recentResources = $resourceModel->getRecentPublic(4, $lang);

// 인기 태그 가져오기
$tagModel = new Tag($pdo);
$popularTags = $tagModel->getPopularTags(8);

// API 엔드포인트 목록
$endpoints = [
    'health' => '/api/health',
    'error' => '/api/test/error',
    'warning' => '/api/test/warning',
    'notice' => '/api/test/notice',
    'memory' => '/api/test/memory',
    'performance' => '/api/test/performance'
];

// API 상태 확인
$apiStatus = [];
foreach ($endpoints as $name => $endpoint) {
    $apiStatus[$name] = checkApiStatus($endpoint);
}

// 로그인 상태
$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

// 검색 처리
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchResults = [];
if ($searchQuery !== '') {
    $searchResults = $resourceModel->searchResources($searchQuery, 10);
}

// Request 객체 생성
$request = new \App\Core\Request();

// 라우터 설정
$router = new Router($request);

// Load bootstrap file
require_once __DIR__ . '/../src/bootstrap.php';

// Load router
require_once __DIR__ . '/../src/routes.php';

// 라우트 정의
$routes = require __DIR__ . '/../src/routes.php';
$routes($router);

// 애플리케이션 실행
$app = new Application($router);
$app->run();

// 페이지 제목 설정
$title = $language->get('common.site_name') . ' - ' . $language->get('home.hero.title');

// 공통 레이아웃 포함
require_once __DIR__ . '/../src/View/layouts/header.php';
?>

<!-- 히어로 섹션 -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3"><?= $language->get('home.hero.title') ?></h1>
        <p class="lead mb-4"><?= $language->get('home.hero.subtitle') ?></p>
        <form class="search-box" method="get" action="/">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="q" placeholder="<?= $language->get('home.hero.search_placeholder') ?>" value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i> <?= $language->get('common.search') ?></button>
            </div>
        </form>
    </div>
</section>

<!-- 메인 콘텐츠 -->
<div class="container">
    <?php if ($searchQuery !== ''): ?>
        <h4 class="mb-4">'<?= htmlspecialchars($searchQuery) ?>' <?= $language->get('common.search') ?></h4>
        <div class="row">
            <?php if (empty($searchResults)): ?>
                <div class="col-12">
                    <div class="alert alert-warning"><?= $language->get('home.recent_resources.no_results') ?></div>
                </div>
            <?php else: ?>
                <?php foreach ($searchResults as $resource): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-resource h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title']) ?></h5>
                                <div class="resource-meta mb-2">
                                    <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                    <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                                </div>
                                <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,80,'...')) ?></p>
                                <div class="mb-2">
                                    <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                        <span class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <a href="/resources/view/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm"><?= $language->get('common.read_more') ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?= $language->get('home.recent_resources.title') ?></h4>
            <a href="/resources" class="btn btn-link"><?= $language->get('common.view_all') ?> <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row">
            <?php foreach ($recentResources as $resource): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-resource h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($resource['title']) ?></h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? $language->get('common.anonymous')) ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,80,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="/resources/view/<?= $resource['id'] ?>" class="btn btn-outline-primary btn-sm"><?= $language->get('common.read_more') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../src/View/layouts/footer.php'; ?> 