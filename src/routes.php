<?php

use App\Core\Router;
use App\Controllers\HealthController;
use App\Controllers\TestController;
use App\Controllers\HomeController;
use App\Controllers\LanguageController;
use App\Controllers\ResourceController;
use App\Controllers\CommentController;
use App\Controllers\LikeController;

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
    $router->add('GET', '/resources/{id}/edit', [ResourceController::class, 'edit']);
    $router->add('POST', '/resources/{id}/update', [ResourceController::class, 'update']);
    $router->add('POST', '/resources/{id}/delete', [ResourceController::class, 'delete']);
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

    // 404 처리 라우트 (모든 경로에 대해)
    $router->add('GET', '*', [HomeController::class, 'notFound']);
}; 