<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Request;
use App\Core\Auth;

abstract class BaseController {
    protected $response;
    protected $request;
    protected $auth;

    public function __construct(Request $request) {
        $this->response = new Response();
        $this->request = $request;
        $this->auth = new Auth();
    }

    protected function json($data, $statusCode = 200) {
        return $this->response->json($data, $statusCode);
    }

    protected function requireAuth() {
        $user = $this->auth->user();
        if (!$user) {
            return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
        }
        return $user;
    }

    protected function requireAdmin() {
        $user = $this->requireAuth();
        if (!is_array($user)) {
            return $user;
        }
        if (!$user['is_admin']) {
            return $this->response->json(['error' => '관리자 권한이 필요합니다.'], 403);
        }
        return $user;
    }

    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "{$field}는 필수입니다.";
            }
        }
        return $errors;
    }

    protected function validateLength($data, $field, $min, $max) {
        $length = mb_strlen($data[$field]);
        if ($length < $min || $length > $max) {
            return "{$field}는 {$min}자 이상 {$max}자 이하여야 합니다.";
        }
        return null;
    }

    protected function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "유효한 이메일 주소를 입력해주세요.";
        }
        return null;
    }

    protected function validateNumeric($data, $field) {
        if (!is_numeric($data[$field])) {
            return "{$field}는 숫자여야 합니다.";
        }
        return null;
    }

    protected function validateIn($data, $field, $allowed) {
        if (!in_array($data[$field], $allowed)) {
            return "{$field}는 " . implode(', ', $allowed) . " 중 하나여야 합니다.";
        }
        return null;
    }

    protected function validateDate($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return "유효한 날짜를 입력해주세요 (YYYY-MM-DD).";
        }
        return null;
    }

    protected function validateDateTime($datetime) {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        if (!$d || $d->format('Y-m-d H:i:s') !== $datetime) {
            return "유효한 날짜와 시간을 입력해주세요 (YYYY-MM-DD HH:MM:SS).";
        }
        return null;
    }

    protected function validateFile($file, $allowedTypes, $maxSize) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return "잘못된 파일입니다.";
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "파일 크기가 너무 큽니다.";
            case UPLOAD_ERR_PARTIAL:
                return "파일이 완전히 업로드되지 않았습니다.";
            case UPLOAD_ERR_NO_FILE:
                return "파일이 없습니다.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "임시 폴더가 없습니다.";
            case UPLOAD_ERR_CANT_WRITE:
                return "파일을 저장할 수 없습니다.";
            case UPLOAD_ERR_EXTENSION:
                return "PHP 확장에 의해 업로드가 중지되었습니다.";
            default:
                return "알 수 없는 오류가 발생했습니다.";
        }

        if ($file['size'] > $maxSize) {
            return "파일 크기는 " . ($maxSize / 1024 / 1024) . "MB를 초과할 수 없습니다.";
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            return "허용되지 않는 파일 형식입니다.";
        }

        return null;
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    protected function getPaginationParams($defaultLimit = 10) {
        $page = max(1, (int)$this->request->get('page', 1));
        $limit = max(1, min(100, (int)$this->request->get('limit', $defaultLimit)));
        return [$page, $limit];
    }

    protected function getSortParams($defaultField = 'id', $defaultOrder = 'desc') {
        $field = $this->request->get('sort_by', $defaultField);
        $order = strtolower($this->request->get('sort_order', $defaultOrder));
        if (!in_array($order, ['asc', 'desc'])) {
            $order = $defaultOrder;
        }
        return [$field, $order];
    }

    /**
     * 뷰 파일을 렌더링하여 Response 객체로 반환
     *
     * @param string $view 뷰 파일 경로 (View 디렉토리 기준)
     * @param array $data 뷰에 전달할 데이터
     * @param int $status HTTP 상태 코드
     * @return \App\Core\Response
     */
    protected function view($view, $data = [], $statusCode = 200): \App\Core\Response
    {
        return $this->response->view($view, $data, $statusCode);
    }
} 