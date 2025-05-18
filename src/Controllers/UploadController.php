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
        try {
            // JSON 응답 헤더 설정
            header('Content-Type: application/json');

            // CSRF 토큰 검증
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new \Exception('Invalid CSRF token');
            }

            // 파일이 업로드되었는지 확인
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('No file uploaded or upload error');
            }

            $file = $_FILES['image'];

            // 파일 타입 검증
            if (!in_array($file['type'], $this->allowedTypes)) {
                throw new \Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
            }

            // 파일 크기 검증
            if ($file['size'] > $this->maxFileSize) {
                throw new \Exception('File size exceeds the limit of 5MB.');
            }

            // 고유한 파일명 생성
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // 파일 이동
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to save the uploaded file.');
            }

            // 상대 URL 반환
            $url = '/uploads/images/' . $filename;
            $response = ['success' => true, 'url' => $url];
            echo json_encode($response);
            return $response;

        } catch (\Exception $e) {
            http_response_code(400);
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            echo json_encode($response);
            return $response;
        }
    }
} 