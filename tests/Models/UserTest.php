<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\User;
use App\Exceptions\ModelException;
use Tests\TestCase;
use PDOException;

class UserTest extends TestCase
{
    private User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User($this->pdo);
    }

    public function testCreateUser(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user'
        ];

        $userId = $this->userModel->create($data);
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);

        $user = $this->userModel->find($userId);
        $this->assertEquals($data['username'], $user['username']);
        $this->assertEquals($data['email'], $user['email']);
        $this->assertEquals($data['role'], $user['role']);
    }

    public function testCreateUserWithInvalidData(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Failed to create user');

        $data = [
            'username' => 'testuser',
            // Missing required email field
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user'
        ];

        $this->userModel->create($data);
    }

    public function testFindUser(): void
    {
        $userId = $this->createTestUser();
        $user = $this->userModel->find($userId);

        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testFindNonExistentUser(): void
    {
        $user = $this->userModel->find(99999);
        $this->assertNull($user);
    }

    public function testUpdateUser(): void
    {
        $userId = $this->createTestUser();
        $newData = [
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ];

        $result = $this->userModel->update($userId, $newData);
        $this->assertTrue($result);

        $updatedUser = $this->userModel->find($userId);
        $this->assertEquals($newData['username'], $updatedUser['username']);
        $this->assertEquals($newData['email'], $updatedUser['email']);
    }

    public function testDeleteUser(): void
    {
        $userId = $this->createTestUser();
        $result = $this->userModel->delete($userId);
        $this->assertTrue($result);

        $deletedUser = $this->userModel->find($userId);
        $this->assertNull($deletedUser);
    }

    public function testFindByEmail(): void
    {
        $userId = $this->createTestUser();
        $user = $this->userModel->findByEmail('test@example.com');

        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals('test@example.com', $user['email']);
    }

    public function testFindByNonExistentEmail(): void
    {
        $user = $this->userModel->findByEmail('nonexistent@example.com');
        $this->assertNull($user);
    }

    public function testFindByGoogleId(): void
    {
        $googleId = 'google123';
        $userId = $this->createTestUser(['google_id' => $googleId]);
        
        $user = $this->userModel->findByGoogleId($googleId);
        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals($googleId, $user['google_id']);
    }

    public function testFindByNonExistentGoogleId(): void
    {
        $user = $this->userModel->findByGoogleId('nonexistent');
        $this->assertNull($user);
    }

    public function testUpdateLastLogin(): void
    {
        $userId = $this->createTestUser();
        $result = $this->userModel->updateLastLogin($userId);
        $this->assertTrue($result);

        $user = $this->userModel->find($userId);
        $this->assertNotNull($user['last_login']);
    }

    public function testUpdateLastLoginWithInvalidId(): void
    {
        $result = $this->userModel->updateLastLogin(99999);
        $this->assertFalse($result);
    }
} 