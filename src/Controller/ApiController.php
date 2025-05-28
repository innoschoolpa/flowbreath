<?php

namespace App\Controller;

use App\Models\Resource;

class ApiController
{
    public function getResourcesByTag($tag) {
        try {
            $resourceModel = new \App\Models\Resource();
            $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
            $resources = $resourceModel->getResourcesByTag($tag, $lang);
            
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
                $content = strip_tags($resource['content'] ?? '');
                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $content = mb_substr($content, 0, 150, 'UTF-8') . '...';
                
                return [
                    'id' => $resource['id'],
                    'title' => $resource['title'],
                    'content_preview' => $content,
                    'video_id' => $videoId,
                    'author_name' => $resource['author_name'],
                    'created_at' => date('Y-m-d', strtotime($resource['created_at']))
                ];
            }, $resources);
            
            echo json_encode([
                'success' => true,
                'resources' => $formattedResources
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => '리소스를 불러오는 중 오류가 발생했습니다.'
            ]);
        }
    }
} 