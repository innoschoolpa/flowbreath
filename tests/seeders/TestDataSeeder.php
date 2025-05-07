<?php

declare(strict_types=1);

namespace Tests\Seeders;

use PDO;

class TestDataSeeder
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function seed(): void
    {
        // Create admin user
        $adminId = $this->createUser([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);

        // Create regular users
        $user1Id = $this->createUser([
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password_hash' => password_hash('user123', PASSWORD_DEFAULT)
        ]);

        $user2Id = $this->createUser([
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password_hash' => password_hash('user123', PASSWORD_DEFAULT)
        ]);

        // Create resources
        $resource1Id = $this->createResource([
            'title' => 'PHP 기초 강좌',
            'content' => 'PHP의 기본 문법과 사용법에 대해 알아봅시다.',
            'user_id' => $adminId,
            'is_public' => true
        ]);

        $resource2Id = $this->createResource([
            'title' => 'MySQL 데이터베이스 설계',
            'content' => '효율적인 데이터베이스 설계 방법을 배워봅시다.',
            'user_id' => $user1Id,
            'is_public' => true
        ]);

        $resource3Id = $this->createResource([
            'title' => '비공개 노트',
            'content' => '이것은 비공개 노트입니다.',
            'user_id' => $user2Id,
            'is_public' => false
        ]);

        // Create comments
        $this->createComment([
            'content' => '정말 유용한 강좌네요!',
            'user_id' => $user1Id,
            'resource_id' => $resource1Id
        ]);

        $this->createComment([
            'content' => '추가 설명이 필요합니다.',
            'user_id' => $user2Id,
            'resource_id' => $resource1Id
        ]);

        // Create tags
        $phpTagId = $this->createTag([
            'name' => 'php',
            'description' => 'PHP 관련 자료'
        ]);

        $mysqlTagId = $this->createTag([
            'name' => 'mysql',
            'description' => 'MySQL 관련 자료'
        ]);

        $tutorialTagId = $this->createTag([
            'name' => 'tutorial',
            'description' => '튜토리얼 자료'
        ]);

        // Associate tags with resources
        $this->associateTagWithResource($phpTagId, $resource1Id);
        $this->associateTagWithResource($tutorialTagId, $resource1Id);
        $this->associateTagWithResource($mysqlTagId, $resource2Id);
        $this->associateTagWithResource($tutorialTagId, $resource2Id);
    }

    private function createUser(array $data): int
    {
        $sql = "INSERT INTO users (username, email, password_hash, role, created_at) 
                VALUES (:username, :email, :password_hash, :role, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }

    private function createResource(array $data): int
    {
        $sql = "INSERT INTO resources (title, content, user_id, is_public, created_at) 
                VALUES (:title, :content, :user_id, :is_public, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }

    private function createComment(array $data): int
    {
        $sql = "INSERT INTO comments (content, user_id, resource_id, created_at) 
                VALUES (:content, :user_id, :resource_id, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }

    private function createTag(array $data): int
    {
        $sql = "INSERT INTO tags (name, description, created_at) 
                VALUES (:name, :description, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->pdo->lastInsertId();
    }

    private function associateTagWithResource(int $tagId, int $resourceId): void
    {
        $sql = "INSERT INTO resource_tags (resource_id, tag_id, created_at) 
                VALUES (:resource_id, :tag_id, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'resource_id' => $resourceId,
            'tag_id' => $tagId
        ]);
    }
} 