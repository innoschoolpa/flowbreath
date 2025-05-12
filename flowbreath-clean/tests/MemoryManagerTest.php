<?php

namespace Tests;

use App\Core\MemoryManager;

class MemoryManagerTest
{
    private $memoryManager;
    private $passed = 0;
    private $failed = 0;

    public function __construct()
    {
        $this->memoryManager = MemoryManager::getInstance();
    }

    private function assert($condition, $message)
    {
        if ($condition) {
            $this->passed++;
            echo "✓ " . $message . "\n";
        } else {
            $this->failed++;
            echo "✗ " . $message . "\n";
        }
    }

    private function assertIsArray($value, $message)
    {
        $this->assert(is_array($value), $message);
    }

    private function assertArrayHasKey($key, $array, $message)
    {
        $this->assert(array_key_exists($key, $array), $message);
    }

    private function assertIsString($value, $message)
    {
        $this->assert(is_string($value), $message);
    }

    private function assertIsFloat($value, $message)
    {
        $this->assert(is_float($value), $message);
    }

    private function assertIsInt($value, $message)
    {
        $this->assert(is_int($value), $message);
    }

    private function assertIsBool($value, $message)
    {
        $this->assert(is_bool($value), $message);
    }

    private function assertNull($value, $message)
    {
        $this->assert($value === null, $message);
    }

    private function assertGreaterThan($expected, $actual, $message)
    {
        $this->assert($actual > $expected, $message);
    }

    private function assertLessThanOrEqual($expected, $actual, $message)
    {
        $this->assert($actual <= $expected, $message);
    }

    private function assertGreaterThanOrEqual($expected, $actual, $message)
    {
        $this->assert($actual >= $expected, $message);
    }

    public function testMemoryStats()
    {
        $stats = $this->memoryManager->getMemoryStats();
        
        $this->assertIsArray($stats, "Memory stats should be an array");
        $this->assertArrayHasKey('current', $stats, "Memory stats should have 'current' key");
        $this->assertArrayHasKey('peak', $stats, "Memory stats should have 'peak' key");
        $this->assertArrayHasKey('limit', $stats, "Memory stats should have 'limit' key");
        $this->assertArrayHasKey('usage_percent', $stats, "Memory stats should have 'usage_percent' key");
        
        $this->assertIsString($stats['current'], "Current memory should be a string");
        $this->assertIsString($stats['peak'], "Peak memory should be a string");
        $this->assertIsString($stats['limit'], "Memory limit should be a string");
        $this->assertIsFloat($stats['usage_percent'], "Usage percent should be a float");
        
        $this->assertLessThanOrEqual(100, $stats['usage_percent'], "Usage percent should be <= 100");
    }

    public function testCheckpointCreation()
    {
        $this->memoryManager->createCheckpoint('test_checkpoint');
        $diff = $this->memoryManager->getCheckpointDiff('test_checkpoint');
        
        $this->assertIsArray($diff, "Checkpoint diff should be an array");
        $this->assertArrayHasKey('memory_diff', $diff, "Diff should have 'memory_diff' key");
        $this->assertArrayHasKey('peak_diff', $diff, "Diff should have 'peak_diff' key");
        $this->assertArrayHasKey('time_diff', $diff, "Diff should have 'time_diff' key");
        
        $this->assertIsInt($diff['memory_diff'], "Memory diff should be an integer");
        $this->assertIsInt($diff['peak_diff'], "Peak diff should be an integer");
        $this->assertIsFloat($diff['time_diff'], "Time diff should be a float");
    }

    public function testMemoryLimitCheck()
    {
        $result = $this->memoryManager->checkMemoryLimit(0.8);
        $this->assertIsBool($result, "Memory limit check should return a boolean");
    }

    public function testMemoryUsageWithLargeArray()
    {
        $this->memoryManager->createCheckpoint('before_array');
        
        // 대용량 배열 생성으로 메모리 사용량 증가
        $largeArray = [];
        for ($i = 0; $i < 100000; $i++) {
            $largeArray[] = str_repeat('x', 1000);
        }
        
        $this->memoryManager->createCheckpoint('after_array');
        $diff = $this->memoryManager->getCheckpointDiff('before_array');
        
        $this->assertGreaterThan(0, $diff['memory_diff'], "Memory usage should increase after array creation");
        
        // 배열 해제
        $largeArray = null;
        $this->memoryManager->forceGarbageCollection();
    }

    public function testInvalidCheckpoint()
    {
        $diff = $this->memoryManager->getCheckpointDiff('non_existent_checkpoint');
        $this->assertNull($diff, "Non-existent checkpoint should return null");
    }

    public function testCheckpointClearing()
    {
        $this->memoryManager->createCheckpoint('test_clear');
        $this->memoryManager->clearCheckpoints();
        
        $diff = $this->memoryManager->getCheckpointDiff('test_clear');
        $this->assertNull($diff, "Cleared checkpoint should return null");
    }

    public function testMemoryUsagePercent()
    {
        $stats = $this->memoryManager->getMemoryStats();
        $this->assertGreaterThanOrEqual(0, $stats['usage_percent'], "Usage percent should be >= 0");
        $this->assertLessThanOrEqual(100, $stats['usage_percent'], "Usage percent should be <= 100");
    }

    public function runAllTests()
    {
        echo "Running MemoryManager tests...\n\n";
        
        $this->testMemoryStats();
        $this->testCheckpointCreation();
        $this->testMemoryLimitCheck();
        $this->testMemoryUsageWithLargeArray();
        $this->testInvalidCheckpoint();
        $this->testCheckpointClearing();
        $this->testMemoryUsagePercent();
        
        echo "\nTest Summary:\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n";
    }
}

// 테스트 실행
$test = new MemoryManagerTest();
$test->runAllTests(); 