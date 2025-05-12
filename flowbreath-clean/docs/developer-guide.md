# SQL Resource Management System - Developer Guide

## 아키텍처 개요

### 핵심 컴포넌트
1. **FileValidator**
   - 파일 검증 및 보안 검사
   - MIME 타입 검증
   - SQL 구문 검증

2. **SQLFileProcessor**
   - 청크 기반 파일 처리
   - 메모리 최적화
   - 트랜잭션 관리

3. **MemoryManager**
   - 메모리 사용량 모니터링
   - 체크포인트 관리
   - 메모리 제한 설정

4. **ResourceManager**
   - SQL 리소스 저장 및 관리
   - 메타데이터 관리
   - 버전 관리

## 클래스 구조

### FileValidator
```php
class FileValidator {
    private $allowedMimeTypes;
    private $maxFileSize;
    private $maxFileNameLength;

    public function validateSQLFile($file);
    private function validateBasicFile($file);
    private function validateMimeType($file);
    private function validateSQLContent($file);
    private function validateSQLSyntax($content);
}
```

### SQLFileProcessor
```php
class SQLFileProcessor {
    private $chunkSize;
    private $maxMemoryUsage;
    private $transactionSize;

    public function processFile($filePath);
    private function splitStatements(&$buffer);
    private function beginTransaction();
    private function commitTransaction();
    private function rollbackTransaction();
}
```

### MemoryManager
```php
class MemoryManager {
    private $checkpoints;
    private $memoryLimit;

    public function createCheckpoint($name);
    public function getCheckpointDiff($checkpointName);
    public function checkMemoryLimit($threshold);
    public function getMemoryStats();
}
```

## 확장 가이드

### 새로운 파일 타입 추가
1. `FileValidator` 클래스의 `$allowedMimeTypes` 배열에 새로운 MIME 타입 추가
2. 필요한 경우 새로운 검증 메서드 구현
3. `validateSQLContent` 메서드 수정

### 메모리 최적화 설정 조정
```php
$processor = new SQLFileProcessor();
$processor->setChunkSize(1024 * 1024); // 1MB
$processor->setMaxMemoryUsage(0.8); // 80%
$processor->setTransactionSize(1000); // 1000 statements
```

### 트랜잭션 관리 커스터마이징
```php
class CustomSQLProcessor extends SQLFileProcessor {
    protected function beginTransaction() {
        // 커스텀 트랜잭션 시작 로직
    }

    protected function commitTransaction() {
        // 커스텀 커밋 로직
    }
}
```

## 테스트 작성

### 단위 테스트
```php
class FileValidatorTest extends TestCase {
    public function testValidateSQLFile() {
        $validator = new FileValidator();
        $file = [
            'name' => 'test.sql',
            'type' => 'application/sql',
            'tmp_name' => '/tmp/test.sql',
            'error' => 0,
            'size' => 1024
        ];
        
        $this->assertTrue($validator->validateSQLFile($file));
    }
}
```

### 통합 테스트
```php
class SQLFileProcessorTest extends TestCase {
    public function testProcessLargeFile() {
        $processor = new SQLFileProcessor();
        $result = $processor->processFile('large.sql');
        
        $this->assertArrayHasKey('statements', $result);
        $this->assertArrayHasKey('memory_used', $result);
        $this->assertArrayHasKey('processing_time', $result);
    }
}
```

## 성능 최적화

### 메모리 사용량 최적화
1. 청크 크기 조정
2. 트랜잭션 크기 최적화
3. 가비지 컬렉션 주기 설정

### 처리 속도 개선
1. 인덱스 최적화
2. 쿼리 캐싱
3. 병렬 처리 구현

## 보안 고려사항

### 파일 업로드 보안
1. 파일 타입 검증
2. 파일 크기 제한
3. 파일명 검증
4. 임시 파일 관리

### SQL 인젝션 방지
1. prepared statements 사용
2. 입력값 검증
3. 위험한 명령어 감지

### 메모리 보안
1. 메모리 제한 설정
2. 메모리 사용량 모니터링
3. 오버플로우 방지

## 로깅 및 모니터링

### 로그 레벨
- DEBUG: 상세 디버깅 정보
- INFO: 일반적인 처리 정보
- WARNING: 잠재적 문제
- ERROR: 오류 발생
- CRITICAL: 심각한 오류

### 모니터링 지표
- 메모리 사용량
- 처리 시간
- 에러 발생률
- 성공률

## 배포 가이드

### 요구사항
- PHP 8.0+
- MySQL 5.7+
- Composer
- Git

### 배포 단계
1. 코드 배포
2. 의존성 설치
3. 환경 설정
4. 데이터베이스 마이그레이션
5. 캐시 초기화

### 모니터링 설정
1. 로그 설정
2. 알림 설정
3. 성능 모니터링
4. 보안 모니터링 