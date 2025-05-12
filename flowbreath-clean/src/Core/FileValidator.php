<?php

namespace App\Core;

class FileValidator
{
    private $logger;
    private $allowedMimeTypes = [
        'text/plain' => ['.sql', '.txt'],
        'application/sql' => ['.sql'],
        'application/x-sql' => ['.sql']
    ];
    private $maxFileSize = 10485760; // 10MB
    private $maxFileNameLength = 255;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    public function validateSQLFile($file)
    {
        try {
            // 기본 파일 검증
            $this->validateBasicFile($file);

            // MIME 타입 검증
            $this->validateMimeType($file);

            // 파일 내용 검증
            $this->validateSQLContent($file);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("File validation failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateBasicFile($file)
    {
        // 파일 존재 여부 확인
        if (!isset($file) || !is_array($file)) {
            throw new \Exception("Invalid file data");
        }

        // 업로드 에러 확인
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file['error']);
            throw new \Exception("File upload error: " . $errorMessage);
        }

        // 파일 크기 검증
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception(sprintf(
                "File size exceeds limit: %s (max: %s)",
                $this->formatBytes($file['size']),
                $this->formatBytes($this->maxFileSize)
            ));
        }

        // 파일명 검증
        $fileName = basename($file['name']);
        if (strlen($fileName) > $this->maxFileNameLength) {
            throw new \Exception("File name is too long");
        }

        // 파일명에 위험한 문자 포함 여부 확인
        if (preg_match('/[<>:"\/\\|?*]/', $fileName)) {
            throw new \Exception("File name contains invalid characters");
        }
    }

    private function validateMimeType($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!isset($this->allowedMimeTypes[$mimeType])) {
            throw new \Exception("Invalid file type: " . $mimeType);
        }

        if (!in_array('.' . $extension, $this->allowedMimeTypes[$mimeType])) {
            throw new \Exception("File extension does not match MIME type");
        }
    }

    private function validateSQLContent($file)
    {
        $content = file_get_contents($file['tmp_name']);
        
        // 파일이 비어있는지 확인
        if (empty(trim($content))) {
            throw new \Exception("SQL file is empty");
        }

        // 위험한 SQL 명령어 검사
        $dangerousCommands = [
            'DROP DATABASE',
            'DROP TABLE',
            'TRUNCATE TABLE',
            'DELETE FROM',
            'UPDATE',
            'ALTER TABLE'
        ];

        foreach ($dangerousCommands as $command) {
            if (stripos($content, $command) !== false) {
                $this->logger->warning("Potentially dangerous SQL command found: " . $command);
            }
        }

        // SQL 구문 검증
        $this->validateSQLSyntax($content);
    }

    private function validateSQLSyntax($content)
    {
        // 기본적인 SQL 구문 검증
        $statements = explode(';', $content);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }

            // SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER 등 기본 명령어 확인
            if (!preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|TRUNCATE)\s+/i', $statement)) {
                $this->logger->warning("Unrecognized SQL statement: " . substr($statement, 0, 100));
            }
        }
    }

    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload";
            default:
                return "Unknown upload error";
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function setMaxFileSize($size)
    {
        if ($size > 0) {
            $this->maxFileSize = $size;
        }
    }

    public function setAllowedMimeTypes($types)
    {
        $this->allowedMimeTypes = $types;
    }
} 