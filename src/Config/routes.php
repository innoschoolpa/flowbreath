<?php

use App\Controllers\ResourceController;
use App\Controllers\ApiController;

// API 라우트
$router->get('/api/tags/suggest', [ApiController::class, 'tagSuggestions']); 

// About 페이지 라우트
$router->get('/about', function() {
    require __DIR__ . '/../View/home/about.php'
}); 

// 리소스 관련 라우트
$router->post('/resources/toggle-visibility/{id}', [ResourceController::class, 'toggleVisibility']); 