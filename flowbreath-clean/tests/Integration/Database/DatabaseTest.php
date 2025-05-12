<?php

namespace Tests\Integration\Database;

use Tests\Integration\TestCase;
use App\Core\Database;
use PDO;

class DatabaseTest extends TestCase
{
    public function testConnectionPool()
    {
        $poolSize = $this->db->getConnectionPoolSize();
        $this->assertIsArray($poolSize);
        $this->assertArrayHasKey('current', $poolSize);
        $this->assertArrayHasKey('max', $poolSize);
        $this->assertArrayHasKey('available', $poolSize);
        
        $this->assertLessThanOrEqual($poolSize['max'], $poolSize['current']);
        $this->assertLessThanOrEqual($poolSize['current'], $poolSize['available']);
    }

    public function testQueryBuilder()
    {
        // 테스트 데이터 생성
        $userId = $this->createTestUser();
        
        // QueryBuilder를 사용한 쿼리 실행
        $user = $this->db->getQueryBuilder()
            ->table('users')
            ->select(['id', 'username', 'email'])
            ->where('id', $userId)
            ->first();
            
        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertEquals('testuser', $user['username']);
        $this->assertEquals('test@example.com', $user['email']);
    }

    public function testQueryCache()
    {
        // 캐시된 쿼리 실행
        $query = $this->db->getQueryBuilder()
            ->table('users')
            ->select(['COUNT(*) as count'])
            ->cache(60);
            
        $result1 = $query->get();
        $result2 = $query->get();
        
        $this->assertEquals($result1, $result2);
    }

    public function testTransaction()
    {
        $this->db->beginTransaction();
        
        try {
            // 첫 번째 사용자 생성
            $userId1 = $this->createTestUser([
                'email' => 'user1@example.com'
            ]);
            
            // 두 번째 사용자 생성
            $userId2 = $this->createTestUser([
                'email' => 'user2@example.com'
            ]);
            
            $this->db->commit();
            
            // 트랜잭션이 성공적으로 완료되었는지 확인
            $users = $this->db->query(
                "SELECT * FROM users WHERE id IN (?, ?)",
                [$userId1, $userId2]
            )->fetchAll();
            
            $this->assertCount(2, $users);
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function testTransactionRollback()
    {
        $this->db->beginTransaction();
        
        try {
            // 첫 번째 사용자 생성
            $userId1 = $this->createTestUser([
                'email' => 'user1@example.com'
            ]);
            
            // 중복 이메일로 두 번째 사용자 생성 시도
            $this->createTestUser([
                'email' => 'user1@example.com'
            ]);
            
            $this->db->commit();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->db->rollback();
            
            // 롤백이 제대로 되었는지 확인
            $user = $this->db->query(
                "SELECT * FROM users WHERE email = ?",
                ['user1@example.com']
            )->fetch();
            
            $this->assertFalse($user);
        }
    }

    public function testPreparedStatements()
    {
        // SQL 인젝션 시도
        $maliciousInput = "'; DROP TABLE users; --";
        
        $this->db->query(
            "SELECT * FROM users WHERE username = ?",
            [$maliciousInput]
        );
        
        // 테이블이 여전히 존재하는지 확인
        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $this->assertContains('users', $tables);
    }

    public function testConnectionTimeout()
    {
        // 연결 타임아웃 설정
        $this->db->setConnectionTimeout(1);
        
        // 오래 걸리는 쿼리 실행
        $startTime = microtime(true);
        
        try {
            $this->db->query("SELECT SLEEP(2)");
            $this->fail('Expected timeout exception was not thrown');
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            $this->assertLessThan(2, $executionTime);
        }
    }
} 