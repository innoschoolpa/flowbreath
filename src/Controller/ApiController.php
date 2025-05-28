<?php

namespace App\Controller;

use App\Models\Resource;

class ApiController
{
    public function getResourcesByTag($tag) {
        try {
            if (empty($tag)) {
                throw new \Exception("태그가 지정되지 않았습니다.");
            }

            $resourceModel = new \App\Models\Resource();
            $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
            
            // URL 디코딩
            $tag = urldecode($tag);
            
            $resources = $resourceModel->getResourcesByTag($tag, $lang);
            
            if ($resources === false) {
                throw new \Exception("리소스를 불러오는 중 데이터베이스 오류가 발생했습니다.");
            }
            
            // Format resources for display
            $formattedResources = array_map(function($resource) {
                // Extract YouTube video ID if exists
                $videoId = null;
                if (!empty($resource['link'])) {
                    $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                    if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                        $videoId = $matches[1];
                    }
                }
                
                // Format content preview
                $content = $resource['content'] ?? '';
                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $content = strip_tags($content);
                $content = mb_substr($content, 0, 150, 'UTF-8');
                if (mb_strlen($content, 'UTF-8') > 150) {
                    $content .= '...';
                }
                
                return [
                    'id' => $resource['id'],
                    'title' => $resource['title'] ?? '',
                    'content_preview' => $content,
                    'video_id' => $videoId,
                    'author_name' => $resource['author_name'] ?? '',
                    'created_at' => date('Y-m-d', strtotime($resource['created_at'])),
                    'tags' => $resource['tags'] ?? []
                ];
            }, $resources);
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'resources' => $formattedResources
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            error_log("Error in getResourcesByTag: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 