<?php

namespace App\Model;

use App\Core\Database;

class ResourceModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getById($id)
    {
        $sql = "SELECT r.*, u.username as author_name 
                FROM resources r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function getAll()
    {
        $sql = "SELECT r.*, u.username as author_name 
                FROM resources r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create($userId, $title, $description, $filePath, $fileType)
    {
        $sql = "INSERT INTO resources (user_id, title, description, file_path, file_type) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [$userId, $title, $description, $filePath, $fileType]);
    }

    public function update($id, $userId, $title, $description)
    {
        $sql = "UPDATE resources 
                SET title = ?, description = ? 
                WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$title, $description, $id, $userId]);
    }

    public function delete($id, $userId)
    {
        $sql = "DELETE FROM resources WHERE id = ? AND user_id = ?";
        return $this->db->query($sql, [$id, $userId]);
    }
} 