<?php

namespace App\Controller;

use App\Models\Resource;

class ApiController
{
    private $resourceModel;
    
    public function __construct() {
        $this->resourceModel = new \App\Models\Resource();
    }

    public function getResourcesByTag($tag) {
        try {
            if (empty($tag)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => '태그를 입력해주세요.',
                    'data' => []
                ], 400);
            }

            $resources = $this->resourceModel->getResourcesByTag($tag);
            
            if ($resources === false) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => '리소스를 검색하는 중 오류가 발생했습니다.',
                    'data' => []
                ], 500);
            }

            if (empty($resources)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => '검색 결과가 없습니다.',
                    'data' => []
                ]);
            }

            // YouTube ID 추출 및 콘텐츠 미리보기 생성
            foreach ($resources as &$resource) {
                if (!empty($resource['link'])) {
                    $resource['youtube_id'] = $this->extractYoutubeId($resource['link']);
                }
                if (!empty($resource['content'])) {
                    $resource['content_preview'] = $this->getContentPreview($resource['content']);
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => count($resources) . '개의 리소스를 찾았습니다.',
                'data' => $resources
            ]);
        } catch (\Exception $e) {
            error_log("Error in getResourcesByTag: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => '서버 오류가 발생했습니다.',
                'data' => []
            ], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function extractYoutubeId($url) {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getContentPreview($content, $length = 200) {
        // HTML 태그 제거
        $text = strip_tags($content);
        // 줄바꿈을 공백으로 변환
        $text = str_replace(["\r", "\n"], ' ', $text);
        // 연속된 공백 제거
        $text = preg_replace('/\s+/', ' ', $text);
        // 길이 제한
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }
} 