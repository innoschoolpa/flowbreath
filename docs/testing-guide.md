# SQL Resource Management System - 테스트 가이드

## 테스트 환경 설정

### 요구사항
- PHP 8.0+
- Composer
- PHPUnit 9.5+
- MySQL 5.7+
- Redis (선택사항)

### 설치
```bash
composer require --dev phpunit/phpunit
composer require --dev mockery/mockery
composer require --dev fakerphp/faker
```

### 환경 설정
```php
// phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="flowbreath_test"/>
    </php>
</phpunit>
```

## 단위 테스트

### FileValidator 테스트
```php
class FileValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FileValidator();
    }

    public function testValidateSQLFile()
    {
        $file = [
            'name' => 'test.sql',
            'type' => 'application/sql',
            'tmp_name' => '/tmp/test.sql',
            'error' => 0,
            'size' => 1024
        ];

        $this->assertTrue($this->validator->validateSQLFile($file));
    }

    public function testValidateSQLFileWithInvalidType()
    {
        $file = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test.txt',
            'error' => 0,
            'size' => 1024
        ];

        $this->expectException(InvalidFileTypeException::class);
        $this->validator->validateSQLFile($file);
    }
}
```

### SQLFileProcessor 테스트
```php
class SQLFileProcessorTest extends TestCase
{
    private $processor;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Mockery::mock(Database::class);
        $this->processor = new SQLFileProcessor($this->db);
    }

    public function testProcessFile()
    {
        $filePath = 'tests/fixtures/test.sql';
        $this->db->shouldReceive('beginTransaction')->once();
        $this->db->shouldReceive('commit')->once();
        $this->db->shouldReceive('execute')->times(3);

        $result = $this->processor->processFile($filePath);

        $this->assertEquals(3, $result['statements']);
        $this->assertArrayHasKey('memory_used', $result);
        $this->assertArrayHasKey('processing_time', $result);
    }

    public function testProcessFileWithError()
    {
        $filePath = 'tests/fixtures/invalid.sql';
        $this->db->shouldReceive('beginTransaction')->once();
        $this->db->shouldReceive('rollback')->once();
        $this->db->shouldReceive('execute')
            ->andThrow(new SQLException('Invalid SQL'));

        $result = $this->processor->processFile($filePath);

        $this->assertEquals(1, $result['errors']);
        $this->assertArrayHasKey('error_details', $result);
    }
}
```

### MemoryManager 테스트
```php
class MemoryManagerTest extends TestCase
{
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new MemoryManager();
    }

    public function testCreateCheckpoint()
    {
        $this->manager->createCheckpoint('test');
        $this->assertTrue($this->manager->hasCheckpoint('test'));
    }

    public function testGetCheckpointDiff()
    {
        $this->manager->createCheckpoint('start');
        // 메모리 사용량 증가
        $diff = $this->manager->getCheckpointDiff('start');
        $this->assertGreaterThan(0, $diff['used']);
    }

    public function testCheckMemoryLimit()
    {
        $this->assertTrue($this->manager->checkMemoryLimit(0.8));
        $this->assertFalse($this->manager->checkMemoryLimit(0.1));
    }
}
```

## 통합 테스트

### 리소스 업로드 테스트
```php
class ResourceUploadTest extends TestCase
{
    use RefreshDatabase;

    public function testUploadSQLFile()
    {
        $file = UploadedFile::fake()->create('test.sql', 1024);
        
        $response = $this->postJson('/api/resources/sql', [
            'file' => $file,
            'name' => 'Test SQL',
            'description' => 'Test description'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'resource_id',
                        'name',
                        'size',
                        'statements',
                        'processing_time',
                        'memory_used'
                    ]
                ]);

        $this->assertDatabaseHas('resources', [
            'name' => 'Test SQL',
            'size' => 1024
        ]);
    }
}
```

### 리소스 실행 테스트
```php
class ResourceExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteResource()
    {
        $resource = Resource::factory()->create();

        $response = $this->postJson("/api/resources/{$resource->id}/execute", [
            'options' => [
                'transaction_size' => 1000,
                'chunk_size' => 1048576,
                'max_memory_usage' => 0.8
            ]
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'execution_id',
                        'status',
                        'started_at'
                    ]
                ]);

        $this->assertDatabaseHas('executions', [
            'resource_id' => $resource->id,
            'status' => 'running'
        ]);
    }
}
```

## 기능 테스트

### 웹소켓 테스트
```php
class WebSocketTest extends TestCase
{
    public function testExecutionProgress()
    {
        $ws = new WebSocket('ws://localhost:8080');
        
        $ws->on('execution.progress', function($data) {
            $this->assertArrayHasKey('progress', $data);
            $this->assertArrayHasKey('statements_executed', $data);
            $this->assertArrayHasKey('memory_used', $data);
        });

        $ws->connect();
        $ws->emit('start_execution', ['resource_id' => '123']);
    }
}
```

### 성능 테스트
```php
class PerformanceTest extends TestCase
{
    public function testLargeFileProcessing()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $file = UploadedFile::fake()->create('large.sql', 10 * 1024 * 1024);
        
        $response = $this->postJson('/api/resources/sql', [
            'file' => $file
        ]);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $this->assertLessThan(30, $endTime - $startTime); // 30초 이내
        $this->assertLessThan(100 * 1024 * 1024, $endMemory - $startMemory); // 100MB 이내
    }
}
```

## 테스트 데이터

### Fixtures
```php
// tests/fixtures/test.sql
CREATE TABLE test (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

INSERT INTO test VALUES (1, 'Test 1');
INSERT INTO test VALUES (2, 'Test 2');
```

### Factories
```php
class ResourceFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word . '.sql',
            'size' => $this->faker->numberBetween(1024, 1048576),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed']),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
```

## 테스트 실행

### 전체 테스트
```bash
./vendor/bin/phpunit
```

### 특정 테스트
```bash
./vendor/bin/phpunit tests/Unit/FileValidatorTest.php
./vendor/bin/phpunit --filter testValidateSQLFile
```

### 커버리지 리포트
```bash
./vendor/bin/phpunit --coverage-html coverage
```

## CI/CD 통합

### GitHub Actions
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_DATABASE: flowbreath_test
          MYSQL_ROOT_PASSWORD: secret
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        extensions: mbstring, dom, fileinfo, mysql
        coverage: xdebug
        
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
      
    - name: Execute tests
      run: vendor/bin/phpunit --coverage-clover coverage.xml
      
    - name: Upload coverage
      uses: codecov/codecov-action@v1
      with:
        file: ./coverage.xml
```

## 테스트 모범 사례

### 테스트 구조
1. 준비 (Arrange)
   - 테스트 데이터 설정
   - 의존성 모킹
   - 환경 설정

2. 실행 (Act)
   - 테스트 대상 메서드 호출
   - 이벤트 발생
   - API 요청

3. 검증 (Assert)
   - 결과 확인
   - 상태 검증
   - 예외 처리

### 테스트 격리
1. 데이터베이스 트랜잭션
2. 테스트 데이터 정리
3. 의존성 격리

### 테스트 명명
1. 메서드명: test[테스트대상]_[시나리오]_[예상결과]
2. 클래스명: [테스트대상]Test
3. 파일명: [테스트대상]Test.php

### 테스트 커버리지
1. 코드 커버리지 목표: 80% 이상
2. 중요 경로 100% 커버리지
3. 예외 처리 테스트 포함 