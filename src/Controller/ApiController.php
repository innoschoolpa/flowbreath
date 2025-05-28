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

            $resourceModel = new \App\Models\Resource();
            $resources = $resourceModel->getResourcesByTag($tag);

            if ($resources === false) {
                return $this->jsonResponse(['error' => '리소스를 조회하는 중 오류가 발생했습니다.'], 500);
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
                'data' => $resources
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
        // Implementation of extractYoutubeId method
    }

    private function getContentPreview($content) {
        // Implementation of getContentPreview method
    }
} 