<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Controller;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;
use App\Core\Session;
use App\Core\Validator;
use PDO;

class AuthController extends Controller
{
    private User $user;
    private GoogleClient $googleClient;
    private Session $session;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_TIMEOUT = 300; // 5 minutes

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $db = \App\Core\Database::getInstance();
        $this->user = new User($db);
        $this->session = new Session();
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient(): void
    {
        try {
            if (empty($_ENV['GOOGLE_CLIENT_ID']) || empty($_ENV['GOOGLE_CLIENT_SECRET']) || empty($_ENV['GOOGLE_REDIRECT_URI'])) {
                throw new \Exception('Missing required Google OAuth configuration');
            }
            
            $this->googleClient = new GoogleClient();
            $this->googleClient->setClientId($_ENV['GOOGLE_CLIENT_ID']);
            $this->googleClient->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
            $this->googleClient->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
            
            // Set application name
            $this->googleClient->setApplicationName('FlowBreath');
            
            // Set scopes
            $this->googleClient->addScope('email');
            $this->googleClient->addScope('profile');
            $this->googleClient->addScope('openid');
            
            // Set access type and prompt
            $this->googleClient->setAccessType('offline');
            $this->googleClient->setIncludeGrantedScopes(true);
            $this->googleClient->setPrompt('consent');
            
        } catch (\Exception $e) {
            error_log("Failed to initialize Google client: " . $e->getMessage());
            throw new \Exception('Google authentication configuration error: ' . $e->getMessage());
        }
    }

    private function checkLoginAttempts(): bool
    {
        $attempts = $this->session->get('login_attempts', 0);
        $lastAttempt = $this->session->get('last_login_attempt', 0);
        $currentTime = time();

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            if ($currentTime - $lastAttempt < self::LOGIN_TIMEOUT) {
                return false;
            }
            // Reset attempts after timeout
            $this->session->set('login_attempts', 0);
        }

