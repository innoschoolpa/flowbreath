<?php

namespace App\Controllers;

class ImageUploadController {
    public function upload() {
        if (!isset($_FILES['upload'])) {
            return $this->jsonResponse(['error' => ['message' => 'No file uploaded']], 400);
        }

        $file = $_FILES['upload'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return $this->jsonResponse(['error' => ['message' => 'Invalid file type']], 400);
        }

        $uploadDir = 'public/uploads/images/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $url = '/uploads/images/' . $fileName;
            return $this->jsonResponse([
                'url' => $url,
                'uploaded' => 1,
                'fileName' => $fileName
            ]);
        }

        return $this->jsonResponse(['error' => ['message' => 'Failed to upload file']], 500);
    }

    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 