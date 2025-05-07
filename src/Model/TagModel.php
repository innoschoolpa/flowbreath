<?php
// src/Model/TagModel.php

namespace Model;

use Core\Database;

class TagModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllTags() {
        $query = "SELECT t.*, COUNT(rt.resource_id) as usage_count 
                 FROM tags t 
                 LEFT JOIN resource_tags rt ON t.tag_id = rt.tag_id 
                 GROUP BY t.tag_id 
                 ORDER BY t.tag_name ASC";
        return $this->db->query($query)->fetchAll();
    }

    public function createTag($tagName, $description = '') {
        $query = "INSERT INTO tags (tag_name, description) VALUES (?, ?)";
        return $this->db->query($query, [$tagName, $description]);
    }

    public function updateTag($tagId, $tagName, $description = '') {
        $query = "UPDATE tags SET tag_name = ?, description = ? WHERE tag_id = ?";
        return $this->db->query($query, [$tagName, $description, $tagId]);
    }

    public function deleteTag($tagId) {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // First delete all resource_tags entries
            $query1 = "DELETE FROM resource_tags WHERE tag_id = ?";
            $this->db->query($query1, [$tagId]);

            // Then delete the tag itself
            $query2 = "DELETE FROM tags WHERE tag_id = ?";
            $this->db->query($query2, [$tagId]);

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback on error
            $this->db->rollback();
            throw $e;
        }
    }

    public function getTagById($tagId) {
        $query = "SELECT * FROM tags WHERE tag_id = ?";
        return $this->db->query($query, [$tagId])->fetch();
    }

    public function getTagsByResourceId($resourceId) {
        $query = "SELECT t.* 
                 FROM tags t 
                 JOIN resource_tags rt ON t.tag_id = rt.tag_id 
                 WHERE rt.resource_id = ?";
        return $this->db->query($query, [$resourceId])->fetchAll();
    }

    public function addTagToResource($resourceId, $tagId) {
        $query = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
        return $this->db->query($query, [$resourceId, $tagId]);
    }

    public function removeTagFromResource($resourceId, $tagId) {
        $query = "DELETE FROM resource_tags WHERE resource_id = ? AND tag_id = ?";
        return $this->db->query($query, [$resourceId, $tagId]);
    }
} 