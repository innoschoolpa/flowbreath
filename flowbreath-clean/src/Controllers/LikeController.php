<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Like;
use App\Models\Resource;

class LikeController extends Controller
{
    public function toggle(Request $request, $resourceId)
    {
        try {
            $userId = $request->getUser()->id;

            // 리소스 존재 확인
            $resource = Resource::findOrFail($resourceId);

            // 좋아요 토글
            $isLiked = Like::toggleLike($resourceId, $userId);
            $likeCount = Like::countResourceLikes($resourceId);

            return [
                'success' => true,
                'data' => [
                    'is_liked' => $isLiked,
                    'like_count' => $likeCount
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function status(Request $request, $resourceId)
    {
        try {
            $userId = $request->getUser()->id;
            
            $isLiked = Like::hasUserLiked($resourceId, $userId);
            $likeCount = Like::countResourceLikes($resourceId);

            return [
                'success' => true,
                'data' => [
                    'is_liked' => $isLiked,
                    'like_count' => $likeCount
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
} 