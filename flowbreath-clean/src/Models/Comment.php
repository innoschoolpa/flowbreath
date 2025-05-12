<?php
namespace App\Models;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('PROJECT_ROOT', __DIR__);

// Composer Autoloader
require_once PROJECT_ROOT . '/vendor/autoload.php';

// .env 환경변수 로드
if (file_exists(PROJECT_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
    $dotenv->load();
}

// DB 연결 (config/database.php 사용)
require_once PROJECT_ROOT . '/config/database.php';
$pdo = getDbConnection();

// 최근 리소스
$resourceModel = new \App\Models\Resource($pdo);
$recentResources = $resourceModel->getRecentPublic(8);

// 인기 태그
$tagModel = new \App\Models\Tag($pdo);
$popularTags = $tagModel->getPopularTags(8);

// 로그인 상태
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'profile_image' => $_SESSION['user_avatar'] ?? null,
    'bio' => $_SESSION['user_bio'] ?? '',
    'social_links' => $_SESSION['user_social_links'] ?? ''
] : null;

// 검색 처리
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchResults = [];
if ($searchQuery !== '') {
    $searchResults = $resourceModel->searchResources($searchQuery, 10);
}

use App\Core\Database;

class Comment
{
    private static function getDB()
    {
        return Database::getInstance();
    }

    public static function getResourceComments($resourceId, $limit = 10, $offset = 0)
    {
        $db = self::getDB();
        $sql = "
            SELECT c.*, u.name as user_name 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.resource_id = ? AND c.parent_id IS NULL AND c.is_deleted = 0
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ";
        return $db->fetchAll($sql, [$resourceId, $limit, $offset]);
    }

    public static function getReplies($parentId, $limit = 5, $offset = 0)
    {
        $db = self::getDB();
        $sql = "
            SELECT c.*, u.name as user_name 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.parent_id = ? AND c.is_deleted = 0
            ORDER BY c.created_at ASC
            LIMIT ? OFFSET ?
        ";
        return $db->fetchAll($sql, [$parentId, $limit, $offset]);
    }

    public static function countResourceComments($resourceId)
    {
        $db = self::getDB();
        $sql = "SELECT COUNT(*) as count FROM comments WHERE resource_id = ? AND is_deleted = 0";
        $result = $db->fetch($sql, [$resourceId]);
        return $result['count'] ?? 0;
    }

    public static function findOrFail($id)
    {
        $db = self::getDB();
        $sql = "SELECT * FROM comments WHERE id = ? AND is_deleted = 0";
        $comment = $db->fetch($sql, [$id]);
        
        if (!$comment) {
            throw new \Exception('댓글을 찾을 수 없습니다.');
        }
        
        return $comment;
    }

    public static function create($data)
    {
        $db = self::getDB();
        return $db->insert('comments', $data);
    }

    public static function update($id, $data)
    {
        $db = self::getDB();
        return $db->update('comments', $data, 'id = ?', [$id]);
    }

    public static function softDelete($id)
    {
        $db = self::getDB();
        return $db->update('comments', ['is_deleted' => 1], 'id = ?', [$id]);
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowBreath.io - 호흡 자료 공유 플랫폼</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #2d3e50; }
        .navbar-brand { font-weight: bold; letter-spacing: 1px; }
        .hero-section { background: linear-gradient(135deg, #3498db, #2ecc71); color: #fff; padding: 3rem 0 2rem 0; text-align: center; }
        .search-box { max-width: 500px; margin: 2rem auto 0 auto; }
        .card-resource { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: box-shadow 0.2s; }
        .card-resource:hover { box-shadow: 0 4px 16px rgba(52,152,219,0.15); }
        .tag-badge { background: #3498db; color: #fff; border-radius: 20px; padding: 0.3em 1em; margin: 0.1em; font-size: 0.95em; }
        .popular-tags .tag-badge { background: #2ecc71; }
        .resource-meta { color: #888; font-size: 0.95em; }
        .footer { background: #2d3e50; color: #fff; padding: 2rem 0; margin-top: 3rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/">FlowBreath.io</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/resources">자료</a></li>
                <li class="nav-item"><a class="nav-link" href="/tags">태그</a></li>
                <li class="nav-item"><a class="nav-link" href="/api/docs">API 안내</a></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="/profile"><i class="fa fa-user"></i> 내 정보</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-outline-light" href="/logout">로그아웃</a></li>
                <?php else: ?>
                    <li class="nav-item ms-2"><a class="btn btn-primary me-2" href="/login">로그인</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary" href="/register">회원가입</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3">호흡을 위한 최고의 자료, <span style="color:#ffe082;">FlowBreath.io</span></h1>
        <p class="lead mb-4">호흡 건강, 운동, 명상, 치료 등 다양한 호흡 자료를 쉽고 빠르게 찾아보세요.</p>
        <form class="search-box" method="get" action="/">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control" name="q" placeholder="자료, 태그, 키워드로 검색..." value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="btn btn-warning" type="submit"><i class="fa fa-search"></i> 검색</button>
            </div>
        </form>
    </div>
</section>

<div class="container mt-5">
    <?php if ($searchQuery !== ''): ?>
        <h4 class="mb-4">'<?= htmlspecialchars($searchQuery) ?>' 검색 결과</h4>
        <div class="row">
            <?php if (empty($searchResults)): ?>
                <div class="col-12"><div class="alert alert-warning">검색 결과가 없습니다.</div></div>
            <?php else: foreach ($searchResults as $resource): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-resource h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($resource['title']) ?>
                                </a>
                            </h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? '익명') ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,80,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">최근 등록된 호흡 자료</h4>
            <a href="/resources" class="btn btn-link">전체 보기 <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="row">
            <?php foreach ($recentResources as $resource): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card card-resource h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="/resources/view/<?= $resource['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($resource['title']) ?>
                                </a>
                            </h5>
                            <div class="resource-meta mb-2">
                                <i class="fa fa-user"></i> <?= htmlspecialchars($resource['username'] ?? '익명') ?> ·
                                <i class="fa fa-calendar"></i> <?= htmlspecialchars(substr($resource['created_at'],0,10)) ?>
                            </div>
                            <p class="card-text mb-2"><?= htmlspecialchars(mb_strimwidth(strip_tags($resource['content']),0,80,'...')) ?></p>
                            <div class="mb-2">
                                <?php foreach (($resource['tags'] ?? []) as $tag): ?>
                                    <span class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="popular-tags mt-5">
            <h5 class="mb-3">인기 태그</h5>
            <?php foreach ($popularTags as $tag): ?>
                <a href="/tags/view/<?= $tag['id'] ?>" class="tag-badge">#<?= htmlspecialchars($tag['name']) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer mt-5">
    <div class="container text-center">
        <div class="mb-2">&copy; <?= date('Y') ?> FlowBreath.io. All rights reserved.</div>
        <div>호흡 건강을 위한 최고의 자료 플랫폼</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>