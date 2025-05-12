<?php

namespace App\Model;

use App\Core\Database;

class BookmarkModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function isBookmarked($userId, $resourceId)
    {
        $sql = "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ? AND resource_id = ?";
        $result = $this->db->query($sql, [$userId, $resourceId])->fetch();
        return $result['count'] > 0;
    }

    public function getAllByUserId($userId)
    {
        $sql = "SELECT b.*, r.title, r.description, r.file_path, r.file_type 
                FROM bookmarks b 
                JOIN resources r ON b.resource_id = r.id 
                WHERE b.user_id = ? 
                ORDER BY b.created_at DESC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function create($userId, $resourceId, $folderId = null)
    {
        $sql = "INSERT INTO bookmarks (user_id, resource_id, folder_id) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$userId, $resourceId, $folderId]);
    }

    public function delete($bookmarkId, $userId)
    {
        $sql = "DELETE FROM bookmarks WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$bookmarkId, $userId]);
    }

    public function moveToFolder($bookmarkId, $userId, $folderId)
    {
        $sql = "UPDATE bookmarks SET folder_id = ? WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$folderId, $bookmarkId, $userId]);
    }
} 