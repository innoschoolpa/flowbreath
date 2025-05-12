<?php

namespace App\Models;

use App\Core\Database;

class Like
{
    private static function getDB()
    {
        return Database::getInstance();
    }

    public static function hasUserLiked($resourceId, $userId)
    {
        $db = self::getDB();
        $sql = "SELECT COUNT(*) as count FROM likes WHERE resource_id = ? AND user_id = ?";
        $result = $db->fetch($sql, [$resourceId, $userId]);
        return ($result['count'] ?? 0) > 0;
    }

    public static function countResourceLikes($resourceId)
    {
        $db = self::getDB();
        $sql = "SELECT COUNT(*) as count FROM likes WHERE resource_id = ?";
        $result = $db->fetch($sql, [$resourceId]);
        return $result['count'] ?? 0;
    }

    public static function toggleLike($resourceId, $userId)
    {
        $db = self::getDB();
        
        // 트랜잭션 시작
        $db->beginTransaction();
        
        try {
            // 이미 좋아요가 있는지 확인
            $sql = "SELECT id FROM likes WHERE resource_id = ? AND user_id = ?";
            $existing = $db->fetch($sql, [$resourceId, $userId]);
            
            if ($existing) {
                // 좋아요가 있으면 삭제
                $sql = "DELETE FROM likes WHERE id = ?";
                $db->query($sql, [$existing['id']]);
                $result = false;
            } else {
                // 좋아요가 없으면 추가
                $sql = "INSERT INTO likes (resource_id, user_id) VALUES (?, ?)";
                $db->query($sql, [$resourceId, $userId]);
                $result = true;
            }
            
            $db->commit();
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
} 