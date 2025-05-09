<?php

use App\Core\Router;
use App\Controllers\HealthController;
use App\Controllers\TestController;
use App\Controllers\HomeController;
use App\Controllers\LanguageController;
use App\Controllers\ResourceController;
use App\Controllers\CommentController;
use App\Controllers\LikeController;
use App\Controllers\BatchController;
use App\Controllers\AuthController;
use App\Controllers\SettingsController;
use App\Controllers\ProfileController;

return function (Router $router) {
    // 홈 라우트
    $router->add('GET', '/', [HomeController::class, 'index']);

    // 상태 확인 라우트
    $router->add('GET', '/api/health', [HealthController::class, 'check']);

    // 테스트 라우트
    $router->add('GET', '/api/test/error', [TestController::class, 'testError']);
    $router->add('GET', '/api/test/warning', [TestController::class, 'testWarning']);
    $router->add('GET', '/api/test/notice', [TestController::class, 'testNotice']);
    $router->add('GET', '/api/test/memory', [TestController::class, 'testMemory']);
    $router->add('GET', '/api/test/performance', [TestController::class, 'testPerformance']);

    // Language routes
    $router->add('GET', '/language/switch/{lang}', [LanguageController::class, 'switch']);

    // Resource routes
    $router->add('GET', '/resources', [ResourceController::class, 'index']);
    $router->add('GET', '/resources/', [ResourceController::class, 'index']);
    $router->add('GET', '/resources/show/{id}', [ResourceController::class, 'show']);
    $router->add('GET', '/resources/create', [ResourceController::class, 'create']);
    $router->add('POST', '/resources/store', [ResourceController::class, 'store']);
    $router->add('GET', '/resources/edit/{id}', [ResourceController::class, 'edit']);
    $router->add('PUT', '/resources/{id}', [ResourceController::class, 'update']);
    $router->add('DELETE', '/resources/{id}', [ResourceController::class, 'delete']);
    $router->add('GET', '/resources/search', [ResourceController::class, 'search']);
    $router->add('POST', '/resources/toggle-visibility/{id}', [ResourceController::class, 'toggleVisibility']);
    $router->add('GET', '/resources/view/{id}', [ResourceController::class, 'show']);

    // New routes for /tags and /api/docs
    $router->add('GET', '/tags', [ResourceController::class, 'tags']);
    $router->add('GET', '/api/docs', [HomeController::class, 'apiDocs']);

    // 댓글 관련 라우트
    $router->add('GET', '/api/resources/{id}/comments', [CommentController::class, 'index']);
    $router->add('POST', '/api/resources/{id}/comments', [CommentController::class, 'store']);
    $router->add('PUT', '/api/comments/{id}', [CommentController::class, 'update']);
    $router->add('DELETE', '/api/comments/{id}', [CommentController::class, 'destroy']);

    // 좋아요 관련 라우트
    $router->add('POST', '/api/resources/{id}/like', [LikeController::class, 'toggle']);
    $router->add('GET', '/api/resources/{id}/like', [LikeController::class, 'status']);

    // Auth routes
    $router->add('GET', '/login', [AuthController::class, 'showLogin']);
    $router->add('POST', '/login', [AuthController::class, 'login']);
    $router->add('GET', '/register', [AuthController::class, 'showRegister']);
    $router->add('POST', '/register', [AuthController::class, 'register']);
    $router->add('GET', '/logout', [\App\Controllers\LogoutController::class, 'index']);
    $router->add('POST', '/logout', [\App\Controllers\LogoutController::class, 'index']);
    $router->add('GET', '/auth/google', [AuthController::class, 'google']);
    $router->add('GET', '/auth/google/callback', [AuthController::class, 'googleCallback']);
    $router->add('GET', '/auth/additional-info', ['App\\Controllers\\AuthController', 'additionalInfo']);
    $router->add('POST', '/auth/additional-info', ['App\\Controllers\\AuthController', 'saveAdditionalInfo']);

    // Batch operation routes
    $router->add('GET', '/batch', [BatchController::class, 'index']);
    $router->add('POST', '/batch/import-resources', [BatchController::class, 'importResources']);
    $router->add('GET', '/batch/export-resources', [BatchController::class, 'exportResources']);
    $router->add('POST', '/batch/import-tags', [BatchController::class, 'importTags']);
    $router->add('GET', '/batch/export-tags', [BatchController::class, 'exportTags']);
    $router->add('POST', '/batch/cleanup-orphaned-resources', [BatchController::class, 'cleanupOrphanedResources']);
    $router->add('POST', '/batch/cleanup-unused-tags', [BatchController::class, 'cleanupUnusedTags']);
    $router->add('POST', '/batch/merge-tags', [BatchController::class, 'mergeTags']);

    // Settings routes
    $router->add('GET', '/settings', [SettingsController::class, 'index']);
    $router->add('POST', '/settings/update-profile', [SettingsController::class, 'updateProfile']);
    $router->add('POST', '/settings/update-password', [SettingsController::class, 'updatePassword']);
    $router->add('POST', '/settings/update-notifications', [SettingsController::class, 'updateNotifications']);

    // Profile routes
    $router->add('GET', '/profile', [ProfileController::class, 'index']);
    // 정적 경로를 먼저 등록
    $router->add('POST', '/profile/update', ['App\Controllers\ProfileController', 'update']);
    $router->add('POST', '/profile/update-image', ['App\Controllers\ProfileController', 'updateImage']);
    $router->add('POST', '/profile/update-social', ['App\Controllers\ProfileController', 'updateSocial']);
    // 동적 경로는 아래에 등록
    $router->add('GET', '/profile/{id}', [ProfileController::class, 'show']);

    // 404 처리 라우트 (모든 경로에 대해)
    $router->add('GET', '*', [HomeController::class, 'notFound']);
}; 