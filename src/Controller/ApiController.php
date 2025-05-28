<?php

namespace App\Controller;

use App\Models\Resource;

class ApiController
{
    public function getResourcesByTag($tag) {
        try {
            if (empty($tag)) {
                return $this->jsonResponse(['error' => '태그가 비어있습니다.'], 400);
            }

            // URL 디코딩
            $tag = urldecode($tag);
            error_log("Searching for tag: " . $tag);

            $resourceModel = new \App\Models\Resource();
            $resources = $resourceModel->getResourcesByTag($tag);

            if ($resources === false) {
                return $this->jsonResponse(['error' => '리소스를 조회하는 중 오류가 발생했습니다.'], 500);
            }

            // 검색 결과가 없는 경우
            if (empty($resources)) {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => [],
                    'message' => '검색 결과가 없습니다.'
                ]);
            }

            // 각 리소스에 대해 YouTube 비디오 ID와 콘텐츠 미리보기 추가
            foreach ($resources as &$resource) {
                if (isset($resource['url']) && strpos($resource['url'], 'youtube.com') !== false) {
                    $resource['youtube_id'] = $this->extractYoutubeId($resource['url']);
                }
                if (isset($resource['content'])) {
                    $resource['content_preview'] = $this->getContentPreview($resource['content']);
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $resources,
                'message' => count($resources) . '개의 리소스를 찾았습니다.'
            ]);
        } catch (\Exception $e) {
            error_log("Error in getResourcesByTag: " . $e->getMessage());
            return $this->jsonResponse(['error' => '리소스를 조회하는 중 오류가 발생했습니다.'], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function extractYoutubeId($url) {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getContentPreview($content) {
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = strip_tags($content);
        $content = mb_substr($content, 0, 150, 'UTF-8');
        if (mb_strlen($content, 'UTF-8') > 150) {
            $content .= '...';
        }
        return $content;
    }
} 