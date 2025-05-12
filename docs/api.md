# SQL Resource Management API Documentation

## Overview
This API provides endpoints for managing SQL resources, including uploading, retrieving, and managing SQL files with optimized memory handling and security features.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require authentication using JWT token. Include the token in the Authorization header:
```
Authorization: Bearer <your_jwt_token>
```

## Endpoints

### Upload SQL File
Upload and process a SQL file with memory optimization and security validation.

```http
POST /resources/sql/upload
Content-Type: multipart/form-data
```

#### Request Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| sql_file | File | SQL file to upload (max 10MB) |

#### Response
```json
{
    "message": "SQL file processed successfully",
    "resource_id": "123",
    "stats": {
        "statements": 100,
        "lines": 500,
        "errors": 0,
        "memory_used": "2.5 MB",
        "processing_time": 1.23,
        "success_rate": 100
    }
}
```

#### Error Responses
- `400 Bad Request`: Invalid file or validation error
- `401 Unauthorized`: Missing or invalid authentication
- `413 Payload Too Large`: File size exceeds limit
- `503 Service Unavailable`: Server memory limit reached

### Get SQL Resource
Retrieve a specific SQL resource by name.

```http
GET /resources/sql/{name}
```

#### Response
```json
{
    "success": true,
    "content": "SELECT * FROM users;"
}
```

### List SQL Resources
Get a list of all available SQL resources.

```http
GET /resources/sql
```

#### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number (default: 1) |
| limit | integer | Items per page (default: 10) |

#### Response
```json
{
    "success": true,
    "resources": [
        {
            "id": "123",
            "name": "users.sql",
            "created_at": "2024-05-07 04:48:37",
            "size": "1.2 MB",
            "statements": 100
        }
    ],
    "pagination": {
        "total": 50,
        "per_page": 10,
        "current_page": 1,
        "last_page": 5
    }
}
```

### Delete SQL Resource
Delete a specific SQL resource.

```http
DELETE /resources/sql/{name}
```

#### Response
```json
{
    "success": true,
    "message": "SQL resource deleted successfully"
}
```

### Update SQL Resource
Update an existing SQL resource.

```http
PUT /resources/sql/{name}
Content-Type: application/json
```

#### Request Body
```json
{
    "content": "SELECT * FROM users WHERE id = 1;"
}
```

#### Response
```json
{
    "success": true,
    "message": "SQL resource updated successfully"
}
```

## Error Handling
All endpoints return error responses in the following format:
```json
{
    "error": "Error message description"
}
```

## Rate Limiting
- Maximum 100 requests per minute per IP
- Maximum file size: 10MB
- Maximum concurrent uploads: 5

## Security
- All SQL files are validated for malicious content
- File type and MIME type verification
- Memory usage monitoring and limits
- Transaction-based processing for data integrity

## Performance
- Chunk-based file processing (512KB chunks)
- Memory usage optimization
- Transaction size: 500 statements
- Memory limit: 70% of available memory 