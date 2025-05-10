<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Session;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Database;
use App\Core\Request;
use Google_Client;
use Google_Service_Oauth2;

class GoogleAuthController extends BaseController
{
    private $client;
    private $userModel;
    private $session;
    private $logger;
    private $db;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->db = Database::getInstance();
        $this->userModel = new User($this->db);
        $this->session = Session::getInstance();
        $this->logger = Logger::getInstance();
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient()
    {
        try {
            $this->client = new Google_Client();
            $this->client->setClientId(Config::get('google.client_id'));
            $this->client->setClientSecret(Config::get('google.client_secret'));
            $this->client->setRedirectUri(Config::get('google.redirect_uri'));
            
            // 올바른 스코프 URL 사용
            $this->client->addScope('https://www.googleapis.com/auth/userinfo.email');
            $this->client->addScope('https://www.googleapis.com/auth/userinfo.profile');
            $this->client->addScope('openid');
            
            $this->client->setIncludeGrantedScopes(true);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('none');
            
            // 디버그 로깅 추가
            $this->logger->info('Google client initialized', [
                'client_id' => Config::get('google.client_id'),
                'redirect_uri' => Config::get('google.redirect_uri'),
                'scopes' => $this->client->getScopes()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Google client initialization failed: ' . $e->getMessage());
            throw new \Exception('Google authentication service is currently unavailable');
        }
    }

    public function redirectToGoogle()
    {
        try {
            // Generate and store state parameter for CSRF protection
            $state = bin2hex(random_bytes(16));
            $this->session->set('google_auth_state', $state);
            
            // Google_Client를 사용하여 인증 URL 생성
            $this->client->setState($state);
            $authUrl = $this->client->createAuthUrl();
            
            if (!$authUrl) {
                throw new \Exception('Failed to generate Google auth URL');
            }
            
            // 디버그 로깅 추가
            $this->logger->info('Redirecting to Google auth URL', [
                'auth_url' => $authUrl,
                'state' => $state
            ]);
            
            header('Location: ' . $authUrl);
            exit;
        } catch (\Exception $e) {
            $this->logger->error('Google redirect failed: ' . $e->getMessage());
            $this->session->set('error', 'Failed to initiate Google login. Please try again.');
            header('Location: /login');
            exit;
        }
    }

    public function handleCallback()
    {
        try {
            // Verify state parameter
            $storedState = $this->session->get('google_auth_state');
            $receivedState = $_GET['state'] ?? '';
            
            if (!$storedState || !$receivedState || $storedState !== $receivedState) {
                throw new \Exception('Invalid state parameter');
            }
            
            // Clear the state parameter
            $this->session->remove('google_auth_state');

            if (!isset($_GET['code'])) {
                // 자동 로그인 실패 시 일반 로그인 페이지로 리다이렉트
                $this->logger->info('Silent login failed, redirecting to login page');
                header('Location: /login');
                exit;
            }

            // Exchange authorization code for access token
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (isset($token['error'])) {
                throw new \Exception('Token error: ' . $token['error']);
            }

            $this->client->setAccessToken($token);

            // Get user info from Google
            $oauth2 = new Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            // 마지막 로그인 이메일 저장
            $this->session->set('last_google_email', $userInfo->getEmail());

            // Google에서 받아온 이름이 없으면 이메일 앞부분을 fallback으로 사용
            $googleName = trim($userInfo->getName());
            if (!$googleName) {
                $googleName = explode('@', $userInfo->getEmail())[0];
            }

            // Prepare user data
            $userData = [
                'email' => $userInfo->getEmail(),
                'name' => $googleName,
                'google_id' => $userInfo->getId(),
                'avatar' => $userInfo->getPicture(),
                'last_login' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'email_verified_at' => date('Y-m-d H:i:s')
            ];

            // Check if user exists
            $existingUser = $this->userModel->findByEmail($userData['email']);
            
            if ($existingUser) {
                // Update last login time
                $this->userModel->updateLastLogin($existingUser['id']);
                
                // Set session for existing user
                $this->session->set('user_id', $existingUser['id']);
                $this->session->set('user_name', $existingUser['name']);
                $this->session->set('user_email', $existingUser['email']);
                $this->session->set('user_avatar', $existingUser['avatar'] ?? $existingUser['profile_image'] ?? null);
                $this->session->set('is_google_user', true);
                $this->session->set('user_status', 'active');

                // Log successful login
                $this->logger->info('Google login successful', [
                    'user_id' => $existingUser['id'],
                    'email' => $existingUser['email'],
                    'google_id' => $existingUser['google_id']
                ]);

                // 즉시 리소스 페이지로 리다이렉트
                header('Location: /resources');
                exit;
            } else {
                // Create new user
                $userId = $this->userModel->createUser($this->db, [
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'google_id' => $userData['google_id'],
                    'avatar' => $userData['avatar'],
                    'password' => bin2hex(random_bytes(16)), // Random password for Google users
                    'status' => 'active',
                    'email_verified_at' => $userData['email_verified_at']
                ]);

                // Verify user was created successfully
                if (!$userId) {
                    throw new \Exception('Failed to create user');
                }

                // Get fresh user data
                $user = $this->userModel->findById($userId);
                if (!$user) {
                    throw new \Exception('Failed to retrieve user data after creation');
                }

                // Set session for new user
                $this->session->set('user_id', $userId);
                $this->session->set('user_name', $user['name']);
                $this->session->set('user_email', $user['email']);
                $this->session->set('user_avatar', $user['avatar'] ?? $user['profile_image'] ?? null);
                $this->session->set('is_google_user', true);
                $this->session->set('user_status', 'active');
                $this->session->set('needs_terms_agreement', true);

                // Log successful registration
                $this->logger->info('Google registration successful', [
                    'user_id' => $userId,
                    'email' => $user['email'],
                    'google_id' => $user['google_id']
                ]);

                // Redirect to terms agreement page for new users
                header('Location: /terms-agreement');
                exit;
            }
        } catch (\Exception $e) {
            $this->logger->error('Google login failed: ' . $e->getMessage());
            $this->session->set('error', 'Google 로그인 중 오류가 발생했습니다. 다시 시도해주세요.');
            header('Location: /login');
            exit;
        }
    }
} 