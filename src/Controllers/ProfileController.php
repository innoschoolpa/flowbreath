<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\Resource;
use App\Core\Validator;
use App\Core\Auth;

class ProfileController extends Controller
{
    private $user;
    private $resource;
    private $session;
    private $auth;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->session = new Session();
        $db = \App\Core\Database::getInstance();
        $this->user = new User($db);
        $this->resource = new Resource($db);
        $this->auth = new Auth();
    }

    /**
     * 로그인 체크 헬퍼 메서드
     */
    private function checkAuth()
    {
        $auth = \App\Core\Auth::getInstance();
        if (!$auth->check()) {
            $_SESSION['error'] = '로그인이 필요한 서비스입니다.';
            $_SESSION['redirect_after_login'] = '/profile';
            header('Location: /login');
            exit;
        }
        return $auth->id();
    }

    /**
     * 사용자 활동 통계 계산
     */
    private function calculateUserStats($userId)
    {
        $stats = [
            'total_resources' => 0,
            'total_likes' => 0,
            'total_views' => 0,
            'total_comments' => 0,
            'recent_activity' => [],
            'popular_resources' => []
        ];

        // 리소스 통계
        $resources = $this->resource->findByUserId($userId);
        $stats['total_resources'] = count($resources);
        
        // 좋아요 통계
        $stats['total_likes'] = $this->resource->getTotalLikesByUserId($userId);
        
        // 조회수 통계
        $stats['total_views'] = $this->resource->getTotalViewsByUserId($userId);
        
        // 댓글 통계
        $stats['total_comments'] = $this->resource->getTotalCommentsByUserId($userId);
        
        // 최근 활동
        $stats['recent_activity'] = $this->resource->getRecentActivityByUserId($userId, 5);
        
        // 인기 리소스
        $stats['popular_resources'] = $this->resource->getPopularResourcesByUserId($userId, 3);

        return $stats;
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = '로그인이 필요합니다.';
            header('Location: /login');
            exit;
        }
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'profile_image' => $_SESSION['user_avatar'] ?? null,
            'bio' => $_SESSION['user_bio'] ?? '',
            'social_links' => $_SESSION['user_social_links'] ?? '',
        ];
        // 사용자의 리소스 목록 가져오기
        $resources = $this->resource->findByUserId((int)$_SESSION['user_id']);
        // 사용자의 통계 정보 계산
        $stats = $this->calculateUserStats((int)$_SESSION['user_id']);
        return $this->view('profile/index', [
            'user' => $user,
            'resources' => $resources,
            'stats' => $stats,
            'title' => '프로필'
        ]);
    }

    public function show($userId)
    {
        // 다른 사용자의 프로필을 볼 때는 로그인이 필수는 아님
        $userId = (int)$userId;
        $user = $this->user->findById($userId);

        if (!$user) {
            $_SESSION['error'] = '사용자를 찾을 수 없습니다.';
            header('Location: /resources');
            exit;
        }

        // 자신의 프로필을 보려고 할 때는 index로 리다이렉트
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId) {
            header('Location: /profile');
            exit;
        }

        // 사용자의 공개 리소스 목록 가져오기
        $resources = $this->resource->findPublicByUserId($userId);
        
        // 사용자의 통계 정보 계산
        $stats = [
            'total_resources' => count($resources),
            'total_likes' => $this->resource->getTotalLikesByUserId($userId),
            'total_views' => $this->resource->getTotalViewsByUserId($userId)
        ];

        return $this->view('profile/user', [
            'profile_user' => $user,
            'resources' => $resources,
            'stats' => $stats,
            'title' => $user['name'] . '의 프로필'
        ]);
    }

    public function showComplete(): Response
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // Get user data
        $user = $this->user->findById($_SESSION['user_id']);
        if (!$user) {
            return $this->redirect('/login');
        }

        return $this->view('auth/profile-complete', [
            'user' => $user
        ]);
    }

    /**
     * 프로필 완성 처리
     */
    public function complete()
    {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                error_log("Profile completion attempted without login");
                return $this->json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
            }

            $userId = $_SESSION['user_id'];
            error_log("Processing profile completion for user ID: " . $userId);

            // Validate input data
            $name = trim($_POST['name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            error_log("Received data - Name: " . $name . ", Bio length: " . strlen($bio));

            // Validate name
            if (empty($name)) {
                error_log("Name validation failed: Empty name");
                return $this->json(['success' => false, 'message' => '이름을 입력해주세요.'], 400);
            }

            if (mb_strlen($name) > 100) {
                error_log("Name validation failed: Name too long");
                return $this->json(['success' => false, 'message' => '이름은 100자를 초과할 수 없습니다.'], 400);
            }

            // Validate bio (allow empty)
            if (mb_strlen($bio) > 65535) {
                error_log("Bio validation failed: Bio too long");
                return $this->json(['success' => false, 'message' => '자기소개가 너무 깁니다.'], 400);
            }

            // Prepare update data
            $updateData = [
                'name' => $name,
                'bio' => $bio,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            error_log("Attempting to update user profile with data: " . json_encode($updateData));

            // Update user profile
            try {
                $updatedUser = $this->user->update($userId, $updateData);

                if (!$updatedUser) {
                    error_log("Profile update failed for user ID: " . $userId);
                    return $this->json(['success' => false, 'message' => '프로필 저장에 실패했습니다. 다시 시도해주세요.'], 500)->send();
                }

                error_log("Profile updated successfully for user ID: " . $userId);
                error_log("Updated user data: " . json_encode($updatedUser));

                // Update session data
                $_SESSION['user_name'] = $updatedUser['name'];
                if (isset($updatedUser['profile_image'])) {
                    $_SESSION['user_avatar'] = $updatedUser['profile_image'];
                }
                error_log("Session data updated successfully");

                // Return success response with 200 status code
                return $this->json([
                    'success' => true,
                    'message' => '프로필이 성공적으로 저장되었습니다.',
                    'redirect' => '/dashboard'
                ], 200)->send();

            } catch (\PDOException $e) {
                error_log("Database error during profile completion: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                error_log("Error Info: " . json_encode($e->errorInfo));
                
                // Check for specific database errors
                if (strpos($e->getMessage(), 'Data too long') !== false) {
                    return $this->json(['success' => false, 'message' => '입력한 내용이 너무 깁니다. 더 짧게 입력해주세요.'], 400);
                } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    return $this->json(['success' => false, 'message' => '이미 사용 중인 정보입니다. 다른 정보를 입력해주세요.'], 400);
                } else {
                    return $this->json(['success' => false, 'message' => '프로필 저장 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'], 500);
                }
            }

        } catch (\Exception $e) {
            error_log("Error during profile completion: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->json(['success' => false, 'message' => '프로필 저장 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'], 500);
        }
    }

    /**
     * 프로필 이미지 업로드 처리
     */
    public function uploadProfileImage()
    {
        try {
            $userId = $this->checkAuth();
            
            if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
                return $this->json(['error' => '이미지 업로드에 실패했습니다.'], 400);
            }

            $file = $_FILES['profile_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            // 파일 타입 검증
            if (!in_array($file['type'], $allowedTypes)) {
                return $this->json(['error' => '지원하지 않는 이미지 형식입니다.'], 400);
            }

            // 파일 크기 검증
            if ($file['size'] > $maxSize) {
                return $this->json(['error' => '이미지 크기는 5MB를 초과할 수 없습니다.'], 400);
            }

            // 이미지 최적화
            $image = null;
            switch ($file['type']) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file['tmp_name']);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($file['tmp_name']);
                    break;
            }

            if (!$image) {
                return $this->json(['error' => '이미지 처리에 실패했습니다.'], 400);
            }

            // 이미지 크기 조정
            $maxDimension = 800;
            $width = imagesx($image);
            $height = imagesy($image);

            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = floor($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = floor($width * ($maxDimension / $height));
                }

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $image = $resized;
            }

            // 저장 경로 설정
            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = uniqid('profile_') . '.jpg';
            $filepath = $uploadDir . $filename;

            // JPEG로 저장 (최적화)
            imagejpeg($image, $filepath, 85);
            imagedestroy($image);

            // 이전 프로필 이미지 삭제
            $user = $this->user->findById($userId);
            if ($user && $user['profile_image']) {
                $oldImagePath = __DIR__ . '/../../public' . $user['profile_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // DB 업데이트
            $imageUrl = '/uploads/profiles/' . $filename;
            $this->user->update($userId, ['profile_image' => $imageUrl]);

            return $this->json([
                'success' => true,
                'image_url' => $imageUrl,
                'message' => '프로필 이미지가 업데이트되었습니다.'
            ]);

        } catch (\Exception $e) {
            error_log("Profile image upload error: " . $e->getMessage());
            return $this->json(['error' => '이미지 업로드 중 오류가 발생했습니다.'], 500);
        }
    }

    public function update()
    {
        try {
            if (!$this->auth->check()) {
                return $this->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $userId = $this->auth->id();
            $name = trim($_POST['name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            // 입력값 검증
            if (empty($name)) {
                return $this->json(['error' => '이름은 필수 입력 항목입니다.'], 400);
            }

            if (mb_strlen($name) > 100) {
                return $this->json(['error' => '이름은 100자를 초과할 수 없습니다.'], 400);
            }

            if (mb_strlen($bio) > 1000) {
                return $this->json(['error' => '자기소개는 1000자를 초과할 수 없습니다.'], 400);
            }

            // 사용자 정보 업데이트
            $updateData = [
                'name' => $name,
                'bio' => $bio,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updatedUser = $this->user->update($userId, $updateData);
            if (!$updatedUser) {
                throw new \Exception('프로필 업데이트에 실패했습니다.');
            }

            // 세션 정보 업데이트
            $_SESSION['user_name'] = $updatedUser['name'];
            if (isset($updatedUser['profile_image'])) {
                $_SESSION['user_avatar'] = $updatedUser['profile_image'];
            }

            return $this->json([
                'success' => true,
                'message' => '프로필이 성공적으로 업데이트되었습니다.',
                'user' => $updatedUser
            ]);

        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return $this->json([
                'error' => '프로필 업데이트 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    public function updateImage()
    {
        if (!$this->auth->check()) {
            return (new Response())->redirect('/login');
        }

        $userId = (int)$_SESSION['user_id'];
        $file = $this->request->getFile('profile_image');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return (new Response())->json([
                'success' => false,
                'message' => '이미지 업로드에 실패했습니다.'
            ], 400);
        }

        try {
            // 이미지 유효성 검사
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new \Exception('지원하지 않는 이미지 형식입니다.');
            }

            // 이미지 크기 제한 (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new \Exception('이미지 크기는 5MB를 초과할 수 없습니다.');
            }

            // 이미지 저장
            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_') . '.' . $extension;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('이미지 저장에 실패했습니다.');
            }

            // DB 업데이트
            $imageUrl = '/uploads/profiles/' . $filename;
            $this->user->update($userId, [
                'profile_image' => $imageUrl
            ]);

            // 세션 업데이트
            $_SESSION['user_avatar'] = $imageUrl;

            return (new Response())->json([
                'success' => true,
                'message' => '프로필 이미지가 성공적으로 업데이트되었습니다.',
                'imageUrl' => $imageUrl
            ]);
        } catch (\Exception $e) {
            error_log("Profile image update error: " . $e->getMessage());
            return (new Response())->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateSocial()
    {
        if (!$this->auth->check()) {
            return (new Response())->redirect('/login');
        }

        $userId = (int)$_SESSION['user_id'];
        $platform = $this->request->getPost('platform');
        $url = $this->request->getPost('url');

        try {
            // URL 유효성 검사
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \Exception('유효하지 않은 URL입니다.');
            }

            // 현재 소셜 링크 가져오기
            $currentLinks = json_decode($_SESSION['user_social_links'] ?? '{}', true);
            $currentLinks[$platform] = $url;

            // DB 업데이트
            $this->user->update($userId, [
                'social_links' => json_encode($currentLinks)
            ]);

            // 세션 업데이트
            $_SESSION['user_social_links'] = json_encode($currentLinks);

            return (new Response())->json([
                'success' => true,
                'message' => '소셜 링크가 성공적으로 업데이트되었습니다.'
            ]);
        } catch (\Exception $e) {
            error_log("Social links update error: " . $e->getMessage());
            return (new Response())->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 