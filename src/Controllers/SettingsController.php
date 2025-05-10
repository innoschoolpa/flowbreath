<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Core\Validator;

class SettingsController extends Controller
{
    private $user;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $db = \App\Core\Database::getInstance();
        $this->user = new User($db);
    }

    private function checkAuth()
    {
        $auth = \App\Core\Auth::getInstance();
        if (!$auth->check()) {
            $_SESSION['error'] = '로그인이 필요한 서비스입니다.';
            $_SESSION['redirect_after_login'] = '/settings';
            header('Location: /login');
            exit;
        }
        return $auth->id();
    }

    public function index()
    {
        $userId = $this->checkAuth();
        $auth = \App\Core\Auth::getInstance();
        $user = $auth->user();

        if (!$user) {
            error_log("Settings access failed - User not found or inactive. User ID: " . $userId);
            $_SESSION['error'] = '로그인 세션이 만료되었습니다. 다시 로그인 해주세요.';
            header('Location: /login');
            exit;
        }

        return $this->view('settings/index', [
            'user' => $user,
            'title' => '설정'
        ]);
    }

    public function updateProfile()
    {
        try {
            $userId = $this->checkAuth();

            if (!$this->validateCsrfToken()) {
                return $this->json(['error' => '잘못된 요청입니다.'], 400);
            }

            $name = $_POST['name'] ?? '';
            $bio = $_POST['bio'] ?? '';

            // 입력값 검증
            $validator = new Validator();
            $validator->validate([
                'name' => [
                    'required' => true,
                    'min' => 2,
                    'max' => 50,
                    'message' => '이름은 2-50자 사이로 입력해주세요.'
                ],
                'bio' => [
                    'max' => 500,
                    'message' => '자기소개는 500자 이내로 입력해주세요.'
                ]
            ]);

            // 프로필 이미지 처리
            $profileImage = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $profileImage = $this->handleProfileImageUpload($_FILES['profile_image']);
            }

            // 업데이트 데이터 준비
            $updateData = [
                'name' => $name,
                'bio' => $bio,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($profileImage) {
                $updateData['profile_image'] = $profileImage;
            }

            // 사용자 정보 업데이트
            $success = $this->user->update($userId, $updateData);
            if (!$success) {
                throw new \Exception('프로필 업데이트에 실패했습니다.');
            }

            // 세션 정보 업데이트
            $name = $name ?? '';
            if (!preg_match('/^[a-zA-Z가-힣0-9 _-]{2,50}$/u', $name)) {
                $name = '사용자';
            }
            $_SESSION['user_name'] = $name;
            if ($profileImage) {
                $_SESSION['user_avatar'] = $profileImage;
            }

            $_SESSION['success'] = '프로필이 성공적으로 업데이트되었습니다.';
            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePassword()
    {
        try {
            $userId = $this->checkAuth();

            if (!$this->validateCsrfToken()) {
                return $this->json(['error' => '잘못된 요청입니다.'], 400);
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // 입력값 검증
            $validator = new Validator();
            $validator->validate([
                'current_password' => [
                    'required' => true,
                    'message' => '현재 비밀번호를 입력해주세요.'
                ],
                'new_password' => [
                    'required' => true,
                    'min' => 8,
                    'message' => '새 비밀번호는 8자 이상이어야 합니다.'
                ],
                'confirm_password' => [
                    'required' => true,
                    'message' => '비밀번호 확인을 입력해주세요.'
                ]
            ]);

            if ($newPassword !== $confirmPassword) {
                throw new \Exception('새 비밀번호가 일치하지 않습니다.');
            }

            // 현재 비밀번호 확인
            $user = $this->user->findById($userId);
            if (!password_verify($currentPassword, $user['password'])) {
                throw new \Exception('현재 비밀번호가 일치하지 않습니다.');
            }

            // 새 비밀번호로 업데이트
            $success = $this->user->update($userId, [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new \Exception('비밀번호 변경에 실패했습니다.');
            }

            $_SESSION['success'] = '비밀번호가 성공적으로 변경되었습니다.';
            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateNotifications()
    {
        try {
            $userId = $this->checkAuth();

            if (!$this->validateCsrfToken()) {
                return $this->json(['error' => '잘못된 요청입니다.'], 400);
            }

            $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
            $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;

            $success = $this->user->update($userId, [
                'email_notifications' => $emailNotifications,
                'push_notifications' => $pushNotifications,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$success) {
                throw new \Exception('알림 설정 업데이트에 실패했습니다.');
            }

            $_SESSION['success'] = '알림 설정이 업데이트되었습니다.';
            return $this->json(['success' => true]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function handleProfileImageUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception('지원하지 않는 이미지 형식입니다. (JPEG, PNG, GIF만 가능)');
        }

        if ($file['size'] > $maxSize) {
            throw new \Exception('이미지 크기는 5MB를 초과할 수 없습니다.');
        }

        $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('이미지 업로드에 실패했습니다.');
        }

        return '/uploads/profiles/' . $fileName;
    }

    private function validateCsrfToken()
    {
        $token = $_POST['csrf_token'] ?? '';
        return $token && $token === ($_SESSION['csrf_token'] ?? '');
    }
} 