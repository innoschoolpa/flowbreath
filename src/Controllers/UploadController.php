<?php
namespace App\Controllers;

use App\Core\Response;

class UploadController {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct() {
        $this->uploadDir = dirname(__DIR__, 2) . '/public/uploads/images/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB

        // 업로드 디렉토리가 없으면 생성
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                error_log("Failed to create upload directory: " . $this->uploadDir);
                throw new \Exception('Failed to create upload directory');
            }
        }

        // 디렉토리 권한 확인
        if (!is_writable($this->uploadDir)) {
            error_log("Upload directory is not writable: " . $this->uploadDir);
            throw new \Exception('Upload directory is not writable');
        }
    }

    public function uploadImage() {
        try {
            // CSRF 토큰 검증
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new \Exception('Invalid CSRF token');
            }

            // 파일이 업로드되었는지 확인
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file uploaded';
                error_log("File upload error: " . $error);
                throw new \Exception('No file uploaded or upload error');
            }

            $file = $_FILES['image'];
            error_log("Uploading file: " . $file['name'] . " (Type: " . $file['type'] . ", Size: " . $file['size'] . ")");

            // 파일 타입 검증
            if (!in_array($file['type'], $this->allowedTypes)) {
                error_log("Invalid file type: " . $file['type']);
                throw new \Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
            }

            // 파일 크기 검증
            if ($file['size'] > $this->maxFileSize) {
                error_log("File size exceeds limit: " . $file['size']);
                throw new \Exception('File size exceeds the limit of 5MB.');
            }

            // 고유한 파일명 생성
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                error_log("Failed to move uploaded file to: " . $filepath);
                throw new \Exception('Failed to save the uploaded file.');
            }

            // 파일 권한 설정
            chmod($filepath, 0644);

            // 상대 URL 반환
            $url = '/uploads/images/' . $filename;
            error_log("File successfully uploaded to: " . $url);
            return new Response([
                'success' => true,
                'url' => $url
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            return new Response([
                'success' => false,
                'error' => $e->getMessage()
            ], 400, ['Content-Type' => 'application/json']);
        }
    }
} 