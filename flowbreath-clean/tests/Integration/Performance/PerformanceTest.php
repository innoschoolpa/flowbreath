<?php

namespace Tests\Integration\Performance;

use Tests\Integration\TestCase;
use App\Core\Database;
use App\Core\Session;
use App\Core\MemoryManager;

class PerformanceTest extends TestCase
{
    private $memoryManager;
    private $iterations = 1000;
    private $thresholds = [
        'query_time' => 0.1,      // 100ms
        'session_time' => 0.05,   // 50ms
        'memory_usage' => 5 * 1024 * 1024  // 5MB
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->memoryManager = new MemoryManager();
    }

    public function testDatabaseQueryPerformance()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 대량의 데이터 생성
        for ($i = 0; $i < $this->iterations; $i++) {
            $this->createTestUser([
                'email' => "test{$i}@example.com",
                'username' => "testuser{$i}"
            ]);
        }

        // SELECT 쿼리 성능 테스트
        $queryStart = microtime(true);
        $users = $this->db->query(
            "SELECT * FROM users LIMIT ?",
            [$this->iterations]
        )->fetchAll();
        $queryTime = microtime(true) - $queryStart;

        $totalTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        $this->assertLessThan(
            $this->thresholds['query_time'],
            $queryTime,
            "Query execution time exceeded threshold: {$queryTime}s"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $memoryUsed,
            "Memory usage exceeded threshold: " . ($memoryUsed / 1024 / 1024) . "MB"
        );
    }

    public function testSessionPerformance()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 세션 데이터 쓰기 성능 테스트
        for ($i = 0; $i < $this->iterations; $i++) {
            $this->session->set("key{$i}", "value{$i}");
        }

        // 세션 데이터 읽기 성능 테스트
        $readStart = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            $value = $this->session->get("key{$i}");
        }
        $readTime = microtime(true) - $readStart;

        $totalTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        $this->assertLessThan(
            $this->thresholds['session_time'],
            $readTime,
            "Session read time exceeded threshold: {$readTime}s"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $memoryUsed,
            "Memory usage exceeded threshold: " . ($memoryUsed / 1024 / 1024) . "MB"
        );
    }

    public function testConnectionPoolPerformance()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 동시 연결 테스트
        $connections = [];
        for ($i = 0; $i < 10; $i++) {
            $connections[] = Database::getInstance();
        }

        // 연결 풀 상태 확인
        $poolSize = $this->db->getConnectionPoolSize();
        
        $totalTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        $this->assertLessThanOrEqual(
            5, // max_pool_size
            $poolSize['current'],
            "Connection pool size exceeded maximum: {$poolSize['current']}"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $memoryUsed,
            "Memory usage exceeded threshold: " . ($memoryUsed / 1024 / 1024) . "MB"
        );
    }

    public function testQueryCachePerformance()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 캐시된 쿼리 실행
        $query = $this->db->getQueryBuilder()
            ->table('users')
            ->select(['COUNT(*) as count'])
            ->cache(60);

        // 첫 번째 실행 (캐시 미스)
        $firstStart = microtime(true);
        $result1 = $query->get();
        $firstTime = microtime(true) - $firstStart;

        // 두 번째 실행 (캐시 히트)
        $secondStart = microtime(true);
        $result2 = $query->get();
        $secondTime = microtime(true) - $secondStart;

        $totalTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        $this->assertLessThan(
            $firstTime,
            $secondTime,
            "Cache hit should be faster than cache miss"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $memoryUsed,
            "Memory usage exceeded threshold: " . ($memoryUsed / 1024 / 1024) . "MB"
        );
    }

    public function testMemoryManagement()
    {
        $startMemory = memory_get_usage();
        
        // 메모리 사용량 증가
        $largeArray = [];
        for ($i = 0; $i < 10000; $i++) {
            $largeArray[] = str_repeat('x', 1000);
        }

        $peakMemory = memory_get_peak_usage();
        
        // 가비지 컬렉션 실행
        $this->memoryManager->runGarbageCollection();
        
        $endMemory = memory_get_usage();
        $memoryReduction = $peakMemory - $endMemory;

        $this->assertGreaterThan(
            0,
            $memoryReduction,
            "Garbage collection should reduce memory usage"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $endMemory - $startMemory,
            "Final memory usage exceeded threshold: " . (($endMemory - $startMemory) / 1024 / 1024) . "MB"
        );
    }

    public function testConcurrentRequests()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 동시 요청 시뮬레이션
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            // 사용자 생성
            $userId = $this->createTestUser([
                'email' => "concurrent{$i}@example.com"
            ]);

            // 세션 설정
            $this->session->set("user{$i}", $userId);

            // 데이터베이스 쿼리
            $user = $this->db->query(
                "SELECT * FROM users WHERE id = ?",
                [$userId]
            )->fetch();

            $results[] = $user;
        }

        $totalTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        $this->assertLessThan(
            2.0, // 2초
            $totalTime,
            "Concurrent operations took too long: {$totalTime}s"
        );

        $this->assertLessThan(
            $this->thresholds['memory_usage'],
            $memoryUsed,
            "Memory usage exceeded threshold: " . ($memoryUsed / 1024 / 1024) . "MB"
        );
    }
} 