        return true;
    }

    private function incrementLoginAttempts(): void
    {
        $attempts = $this->session->get('login_attempts', 0);
        $this->session->set('login_attempts', $attempts + 1);
        $this->session->set('last_login_attempt', time());
    }

    public function showLogin(): Response
    {
        return $this->view('auth/login');
    }

    public function showRegister(): Response
    {
        return $this->view('auth/register');
    }

    public function register(): Response
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        // Get and validate input
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $terms = isset($_POST['terms']);

        // Validate input
        $validator = new Validator();
        $validator->validate([
            'username' => [
                'required' => true,
                'min' => 3,
                'max' => 50,
                'pattern' => '/^[a-zA-Z0-9_]+$/',
                'message' => '사용자 이름은 3-50자의 영문, 숫자, 언더스코어만 사용 가능합니다.'
            ],
            'email' => [
                'required' => true,
                'email' => true,
                'max' => 255,
                'message' => '유효한 이메일 주소를 입력해주세요.'
            ],
            'password' => [
                'required' => true,
                'min' => 8,
                'message' => '비밀번호는 최소 8자 이상이어야 합니다.'
            ],
            'terms' => [
                'required' => true,
                'message' => '이용약관에 동의해주세요.'
            ]
        ]);

        if ($password !== $passwordConfirmation) {
            return $this->json(['error' => '비밀번호가 일치하지 않습니다.'], 400);
        }

        // Check if username or email already exists
        $existingUser = $this->user->findByEmailOrUsername($username);
        if ($existingUser) {
            return $this->json(['error' => '이미 사용 중인 사용자 이름입니다.'], 400);
        }

        $existingUser = $this->user->findByEmail($email);
        if ($existingUser) {
            return $this->json(['error' => '이미 사용 중인 이메일입니다.'], 400);
        }

        try {
            // Create new user
            $userId = $this->user->createUser(
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            );

            if (!$userId) {
                throw new \Exception('Failed to create user');
            }

            // Log the user in
            $this->session->set('user_id', $userId);
            $this->session->set('username', $username);

            return $this->json([
                'success' => true,
                'redirect' => '/'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => '회원가입 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    public function login(): Response
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // 입력값 검증
            if (empty($email) || empty($password)) {
                throw new \Exception('이메일과 비밀번호를 모두 입력해주세요.');
            }

            // 사용자 찾기
            $user = $this->user->findByEmail($email);
            if (!$user) {
                throw new \Exception('이메일 또는 비밀번호가 일치하지 않습니다.');
            }

            // 비밀번호 확인
            if (!password_verify($password, $user['password'])) {
                throw new \Exception('이메일 또는 비밀번호가 일치하지 않습니다.');
            }

            // 세션에 사용자 정보 저장
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar'] = $user['profile_image'] ?? null;
            $_SESSION['is_admin'] = $user['is_admin'] ?? false;

            // CSRF 토큰 생성
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // 로그인 성공 메시지
            $_SESSION['success'] = '로그인되었습니다.';

            // 리다이렉트 URL 확인
            $redirect = $_SESSION['redirect_after_login'] ?? '/';
            unset($_SESSION['redirect_after_login']); // 사용 후 삭제

            // 디버그 로그
            error_log('Login successful for user: ' . $user['id']);
            error_log('Session data: ' . print_r($_SESSION, true));

            return $this->json([
                'success' => true,
                'redirect' => $redirect
            ]);

        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function logout(): Response
    {
        $this->session->clear();
        return $this->redirect('/');
    }

    public function google(): Response
    {
        try {
            if (!$this->checkLoginAttempts()) {
                return $this->redirect('/login?error=' . urlencode('너무 많은 로그인 시도가 있었습니다. 잠시 후 다시 시도해주세요.'));
            }

            // Generate and store state parameter
            $state = bin2hex(random_bytes(32));
            $this->session->set('google_oauth_state', $state);
            
            // Set state parameter
            $this->googleClient->setState($state);
            
            // Create auth URL
            $authUrl = $this->googleClient->createAuthUrl();
            
            if (!$authUrl) {
                throw new \Exception('Failed to generate Google auth URL');
            }

            // Redirect to Google's OAuth page
            return $this->redirect($authUrl);
        } catch (\Exception $e) {
            error_log("Failed to start Google OAuth: " . $e->getMessage());
            return $this->redirect('/login?error=' . urlencode('Google 로그인을 시작할 수 없습니다. 잠시 후 다시 시도해주세요.'));
        }
    }

    public function googleCallback(): Response
    {
        try {
            $client = $this->getGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (isset($token['error'])) {
                throw new \Exception('Google 로그인 중 오류가 발생했습니다: ' . $token['error_description']);
            }

            $oauth2 = new \Google_Service_Oauth2($client);
            $googleUser = $oauth2->userinfo->get();

            // 사용자 정보 가져오기 또는 생성
            $user = $this->user->findByEmail($googleUser->email);
            
            if (!$user) {
                // 새 사용자 생성
                $userData = [
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                    'profile_image' => $googleUser->picture,
                    'google_id' => $googleUser->id,
                    'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                    'email_verified_at' => date('Y-m-d H:i:s')
                ];
                
                $userId = $this->user->create($userData);
                $user = $this->user->findById($userId);
            }

            // 세션에 사용자 정보 저장
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar'] = $user['profile_image'] ?? null;
            $_SESSION['is_admin'] = $user['is_admin'] ?? false;

            // CSRF 토큰 생성
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // 디버그 로그
            error_log('Google login successful for user: ' . $user['id']);
            error_log('Session data: ' . print_r($_SESSION, true));

            // 추가 정보가 필요한지 확인
            if (empty($user['bio'])) {
                header('Location: /auth/additional-info');
                exit;
            }

            header('Location: /resources');
            exit;

        } catch (\Exception $e) {
            error_log('Google login error: ' . $e->getMessage());
            $_SESSION['error'] = 'Google 로그인 중 오류가 발생했습니다.';
            header('Location: /login');
            exit;
        }
    }

    private function generateUniqueUsername(string $name): string
    {
        $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
        $username = $baseUsername;
        $counter = 1;

        while ($this->user->findByEmailOrUsername($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    private function validateCsrfToken(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return $token && $token === ($_SESSION['csrf_token'] ?? '');
    }

    /**
     * 사용자 프로필 업데이트
     */
    public function updateProfile(): Response
    {
        try {
            if (!$this->session->get('user_id')) {
                return $this->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $userId = $this->session->get('user_id');
            $bio = $_POST['bio'] ?? '';
            $name = $_POST['name'] ?? '';

            // 입력값 검증
            $validator = new Validator();
            $validator->validate([
                'bio' => [
                    'max' => 1000,
                    'message' => '자기소개는 1000자 이내로 입력해주세요.'
                ],
                'name' => [
                    'required' => true,
                    'min' => 2,
                    'max' => 100,
                    'message' => '이름은 2-100자 사이로 입력해주세요.'
                ]
            ]);

            // 프로필 업데이트
            $updateData = [
                'bio' => $bio,
                'name' => $name,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $success = $this->user->update($userId, $updateData);
            if (!$success) {
                throw new \Exception('프로필 업데이트에 실패했습니다.');
            }

            // 세션 정보 업데이트
            $this->session->set('username', $name);

            return $this->json([
                'success' => true,
                'message' => '프로필이 성공적으로 업데이트되었습니다.'
            ]);
        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return $this->json([
                'error' => '프로필 업데이트 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * Google 로그인 후 추가 정보 입력 페이지 표시
     */
    public function showAdditionalInfo(): Response
    {
        if (!$this->session->get('user_id')) {
            return $this->redirect('/login');
        }

        $user = $this->user->findById($this->session->get('user_id'));
        if (!$user) {
            return $this->redirect('/login');
        }

        return $this->view('auth/additional-info', [
            'user' => $user
        ]);
    }

    /**
     * 추가 정보 저장
     */
    public function saveAdditionalInfo(): Response
    {
        try {
            if (!$this->session->get('user_id')) {
                return $this->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $userId = $this->session->get('user_id');
            $bio = $_POST['bio'] ?? '';
            $name = $_POST['name'] ?? '';

            // 입력값 검증
            $validator = new Validator();
            $validator->validate([
                'bio' => [
                    'max' => 1000,
                    'message' => '자기소개는 1000자 이내로 입력해주세요.'
                ],
                'name' => [
                    'required' => true,
                    'min' => 2,
                    'max' => 100,
                    'message' => '이름은 2-100자 사이로 입력해주세요.'
                ]
            ]);

            // 프로필 업데이트
            $updateData = [
                'bio' => $bio,
                'name' => $name,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $success = $this->user->update($userId, $updateData);
            if (!$success) {
                throw new \Exception('추가 정보 저장에 실패했습니다.');
            }

            // 세션 정보 업데이트
            $this->session->set('username', $name);

            return $this->json([
                'success' => true,
                'message' => '추가 정보가 성공적으로 저장되었습니다.',
                'redirect' => '/dashboard'
            ]);
        } catch (\Exception $e) {
            error_log("Additional info save error: " . $e->getMessage());
            return $this->json([
                'error' => '추가 정보 저장 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    private function getGoogleClient(): GoogleClient
    {
        return $this->googleClient;
    }
} 