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

            // URL 디코딩
            $tag = urldecode($tag);
            
            // 태그 검색 로그
            error_log("Searching for tag: " . $tag);

            $resourceModel = new \App\Models\Resource();
            $resources = $resourceModel->getResourcesByTag($tag);

            if ($resources === false) {
                error_log("Error fetching resources for tag: " . $tag);
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

            // 각 리소스에 대해 YouTube ID와 콘텐츠 미리보기 추가
            foreach ($resources as &$resource) {
                // YouTube ID 추출
                if (!empty($resource['link'])) {
                    $youtube_pattern = '/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=|live\\/)|youtu\\.be\\/)([^"&?\\/\\s]{11})/';
                    if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $resource['video_id'] = $matches[1];
                    }
                }

                // 태그 처리
                if (!empty($resource['tags'])) {
                    if (is_array($resource['tags'])) {
                        $resource['tags'] = array_map(function($tag) {
                            return ['name' => is_array($tag) ? $tag['name'] : $tag];
                        }, $resource['tags']);
                    } else {
                        $resource['tags'] = array_map(function($tag) {
                            return ['name' => trim($tag)];
                        }, explode(',', $resource['tags']));
                    }
                } else {
                    $resource['tags'] = [];
                }

                // 콘텐츠 미리보기 생성
                if (!empty($resource['content'])) {
                    $resource['preview'] = mb_substr(strip_tags($resource['content']), 0, 200) . '...';
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => '리소스를 성공적으로 조회했습니다.',
                'data' => $resources
            ]);

        } catch (\Exception $e) {
            error_log("Error in getResourcesByTag: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->jsonResponse([
                'success' => false,
                'message' => '리소스를 검색하는 중 오류가 발생했습니다: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        // 오류 출력 버퍼링 비활성화
        if (ob_get_level()) ob_end_clean();
        
        // 이전 출력 버퍼 제거
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // 헤더 설정
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // 상태 코드 설정
        http_response_code($statusCode);
        
        // JSON 인코딩 및 출력
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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