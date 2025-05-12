<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Comment;
use App\Exceptions\ModelException;
use Tests\TestCase;
use PDOException;

class CommentTest extends TestCase
{
    private Comment $commentModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentModel = new Comment($this->pdo);
    }

    public function testCreateComment(): void
    {
        $userId = $this->createTestUser();
        $resourceId = $this->createTestResource();

        $data = [
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'Test comment content'
        ];

        $commentId = $this->commentModel->create($data);
        $this->assertIsInt($commentId);
        $this->assertGreaterThan(0, $commentId);

        $comment = $this->commentModel->find($commentId);
        $this->assertEquals($data['user_id'], $comment['user_id']);
        $this->assertEquals($data['resource_id'], $comment['resource_id']);
        $this->assertEquals($data['content'], $comment['content']);
    }

    public function testCreateCommentWithInvalidData(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Failed to create comment');

        $data = [
            'user_id' => 1,
            // Missing required resource_id field
            'content' => 'Test comment content'
        ];

        $this->commentModel->create($data);
    }

    public function testGetCommentsByResourceId(): void
    {
        $userId = $this->createTestUser();
        $resourceId = $this->createTestResource();

        // Create multiple comments
        $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'First comment'
        ]);

        $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'Second comment'
        ]);

        $comments = $this->commentModel->getByResourceId($resourceId);
        $this->assertIsArray($comments);
        $this->assertCount(2, $comments);
        $this->assertEquals('First comment', $comments[0]['content']);
        $this->assertEquals('Second comment', $comments[1]['content']);
    }

    public function testGetCommentsByNonExistentResourceId(): void
    {
        $comments = $this->commentModel->getByResourceId(99999);
        $this->assertIsArray($comments);
        $this->assertEmpty($comments);
    }

    public function testUpdateComment(): void
    {
        $userId = $this->createTestUser();
        $resourceId = $this->createTestResource();
        $commentId = $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'Original comment'
        ]);

        $newData = [
            'content' => 'Updated comment'
        ];

        $result = $this->commentModel->update($commentId, $newData);
        $this->assertTrue($result);

        $updatedComment = $this->commentModel->find($commentId);
        $this->assertEquals($newData['content'], $updatedComment['content']);
    }

    public function testDeleteComment(): void
    {
        $userId = $this->createTestUser();
        $resourceId = $this->createTestResource();
        $commentId = $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'Comment to delete'
        ]);

        $result = $this->commentModel->delete($commentId);
        $this->assertTrue($result);

        $deletedComment = $this->commentModel->find($commentId);
        $this->assertNull($deletedComment);
    }

    public function testCountCommentsByResourceId(): void
    {
        $userId = $this->createTestUser();
        $resourceId = $this->createTestResource();

        // Create multiple comments
        $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'First comment'
        ]);

        $this->commentModel->create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
            'content' => 'Second comment'
        ]);

        $count = $this->commentModel->countByResourceId($resourceId);
        $this->assertEquals(2, $count);
    }

    public function testCountCommentsByNonExistentResourceId(): void
    {
        $count = $this->commentModel->countByResourceId(99999);
        $this->assertEquals(0, $count);
    }
} 