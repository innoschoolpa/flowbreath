<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Diary {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $title, $content, $isPublic, $tags = []) {
        try {
            $this->db->beginTransaction();

            // Insert diary
            $stmt = $this->db->prepare("
                INSERT INTO diaries (user_id, title, content, is_public)
                VALUES (:user_id, :title, :content, :is_public)
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':title' => $title,
                ':content' => $content,
                ':is_public' => $isPublic
            ]);

            $diaryId = $this->db->lastInsertId();

            // Insert tags
            if (!empty($tags)) {
                $tagStmt = $this->db->prepare("
                    INSERT INTO diary_tags (diary_id, tag_id)
                    VALUES (:diary_id, :tag_id)
                ");

                foreach ($tags as $tagId) {
                    $tagStmt->execute([
                        ':diary_id' => $diaryId,
                        ':tag_id' => $tagId
                    ]);
                }
            }

            $this->db->commit();
            return $diaryId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as author_name, u.profile_image
            FROM diaries d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getList($page = 1, $limit = 20, $userId = null) {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE d.is_public = 1";
        $params = [];
        
        if ($userId) {
            $where .= " OR d.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->db->prepare("
            SELECT d.*, u.name as author_name, u.profile_image,
                   COUNT(DISTINCT dl.user_id) as like_count,
                   COUNT(DISTINCT dc.id) as comment_count
            FROM diaries d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN diary_likes dl ON d.id = dl.diary_id
            LEFT JOIN diary_comments dc ON d.id = dc.diary_id
            $where
            GROUP BY d.id
            ORDER BY d.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $userId, $title, $content, $isPublic, $tags = []) {
        try {
            $this->db->beginTransaction();

            // Update diary
            $stmt = $this->db->prepare("
                UPDATE diaries 
                SET title = :title, content = :content, is_public = :is_public
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                ':id' => $id,
                ':user_id' => $userId,
                ':title' => $title,
                ':content' => $content,
                ':is_public' => $isPublic
            ]);

            // Update tags
            $this->db->prepare("DELETE FROM diary_tags WHERE diary_id = ?")->execute([$id]);
            
            if (!empty($tags)) {
                $tagStmt = $this->db->prepare("
                    INSERT INTO diary_tags (diary_id, tag_id)
                    VALUES (:diary_id, :tag_id)
                ");

                foreach ($tags as $tagId) {
                    $tagStmt->execute([
                        ':diary_id' => $id,
                        ':tag_id' => $tagId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("
            DELETE FROM diaries 
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    public function toggleLike($diaryId, $userId) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT 1 FROM diary_likes 
                WHERE diary_id = :diary_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':diary_id' => $diaryId,
                ':user_id' => $userId
            ]);

            if ($stmt->fetch()) {
                // Unlike
                $stmt = $this->db->prepare("
                    DELETE FROM diary_likes 
                    WHERE diary_id = :diary_id AND user_id = :user_id
                ");
            } else {
                // Like
                $stmt = $this->db->prepare("
                    INSERT INTO diary_likes (diary_id, user_id)
                    VALUES (:diary_id, :user_id)
                ");
            }

            $stmt->execute([
                ':diary_id' => $diaryId,
                ':user_id' => $userId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function search($query, $tags = [], $startDate = null, $endDate = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ["d.is_public = 1"];
        $params = [];

        if ($query) {
            $where[] = "(d.title LIKE :query OR d.content LIKE :query)";
            $params[':query'] = "%$query%";
        }

        if (!empty($tags)) {
            $where[] = "EXISTS (
                SELECT 1 FROM diary_tags dt 
                WHERE dt.diary_id = d.id AND dt.tag_id IN (" . implode(',', $tags) . ")
            )";
        }

        if ($startDate) {
            $where[] = "d.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $where[] = "d.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT d.*, u.name as author_name, u.profile_image,
                   COUNT(DISTINCT dl.user_id) as like_count,
                   COUNT(DISTINCT dc.id) as comment_count
            FROM diaries d
            JOIN users u ON d.user_id = u.id
            LEFT JOIN diary_likes dl ON d.id = dl.diary_id
            LEFT JOIN diary_comments dc ON d.id = dc.diary_id
            WHERE $whereClause
            GROUP BY d.id
            ORDER BY d.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 