<?php
namespace App\Controllers;

class UploadController {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct() {
        $this->uploadDir = __DIR__ . '/../../public/uploads/images/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB

        // 업로드 디렉토리가 없으면 생성
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function uploadImage() {
        // CSRF 토큰 검증
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        // 파일이 업로드되었는지 확인
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
            return;
        }

        $file = $_FILES['image'];

        // 파일 타입 검증
        if (!in_array($file['type'], $this->allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
            return;
        }

        // 파일 크기 검증
        if ($file['size'] > $this->maxFileSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File size exceeds the limit of 5MB.']);
            return;
        }

        // 고유한 파일명 생성
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // 파일 이동
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // 상대 URL 반환
            $url = '/uploads/images/' . $filename;
            echo json_encode(['success' => true, 'url' => $url]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save the uploaded file.']);
        }
    }
} 