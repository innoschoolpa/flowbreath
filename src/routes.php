<?php

use App\Core\Router;
use App\Controllers\HealthController;
use App\Controllers\TestController;
use App\Controllers\HomeController;
use App\Controllers\LanguageController;

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
    $router->get('/language/switch/{lang}', LanguageController::class, 'switch');

    // 404 처리 라우트 (모든 경로에 대해)
    $router->add('GET', '*', [HomeController::class, 'notFound']);
}; 