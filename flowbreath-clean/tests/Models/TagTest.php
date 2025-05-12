<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Tag;
use App\Exceptions\ModelException;
use Tests\TestCase;
use PDOException;

class TagTest extends TestCase
{
    private Tag $tagModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagModel = new Tag($this->pdo);
    }

    public function testCreateTag(): void
    {
        $data = [
            'name' => 'test-tag',
            'description' => 'Test tag description'
        ];

        $tagId = $this->tagModel->create($data);
        $this->assertIsInt($tagId);
        $this->assertGreaterThan(0, $tagId);

        $tag = $this->tagModel->find($tagId);
        $this->assertEquals($data['name'], $tag['name']);
        $this->assertEquals($data['description'], $tag['description']);
    }

    public function testCreateTagWithInvalidData(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Failed to create tag');

        $data = [
            // Missing required name field
            'description' => 'Test tag description'
        ];

        $this->tagModel->create($data);
    }

    public function testFindTagByName(): void
    {
        $tagId = $this->tagModel->create([
            'name' => 'test-tag',
            'description' => 'Test tag description'
        ]);

        $tag = $this->tagModel->findByName('test-tag');
        $this->assertIsArray($tag);
        $this->assertEquals($tagId, $tag['id']);
        $this->assertEquals('test-tag', $tag['name']);
    }

    public function testFindNonExistentTagByName(): void
    {
        $tag = $this->tagModel->findByName('nonexistent-tag');
        $this->assertNull($tag);
    }

    public function testSearchTags(): void
    {
        // Create multiple tags
        $this->tagModel->create([
            'name' => 'php-tag',
            'description' => 'PHP related tag'
        ]);

        $this->tagModel->create([
            'name' => 'javascript-tag',
            'description' => 'JavaScript related tag'
        ]);

        $this->tagModel->create([
            'name' => 'python-tag',
            'description' => 'Python related tag'
        ]);

        $tags = $this->tagModel->search('script');
        $this->assertIsArray($tags);
        $this->assertCount(1, $tags);
        $this->assertEquals('javascript-tag', $tags[0]['name']);
    }

    public function testGetPopularTags(): void
    {
        // Create multiple tags
        $tag1Id = $this->tagModel->create([
            'name' => 'tag1',
            'description' => 'First tag'
        ]);

        $tag2Id = $this->tagModel->create([
            'name' => 'tag2',
            'description' => 'Second tag'
        ]);

        $tag3Id = $this->tagModel->create([
            'name' => 'tag3',
            'description' => 'Third tag'
        ]);

        // Create resources and associate tags
        $resource1Id = $this->createTestResource();
        $resource2Id = $this->createTestResource();

        // Associate tags with resources
        $this->tagModel->associateWithResource($tag1Id, $resource1Id);
        $this->tagModel->associateWithResource($tag1Id, $resource2Id);
        $this->tagModel->associateWithResource($tag2Id, $resource1Id);
        $this->tagModel->associateWithResource($tag3Id, $resource2Id);

        $popularTags = $this->tagModel->getPopularTags(2);
        $this->assertIsArray($popularTags);
        $this->assertCount(2, $popularTags);
        $this->assertEquals('tag1', $popularTags[0]['name']);
        $this->assertEquals(2, $popularTags[0]['count']);
    }

    public function testGetTagsByResourceId(): void
    {
        $tag1Id = $this->tagModel->create([
            'name' => 'tag1',
            'description' => 'First tag'
        ]);

        $tag2Id = $this->tagModel->create([
            'name' => 'tag2',
            'description' => 'Second tag'
        ]);

        $resourceId = $this->createTestResource();

        // Associate tags with resource
        $this->tagModel->associateWithResource($tag1Id, $resourceId);
        $this->tagModel->associateWithResource($tag2Id, $resourceId);

        $tags = $this->tagModel->getByResourceId($resourceId);
        $this->assertIsArray($tags);
        $this->assertCount(2, $tags);
        $this->assertEquals('tag1', $tags[0]['name']);
        $this->assertEquals('tag2', $tags[1]['name']);
    }

    public function testGetTagsByNonExistentResourceId(): void
    {
        $tags = $this->tagModel->getByResourceId(99999);
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    public function testDeleteTag(): void
    {
        $tagId = $this->tagModel->create([
            'name' => 'tag-to-delete',
            'description' => 'Tag to be deleted'
        ]);

        $result = $this->tagModel->delete($tagId);
        $this->assertTrue($result);

        $deletedTag = $this->tagModel->find($tagId);
        $this->assertNull($deletedTag);
    }

    public function testAssociateAndDissociateTagWithResource(): void
    {
        $tagId = $this->tagModel->create([
            'name' => 'test-tag',
            'description' => 'Test tag'
        ]);

        $resourceId = $this->createTestResource();

        // Test association
        $result = $this->tagModel->associateWithResource($tagId, $resourceId);
        $this->assertTrue($result);

        $tags = $this->tagModel->getByResourceId($resourceId);
        $this->assertCount(1, $tags);
        $this->assertEquals($tagId, $tags[0]['id']);

        // Test dissociation
        $result = $this->tagModel->dissociateFromResource($tagId, $resourceId);
        $this->assertTrue($result);

        $tags = $this->tagModel->getByResourceId($resourceId);
        $this->assertEmpty($tags);
    }
} 