<?php

use App\Core\Router;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\LanguageController;
use App\Controllers\ResourceController;
use App\Controllers\CommentController;
use App\Controllers\LikeController;
use App\Controllers\BatchController;
use App\Controllers\AuthController;
use App\Controllers\SettingsController;
use App\Controllers\ProfileController;
use App\Controllers\BreathingController;
use App\Controllers\UploadController;
use App\Controllers\DiaryController;
use App\Controllers\ApiController;

return function (Router $router) {
    // 홈 라우트
    $router->add('GET', '/', [HomeController::class, 'index']);

    // 상태 확인 라우트
    $router->add('GET', '/api/health', [HealthController::class, 'check']);

    // 테스트 라우트
    $router->add('GET', '/api/test/error', ['App\Controllers\TestController', 'testError']);
    $router->add('GET', '/api/test/warning', ['App\Controllers\TestController', 'testWarning']);
    $router->add('GET', '/api/test/notice', ['App\Controllers\TestController', 'testNotice']);
    $router->add('GET', '/api/test/memory', ['App\Controllers\TestController', 'testMemory']);
    $router->add('GET', '/api/test/performance', ['App\Controllers\TestController', 'testPerformance']);

    // Language routes
    $router->add('GET', '/language/switch/{lang}', [LanguageController::class, 'switch']);

    // Resource routes
    $router->add('GET', '/resources', [ResourceController::class, 'index']);
    $router->add('GET', '/resources/', [ResourceController::class, 'index']);
    $router->add('GET', '/resources/show/{id}', [ResourceController::class, 'show']);
    $router->add('GET', '/resources/create', [ResourceController::class, 'create']);
    $router->add('POST', '/resources/store', [ResourceController::class, 'store']);
    $router->add('GET', '/resources/{id}/edit', [ResourceController::class, 'edit']);
    $router->add('PUT', '/resources/{id}', [ResourceController::class, 'update']);
    $router->add('DELETE', '/resources/{id}', [ResourceController::class, 'delete']);
    $router->add('DELETE', '/api/resources/{id}/translation', [ResourceController::class, 'deleteTranslation']);
    $router->add('GET', '/resources/search', [ResourceController::class, 'search']);
    $router->add('POST', '/resources/toggle-visibility/{id}', [ResourceController::class, 'toggleVisibility']);
    $router->add('GET', '/resources/view/{id}', [ResourceController::class, 'show']);
    $router->add('POST', '/resources/view/{id}/like', [ResourceController::class, 'like']);
    $router->add('POST', '/resources/{id}/delete', [ResourceController::class, 'delete']);

    // 태그명 기반 자료 검색 라우트 추가
    $router->add('GET', '/resources/tag/{tag}', [ResourceController::class, 'tagResources']);

    // New routes for /tags and /api/docs
    $router->add('GET', '/tags', [ResourceController::class, 'tags']);
    $router->add('GET', '/tags/{tag}', [ResourceController::class, 'tagResources']);
    $router->add('GET', '/api/docs', [HomeController::class, 'apiDocs']);

    // 댓글 관련 라우트
    $router->add('GET', '/api/resources/{resourceId}/comments', [CommentController::class, 'index']);
    $router->add('POST', '/api/resources/{resourceId}/comments', [CommentController::class, 'store']);
    $router->add('PUT', '/api/comments/{commentId}', [CommentController::class, 'update']);
    $router->add('DELETE', '/api/comments/{commentId}', [CommentController::class, 'destroy']);
    $router->add('GET', '/api/comments/{commentId}/replies', [CommentController::class, 'getReplies']);
    $router->add('GET', '/comments/{commentId}/translate', [CommentController::class, 'translate']);
    $router->add('POST', '/api/comments/{commentId}/report', [CommentController::class, 'report']);
    $router->add('POST', '/api/comments/{commentId}/block', [CommentController::class, 'block']);
    $router->add('POST', '/api/comments/{commentId}/reactions', [CommentController::class, 'addReaction']);
    $router->add('DELETE', '/api/comments/{commentId}/reactions', [CommentController::class, 'removeReaction']);

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
    $router->add('GET', '/profile/{userId}', [ProfileController::class, 'show']);

    // Breathing exercise routes
    $router->add('GET', '/breathing', ['App\Controllers\BreathingController', 'index']);
    $router->add('GET', '/api/breathing/patterns', ['App\Controllers\BreathingController', 'getPatterns']);
    $router->add('POST', '/api/breathing/sessions', ['App\Controllers\BreathingController', 'startSession']);
    $router->add('GET', '/api/breathing/sessions/{session_id}', ['App\Controllers\BreathingController', 'getSessionStatus']);
    $router->add('POST', '/api/breathing/sessions/{session_id}/end', ['App\Controllers\BreathingController', 'endSession']);
    $router->add('GET', '/api/breathing/sessions/{session_id}/guide', ['App\Controllers\BreathingController', 'getSessionGuide']);
    $router->add('GET', '/api/breathing/settings', ['App\Controllers\BreathingController', 'getSettings']);
    $router->add('POST', '/api/breathing/settings', ['App\Controllers\BreathingController', 'updateSettings']);

    // 이미지 업로드
    $router->add('POST', '/upload/image', ['App\Controllers\UploadController', 'uploadImage']);

    // 정적 파일 제공을 위한 라우트
    $router->add('GET', '/uploads/images/{filename}', function($params) {
        // URL에서 직접 파일명 추출
        $requestUri = $_SERVER['REQUEST_URI'];
        $filename = basename($requestUri);
        
        if (empty($filename)) {
            error_log("Empty filename requested from URI: " . $requestUri);
            http_response_code(404);
            exit;
        }
        
        $filePath = dirname(__DIR__) . '/public/uploads/images/' . $filename;
        error_log("Attempting to serve file: " . $filePath);
        
        if (!file_exists($filePath)) {
            error_log("File not found: " . $filePath);
            http_response_code(404);
            exit;
        }

        // 파일 확장자에 따른 MIME 타입 설정
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? mime_content_type($filePath);
        if (!$mimeType) {
            error_log("Unknown MIME type for file: " . $filePath);
            http_response_code(500);
            exit;
        }

        // 캐시 헤더 설정
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        
        // 파일 출력
        readfile($filePath);
        exit;
    });

    // 프로필 이미지를 위한 라우트
    $router->add('GET', '/uploads/profiles/{filename}', function($params) {
        // URL에서 직접 파일명 추출
        $requestUri = $_SERVER['REQUEST_URI'];
        $filename = basename($requestUri);
        
        if (empty($filename)) {
            error_log("Empty filename requested from URI: " . $requestUri);
            http_response_code(404);
            exit;
        }
        
        $filePath = dirname(__DIR__) . '/public/uploads/profiles/' . $filename;
        error_log("Attempting to serve profile image: " . $filePath);
        
        if (!file_exists($filePath)) {
            error_log("Profile image not found: " . $filePath);
            http_response_code(404);
            exit;
        }

        // 파일 확장자에 따른 MIME 타입 설정
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? mime_content_type($filePath);
        if (!$mimeType) {
            error_log("Unknown MIME type for profile image: " . $filePath);
            http_response_code(500);
            exit;
        }

        // 캐시 헤더 설정
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        
        // 파일 출력
        readfile($filePath);
        exit;
    });

    // API Routes
    $router->get('/api/resources/tag/{tag}', 'App\\Controller\\ApiController', 'getResourcesByTag');

    // Diary routes
    $router->get('/diary', 'App\\Controllers\\DiaryController', 'index');
    $router->get('/diary/create', 'App\\Controllers\\DiaryController', 'create');
    $router->post('/diary', 'App\\Controllers\\DiaryController', 'store');
    $router->get('/diary/search', 'App\\Controllers\\DiaryController', 'search');
    $router->post('/diary/comment', 'App\\Controllers\\DiaryController', 'storeComment');
    $router->get('/diary/{id}', 'App\\Controllers\\DiaryController', 'show');
    $router->get('/diary/{id}/edit', 'App\\Controllers\\DiaryController', 'edit');
    $router->put('/diary/{id}', 'App\\Controllers\\DiaryController', 'update');
    $router->delete('/diary/{id}', 'App\\Controllers\\DiaryController', 'delete');
    $router->post('/diary/{id}/like', 'App\\Controllers\\DiaryController', 'toggleLike');
    $router->delete('/diary/comment/{id}', 'App\\Controllers\\DiaryController', 'deleteComment');
    $router->get('/diary/{id}/comments', 'App\\Controllers\\DiaryController', 'getComments');
    $router->put('/diary/comment/{id}', 'App\\Controllers\\DiaryController', 'updateComment');

    // 404 처리 라우트 (모든 경로에 대해)
    $router->add('GET', '*', [HomeController::class, 'notFound']);
}; 