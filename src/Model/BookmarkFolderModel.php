<?php

namespace App\Model;

use App\Core\Database;

class BookmarkFolderModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllByUserId($userId)
    {
        $sql = "SELECT * FROM bookmark_folders WHERE user_id = ? ORDER BY name ASC";
        return $this->db->query($sql, [$userId])->fetchAll();
    }

    public function create($userId, $name)
    {
        $sql = "INSERT INTO bookmark_folders (user_id, name) VALUES (?, ?)";
        return $this->db->query($sql, [$userId, $name]);
    }

    public function update($folderId, $userId, $name)
    {
        $sql = "UPDATE bookmark_folders SET name = ? WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$name, $folderId, $userId]);
    }

    public function delete($folderId, $userId)
    {
        $sql = "DELETE FROM bookmark_folders WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$folderId, $userId]);
    }
} 