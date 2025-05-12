<?php

namespace Tests\Integration\Auth;

use Tests\Integration\TestCase;
use App\Core\Auth;
use App\Core\Session;

class AuthenticationTest extends TestCase
{
    private $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new Auth();
    }

    public function testUserRegistration()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];

        $userId = $this->auth->register($userData);
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);

        // 데이터베이스에서 사용자 확인
        $user = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        )->fetch();

        $this->assertEquals($userData['username'], $user['username']);
        $this->assertEquals($userData['email'], $user['email']);
        $this->assertTrue(password_verify($userData['password'], $user['password']));
    }

    public function testUserLogin()
    {
        // 테스트 사용자 생성
        $userId = $this->createTestUser();
        
        // 로그인 시도
        $result = $this->auth->login('test@example.com', 'password123');
        $this->assertTrue($result);

        // 세션에서 사용자 정보 확인
        $this->assertEquals($userId, $this->session->get('user_id'));
        $this->assertEquals('user', $this->session->get('user_role'));
    }

    public function testUserLogout()
    {
        // 테스트 사용자 생성 및 로그인
        $userId = $this->createTestUser();
        $this->loginAs($userId);

        // 로그아웃
        $this->auth->logout();

        // 세션에서 사용자 정보가 제거되었는지 확인
        $this->assertFalse($this->session->has('user_id'));
        $this->assertFalse($this->session->has('user_role'));
    }

    public function testPasswordReset()
    {
        // 테스트 사용자 생성
        $userId = $this->createTestUser();
        
        // 비밀번호 재설정 토큰 생성
        $token = $this->auth->createPasswordResetToken($userId);
        $this->assertNotEmpty($token);

        // 토큰 유효성 검증
        $isValid = $this->auth->validatePasswordResetToken($token);
        $this->assertTrue($isValid);

        // 새 비밀번호로 변경
        $newPassword = 'newpassword123';
        $result = $this->auth->resetPassword($token, $newPassword);
        $this->assertTrue($result);

        // 새 비밀번호로 로그인 가능한지 확인
        $loginResult = $this->auth->login('test@example.com', $newPassword);
        $this->assertTrue($loginResult);
    }

    public function testInvalidLoginAttempts()
    {
        // 테스트 사용자 생성
        $this->createTestUser();

        // 잘못된 비밀번호로 로그인 시도
        for ($i = 0; $i < 5; $i++) {
            $result = $this->auth->login('test@example.com', 'wrongpassword');
            $this->assertFalse($result);
        }

        // 계정 잠금 확인
        $isLocked = $this->auth->isAccountLocked('test@example.com');
        $this->assertTrue($isLocked);

        // 잠긴 계정으로 로그인 시도
        $result = $this->auth->login('test@example.com', 'password123');
        $this->assertFalse($result);
    }

    public function testSessionExpiration()
    {
        // 테스트 사용자 생성 및 로그인
        $userId = $this->createTestUser();
        $this->loginAs($userId);

        // 세션 만료 시간을 1초로 설정
        $this->session->setGcMaxLifetime(1);
        sleep(2);

        // 세션 만료 확인
        $this->assertFalse($this->auth->isAuthenticated());
    }

    public function testRememberMe()
    {
        // 테스트 사용자 생성
        $userId = $this->createTestUser();
        
        // Remember Me 옵션으로 로그인
        $result = $this->auth->login('test@example.com', 'password123', true);
        $this->assertTrue($result);

        // Remember Me 토큰 확인
        $rememberToken = $this->session->get('remember_token');
        $this->assertNotEmpty($rememberToken);

        // 세션 만료 후에도 로그인 상태 유지
        $this->session->setGcMaxLifetime(1);
        sleep(2);

        $this->assertTrue($this->auth->isAuthenticated());
    }
} 