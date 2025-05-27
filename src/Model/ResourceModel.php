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

    public function getRecentResources($limit = 10, $language = 'ko')
    {
        $sql = "SELECT r.*, rt.title, rt.content, rt.description, u.username as author_name,
                GROUP_CONCAT(t.name) as tags
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.status = 'published' AND r.visibility = 'public'
                GROUP BY r.id
                ORDER BY r.created_at DESC
                LIMIT ?";

        $resources = $this->db->query($sql, [$language, $limit])->fetchAll();
        
        foreach ($resources as &$resource) {
            $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
        }
        
        return $resources;
    }

    public function getById($id, $language = 'ko')
    {
        $sql = "SELECT r.*, rt.title, rt.content, rt.description, u.username as author_name,
                GROUP_CONCAT(t.name) as tags
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.id = ?
                GROUP BY r.id";
        
        $resource = $this->db->query($sql, [$language, $id])->fetch();
        
        if ($resource) {
            $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
        }
        
        return $resource;
    }

    public function getAll($language = 'ko', $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT r.*, rt.title, rt.content, rt.description, u.username as author_name,
                GROUP_CONCAT(t.name) as tags
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.status = 'published' AND r.visibility = 'public'
                GROUP BY r.id
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        
        $resources = $this->db->query($sql, [$language, $perPage, $offset])->fetchAll();
        
        foreach ($resources as &$resource) {
            $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
        }
        
        return $resources;
    }

    public function create($userId, $data, $translations = [])
    {
        $this->db->beginTransaction();
        
        try {
            // Insert main resource
            $sql = "INSERT INTO resources (user_id, slug, visibility, status) 
                    VALUES (?, ?, ?, ?)";
            $this->db->query($sql, [
                $userId,
                $data['slug'],
                $data['visibility'] ?? 'private',
                $data['status'] ?? 'draft'
            ]);
            
            $resourceId = $this->db->lastInsertId();
            
            // Insert translations
            foreach ($translations as $lang => $translation) {
                $sql = "INSERT INTO resource_translations 
                        (resource_id, language_code, title, content, description) 
                        VALUES (?, ?, ?, ?, ?)";
                $this->db->query($sql, [
                    $resourceId,
                    $lang,
                    $translation['title'],
                    $translation['content'] ?? null,
                    $translation['description'] ?? null
                ]);
            }
            
            $this->db->commit();
            return $resourceId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $userId, $data, $translations = [])
    {
        $this->db->beginTransaction();
        
        try {
            // Update main resource
            $sql = "UPDATE resources 
                    SET visibility = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?";
            $this->db->query($sql, [
                $data['visibility'] ?? 'private',
                $data['status'] ?? 'draft',
                $id,
                $userId
            ]);
            
            // Update translations
            foreach ($translations as $lang => $translation) {
                $sql = "INSERT INTO resource_translations 
                        (resource_id, language_code, title, content, description) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        title = VALUES(title),
                        content = VALUES(content),
                        description = VALUES(description),
                        updated_at = CURRENT_TIMESTAMP";
                $this->db->query($sql, [
                    $id,
                    $lang,
                    $translation['title'],
                    $translation['content'] ?? null,
                    $translation['description'] ?? null
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id, $userId)
    {
        $this->db->beginTransaction();
        
        try {
            // Delete translations
            $sql = "DELETE FROM resource_translations WHERE resource_id = ?";
            $this->db->query($sql, [$id]);
            
            // Delete main resource
            $sql = "DELETE FROM resources WHERE id = ? AND user_id = ?";
            $this->db->query($sql, [$id, $userId]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 