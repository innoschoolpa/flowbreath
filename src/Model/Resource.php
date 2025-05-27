<?php

namespace App\Model;

use App\Core\Database;

class Resource
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRecentPublic($limit = 10, $language = 'ko')
    {
        $sql = "SELECT r.*, rt.title, rt.content, rt.description, u.name as author_name,
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

    public function searchResources($query, $limit = 10, $offset = 0, $language = 'ko')
    {
        $sql = "SELECT r.*, rt.title, rt.content, rt.description, u.name as author_name,
                GROUP_CONCAT(t.name) as tags
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.status = 'published' AND r.visibility = 'public'
                AND (
                    rt.title LIKE ? OR
                    rt.content LIKE ? OR
                    rt.description LIKE ? OR
                    t.name LIKE ?
                )
                GROUP BY r.id
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";

        $searchTerm = "%{$query}%";
        $resources = $this->db->query($sql, [
            $language,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $limit,
            $offset
        ])->fetchAll();
        
        foreach ($resources as &$resource) {
            $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
        }
        
        return $resources;
    }
} 