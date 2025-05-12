# SQL Resource Management System - API 가이드

## API 개요

### 기본 정보
- 기본 URL: `https://api.example.com/v1`
- 인증 방식: Bearer Token
- 응답 형식: JSON
- 문자 인코딩: UTF-8

### 인증
```http
Authorization: Bearer <access_token>
```

### 응답 형식
```json
{
    "success": true,
    "data": {
        // 응답 데이터
    },
    "error": null,
    "message": "성공적으로 처리되었습니다."
}
```

## 엔드포인트

### SQL 파일 업로드
```http
POST /resources/sql
Content-Type: multipart/form-data
```

#### 요청 파라미터
| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| file | File | Yes | SQL 파일 |
| name | String | No | 리소스 이름 |
| description | String | No | 리소스 설명 |
| tags | Array | No | 태그 목록 |

#### 응답
```json
{
    "success": true,
    "data": {
        "resource_id": "123e4567-e89b-12d3-a456-426614174000",
        "name": "example.sql",
        "size": 1024,
        "statements": 100,
        "processing_time": 1.5,
        "memory_used": "50MB"
    }
}
```

### 리소스 목록 조회
```http
GET /resources
```

#### 쿼리 파라미터
| 파라미터 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| page | Integer | No | 페이지 번호 |
| limit | Integer | No | 페이지당 항목 수 |
| sort | String | No | 정렬 기준 |
| order | String | No | 정렬 순서 (asc/desc) |
| search | String | No | 검색어 |
| tags | Array | No | 태그 필터 |

#### 응답
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "resource_id": "123e4567-e89b-12d3-a456-426614174000",
                "name": "example.sql",
                "size": 1024,
                "created_at": "2024-03-20T10:00:00Z",
                "status": "completed"
            }
        ],
        "total": 100,
        "page": 1,
        "limit": 10
    }
}
```

### 리소스 상세 조회
```http
GET /resources/{resource_id}
```

#### 응답
```json
{
    "success": true,
    "data": {
        "resource_id": "123e4567-e89b-12d3-a456-426614174000",
        "name": "example.sql",
        "description": "예제 SQL 파일",
        "size": 1024,
        "created_at": "2024-03-20T10:00:00Z",
        "updated_at": "2024-03-20T10:00:00Z",
        "status": "completed",
        "statements": 100,
        "processing_time": 1.5,
        "memory_used": "50MB",
        "tags": ["example", "test"]
    }
}
```

### 리소스 실행
```http
POST /resources/{resource_id}/execute
```

#### 요청 본문
```json
{
    "options": {
        "transaction_size": 1000,
        "chunk_size": 1048576,
        "max_memory_usage": 0.8
    }
}
```

#### 응답
```json
{
    "success": true,
    "data": {
        "execution_id": "123e4567-e89b-12d3-a456-426614174001",
        "status": "running",
        "started_at": "2024-03-20T10:00:00Z"
    }
}
```

### 실행 상태 조회
```http
GET /executions/{execution_id}
```

#### 응답
```json
{
    "success": true,
    "data": {
        "execution_id": "123e4567-e89b-12d3-a456-426614174001",
        "status": "completed",
        "started_at": "2024-03-20T10:00:00Z",
        "completed_at": "2024-03-20T10:01:00Z",
        "statements_executed": 100,
        "statements_failed": 0,
        "processing_time": 60,
        "memory_used": "50MB"
    }
}
```

### 리소스 삭제
```http
DELETE /resources/{resource_id}
```

#### 응답
```json
{
    "success": true,
    "data": null,
    "message": "리소스가 삭제되었습니다."
}
```

## 오류 코드

### HTTP 상태 코드
| 코드 | 설명 |
|------|------|
| 200 | 성공 |
| 201 | 생성됨 |
| 400 | 잘못된 요청 |
| 401 | 인증 실패 |
| 403 | 권한 없음 |
| 404 | 리소스 없음 |
| 413 | 파일 크기 초과 |
| 415 | 지원하지 않는 파일 형식 |
| 500 | 서버 오류 |

### 오류 응답
```json
{
    "success": false,
    "data": null,
    "error": {
        "code": "INVALID_FILE_TYPE",
        "message": "지원하지 않는 파일 형식입니다.",
        "details": {
            "allowed_types": ["application/sql", "text/plain"]
        }
    }
}
```

## 웹소켓 API

### 연결
```javascript
const ws = new WebSocket('wss://api.example.com/v1/ws');
```

### 이벤트
| 이벤트 | 설명 |
|--------|------|
| execution.started | 실행 시작 |
| execution.progress | 실행 진행 상황 |
| execution.completed | 실행 완료 |
| execution.failed | 실행 실패 |

### 메시지 형식
```json
{
    "event": "execution.progress",
    "data": {
        "execution_id": "123e4567-e89b-12d3-a456-426614174001",
        "progress": 50,
        "statements_executed": 50,
        "memory_used": "25MB"
    }
}
```

## 레이트 제한

### 제한
- 인증된 요청: 1000회/시간
- 인증되지 않은 요청: 100회/시간
- 파일 업로드: 10회/시간

### 헤더
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1616236800
```

## 버전 관리

### 버전 확인
```http
GET /version
```

#### 응답
```json
{
    "success": true,
    "data": {
        "version": "1.0.0",
        "api_version": "v1",
        "min_version": "1.0.0",
        "deprecated": false
    }
}
```

### 버전 지정
- URL에 버전 포함: `/v1/resources`
- 헤더로 지정: `X-API-Version: 1.0.0`

## 보안

### 인증
- Bearer Token 인증
- 토큰 갱신: `/auth/refresh`
- 토큰 만료: 1시간

### 권한
| 권한 | 설명 |
|------|------|
| resources.read | 리소스 조회 |
| resources.write | 리소스 생성/수정 |
| resources.delete | 리소스 삭제 |
| resources.execute | 리소스 실행 |

### CORS
```
Access-Control-Allow-Origin: https://example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Authorization, Content-Type
```

## 모니터링

### 상태 확인
```http
GET /health
```

#### 응답
```json
{
    "success": true,
    "data": {
        "status": "healthy",
        "version": "1.0.0",
        "uptime": 3600,
        "memory_usage": "50MB",
        "database": "connected"
    }
}
```

### 메트릭
```http
GET /metrics
```

#### 응답
```json
{
    "success": true,
    "data": {
        "requests": {
            "total": 1000,
            "success": 950,
            "failed": 50
        },
        "resources": {
            "total": 100,
            "active": 80,
            "deleted": 20
        },
        "executions": {
            "total": 500,
            "running": 10,
            "completed": 480,
            "failed": 10
        }
    }
}
``` 