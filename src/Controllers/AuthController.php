<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;

class AuthController
{
    private $request;
    private $user;
    private $googleClient;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = new User();
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->googleClient->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->googleClient->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }

    public function showLogin()
    {
        require_once dirname(__DIR__, 2) . '/View/auth/login.php';
    }

    public function showRegister()
    {
        require_once dirname(__DIR__, 2) . '/View/auth/register.php';
    }

    public function login()
    {
        $data = $this->request->getJson();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // TODO: 데이터베이스에서 사용자 확인 및 비밀번호 검증
        // 임시로 하드코딩된 사용자 정보로 테스트
        if ($email === 'test@example.com' && $password === 'password') {
            $this->request->setSession('user', [
                'id' => 1,
                'email' => $email,
                'name' => 'Test User'
            ]);
            return json_encode(['success' => true]);
        }

        return json_encode(['error' => '이메일 또는 비밀번호가 올바르지 않습니다.'], 401);
    }

    public function register()
    {
        $data = $this->request->getJson();
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            return json_encode(['error' => '모든 필드를 입력해주세요.'], 400);
        }

        // TODO: 데이터베이스에 사용자 저장
        // 임시로 성공 응답만 반환
        return json_encode(['success' => true]);
    }

    public function google()
    {
        $authUrl = $this->googleClient->createAuthUrl();
        return json_encode(['url' => $authUrl]);
    }

    public function googleCallback()
    {
        if (isset($_GET['code'])) {
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
            $this->googleClient->setAccessToken($token);

            $oauth2 = new Google_Service_Oauth2($this->googleClient);
            $userInfo = $oauth2->userinfo->get();

            // TODO: 데이터베이스에서 사용자 확인 또는 생성
            $this->request->setSession('user', [
                'id' => 1, // 임시 ID
                'email' => $userInfo->email,
                'name' => $userInfo->name
            ]);

            header('Location: /');
            exit;
        }

        header('Location: /login');
        exit;
    }

    public function logout()
    {
        $this->request->clearSession();
        header('Location: /login');
        exit;
    }

    private function jsonResponse($data, $status = 200)
    {
        $response = new Response();
        $response->setContentType('application/json');
        $response->setStatusCode($status);
        $response->setContent(json_encode($data));
        return $response;
    }

    private function redirect($url)
    {
        $response = new Response();
        $response->setStatusCode(302);
        $response->setHeader('Location', $url);
        return $response;
    }
} 