<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Core\Validator;

class ProfileController extends Controller
{
    private User $user;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $db = \App\Core\Database::getInstance();
        $this->user = new User($db);
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
                $_SESSION['name'] = $updatedUser['name'];
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
} 