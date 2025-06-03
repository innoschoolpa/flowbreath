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
        
        error_log("getList - userId parameter: " . ($userId ?? 'null'));
        
        if ($userId) {
            $where .= " AND (d.is_public = 1 OR d.user_id = :user_id)";
            $params[':user_id'] = $userId;
        } else {
            $where .= " AND d.is_public = 1";
        }

        $sql = "SELECT d.*, u.name as author_name, u.profile_image,
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id AND deleted_at IS NULL) as comment_count,
                " . ($userId ? "(SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id AND user_id = :like_user_id) as is_liked" : "0 as is_liked") . "
                FROM diaries d
                LEFT JOIN users u ON d.user_id = u.id
                $where
                ORDER BY d.created_at DESC
                LIMIT :limit OFFSET :offset";

        // Add parameters in the correct order
        if ($userId) {
            $params[':like_user_id'] = $userId;
        }
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;

        try {
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters with their types
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            $diaries = $stmt->fetchAll();

            // Debug the first diary entry
            if (!empty($diaries)) {
                error_log("First diary entry: " . print_r($diaries[0], true));
            }

            // Convert is_liked to boolean for each diary
            foreach ($diaries as &$diary) {
                $diary['is_liked'] = (bool)$diary['is_liked'];
            }

            // Get total count
            $countSql = "SELECT COUNT(*) FROM diaries d $where";
            $stmt = $this->db->prepare($countSql);
            
            // Bind parameters for count query
            if ($userId) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $total = $stmt->fetchColumn();

            return [
                'items' => $diaries,
                'total' => $total
            ];
        } catch (\PDOException $e) {
            error_log("Database error in getList: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            return [
                'items' => [],
                'total' => 0
            ];
        }
    }

    public function find($id) {
        $userId = $_SESSION['user_id'] ?? null;
        $sql = "SELECT d.*, u.name as author_name, u.profile_image,
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id AND deleted_at IS NULL) as comment_count,
                " . ($userId ? "(SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id AND user_id = ?) as is_liked" : "0 as is_liked") . "
                FROM diaries d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ? AND d.deleted_at IS NULL";
        
        $params = $userId ? [$userId, $id] : [$id];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $diary = $stmt->fetch();
        
        if ($diary) {
            $diary['is_liked'] = (bool)$diary['is_liked'];
            // 태그는 원본 문자열 유지
            $diary['tags'] = $diary['tags'] ?? '';
            // 디버그 정보
            error_log("Diary::find - Diary data: " . print_r($diary, true));
        }
        
        return $diary;
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
        try {
            error_log("Updating diary in database - ID: " . $id);
            error_log("Update data: " . print_r($data, true));

            $sql = "UPDATE diaries SET 
                    title = :title,
                    content = :content,
                    tags = :tags,
                    is_public = :is_public,
                    updated_at = :updated_at
                    WHERE id = :id AND deleted_at IS NULL";

            $params = [
                ':id' => $id,
                ':title' => $data['title'],
                ':content' => $data['content'],
                ':tags' => $data['tags'],
                ':is_public' => $data['is_public'],
                ':updated_at' => $data['updated_at']
            ];

            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                error_log("Diary updated successfully in database - ID: " . $id);
                return true;
            } else {
                error_log("Failed to update diary in database - ID: " . $id);
                error_log("Database error: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (\PDOException $e) {
            error_log("Database error while updating diary: " . $e->getMessage());
            throw $e;
        }
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

        $sql = "SELECT d.*, u.name as author_name, u.profile_image,
                (SELECT COUNT(*) FROM diary_likes WHERE diary_id = d.id) as like_count,
                (SELECT COUNT(*) FROM diary_comments WHERE diary_id = d.id AND deleted_at IS NULL) as comment_count
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

    public function getLikeCount($diaryId) {
        $sql = "SELECT COUNT(*) as count FROM diary_likes WHERE diary_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$diaryId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    public function addComment($data) {
        try {
            // 트랜잭션 시작
            $this->db->beginTransaction();

            // 댓글 추가
            $sql = "INSERT INTO diary_comments (diary_id, user_id, content, created_at)
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['diary_id'],
                $data['user_id'],
                $data['content'],
                $data['created_at']
            ]);

            if (!$result) {
                error_log("Failed to add comment: " . implode(", ", $stmt->errorInfo()));
                $this->db->rollBack();
                return false;
            }

            // lastInsertId가 실패할 경우를 대비한 대체 방법
            $commentId = $this->db->lastInsertId();
            if (!$commentId) {
                error_log("lastInsertId failed, trying alternative method");
                $query = $this->db->prepare("SELECT MAX(id) as max_id FROM diary_comments WHERE diary_id = ? AND user_id = ? AND created_at = ?");
                $query->execute([$data['diary_id'], $data['user_id'], $data['created_at']]);
                $row = $query->fetch();
                $commentId = $row ? $row['max_id'] : null;
                
                if (!$commentId) {
                    error_log("Failed to get comment ID using alternative method");
                    $this->db->rollBack();
                    return false;
                }
            }

            // 댓글 수 업데이트
            $updateSql = "UPDATE diaries SET comment_count = comment_count + 1 WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateResult = $updateStmt->execute([$data['diary_id']]);

            if (!$updateResult) {
                error_log("Failed to update comment count: " . implode(", ", $updateStmt->errorInfo()));
                $this->db->rollBack();
                return false;
            }

            // 트랜잭션 커밋
            $this->db->commit();
            return $commentId;

        } catch (\PDOException $e) {
            error_log("Database error in addComment: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            $this->db->rollBack();
            return false;
        }
    }

    public function findComment($id) {
        $sql = "SELECT c.*, u.name as author_name, u.profile_image 
                FROM diary_comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = ? AND c.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteComment($id) {
        $sql = "UPDATE diary_comments SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getComments($diaryId) {
        $sql = "SELECT c.*, u.name as author_name, u.profile_image 
                FROM diary_comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.diary_id = ? AND c.deleted_at IS NULL
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$diaryId]);
        return $stmt->fetchAll();
    }

    public function updateComment($id, $content) {
        try {
            $sql = "UPDATE diary_comments 
                    SET content = ?, updated_at = NOW() 
                    WHERE id = ? AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$content, $id]);
        } catch (\PDOException $e) {
            error_log("Database error in updateComment: " . $e->getMessage());
            return false;
        }
    }

    public function incrementViewCount($id) {
        try {
            $sql = "UPDATE diaries SET view_count = view_count + 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Database error in incrementViewCount: " . $e->getMessage());
            return false;
        }
    }
} 