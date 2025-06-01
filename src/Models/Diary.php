<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Diary {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getList($page = 1, $limit = 20, $userId = null) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = "WHERE d.deleted_at IS NULL";
        
        if ($userId) {
            $where .= " AND (d.is_public = 1 OR d.user_id = ?)";
            $params[] = $userId;
        } else {
            $where .= " AND d.is_public = 1";
        }

        $sql = "SELECT d.*, u.name as author_name, 
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id) as comment_count
                FROM diaries d
                LEFT JOIN users u ON d.user_id = u.id
                $where
                ORDER BY d.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $diaries = $stmt->fetchAll();

        // Get total count
        $countSql = "SELECT COUNT(*) FROM diaries d $where";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute(array_slice($params, 0, -2));
        $total = $stmt->fetchColumn();

        return [
            'items' => $diaries,
            'total' => $total
        ];
    }

    public function find($id) {
        $sql = "SELECT d.*, u.name as author_name,
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id) as comment_count
                FROM diaries d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ? AND d.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO diaries (user_id, title, content, tags, is_public, created_at, updated_at, view_count, like_count, comment_count)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 0, 0, 0)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['user_id'],
                $data['title'],
                $data['content'],
                $data['tags'],
                $data['is_public']
            ]);

            if (!$result) {
                error_log("Failed to create diary: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            $insertId = $this->db->lastInsertId();
            if (!$insertId || $insertId == 0) {
                // lastInsertId가 0이거나 false일 때, 실제로 insert된 id를 조회해서 반환
                $query = $this->db->prepare("SELECT MAX(id) as max_id FROM diaries WHERE user_id = ?");
                $query->execute([$data['user_id']]);
                $row = $query->fetch();
                $insertId = $row ? $row['max_id'] : null;
            }

            return $insertId;
        } catch (\PDOException $e) {
            error_log("Database error while creating diary: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $sql = "UPDATE diaries 
                SET title = ?, content = ?, tags = ?, is_public = ?, updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $data['tags'],
            $data['is_public'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "UPDATE diaries SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function search($query, $tag, $startDate, $endDate, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = ["d.deleted_at IS NULL"];

        if ($query) {
            $where[] = "(d.title LIKE ? OR d.content LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        if ($tag) {
            $where[] = "d.tags LIKE ?";
            $params[] = "%$tag%";
        }

        if ($startDate) {
            $where[] = "d.created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $where[] = "d.created_at <= ?";
            $params[] = $endDate;
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT d.*, u.name as author_name,
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id) as comment_count
                FROM diaries d
                LEFT JOIN users u ON d.user_id = u.id
                $whereClause
                ORDER BY d.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $diaries = $stmt->fetchAll();

        // Get total count
        $countSql = "SELECT COUNT(*) FROM diaries d $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute(array_slice($params, 0, -2));
        $total = $stmt->fetchColumn();

        return [
            'items' => $diaries,
            'total' => $total
        ];
    }

    public function toggleLike($diaryId, $userId) {
        $sql = "SELECT * FROM diary_likes WHERE diary_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$diaryId, $userId]);
        $like = $stmt->fetch();

        if ($like) {
            $sql = "DELETE FROM diary_likes WHERE diary_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$diaryId, $userId]);
            return false;
        } else {
            $sql = "INSERT INTO diary_likes (diary_id, user_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$diaryId, $userId]);
            return true;
        }
    }

    public function addComment($data) {
        $sql = "INSERT INTO diary_comments (diary_id, user_id, content, created_at)
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['diary_id'],
            $data['user_id'],
            $data['content']
        ]);

        return $this->db->lastInsertId();
    }

    public function findComment($id) {
        $sql = "SELECT * FROM diary_comments WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteComment($id) {
        $sql = "UPDATE diary_comments SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 