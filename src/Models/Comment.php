<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Comment
{
    protected $db;
    protected $table = 'comments';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByResourceId($resourceId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT c.*, u.name as author_name, u.profile_image 
                FROM {$this->table} c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.resource_id = :resource_id 
                AND c.parent_id IS NULL 
                AND c.deleted_at IS NULL 
                ORDER BY c.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':resource_id', $resourceId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByResourceId($resourceId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE resource_id = :resource_id 
                AND parent_id IS NULL 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':resource_id', $resourceId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function getReplies($commentId)
    {
        $sql = "SELECT c.*, u.name as author_name, u.profile_image 
                FROM {$this->table} c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.parent_id = :parent_id 
                AND c.deleted_at IS NULL 
                ORDER BY c.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':parent_id', $commentId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "SELECT c.*, u.name as author_name, u.profile_image 
                FROM {$this->table} c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.id = :id 
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (resource_id, user_id, parent_id, content, created_at) 
                VALUES 
                (:resource_id, :user_id, :parent_id, :content, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':resource_id', $data['resource_id'], PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':parent_id', $data['parent_id'], PDO::PARAM_INT);
        $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET content = :content, 
                    updated_at = NOW() 
                WHERE id = :id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function delete($id)
    {
        try {
            // 먼저 댓글이 존재하는지 확인
            $checkSql = "SELECT id FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if (!$checkStmt->fetch()) {
                error_log("Comment not found or already deleted: ID = {$id}");
                return false;
            }

            // 트랜잭션 시작
            $this->db->beginTransaction();

            try {
                $sql = "UPDATE {$this->table} 
                        SET deleted_at = NOW() 
                        WHERE id = :id 
                        AND deleted_at IS NULL";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $result = $stmt->execute();
                
                if (!$result) {
                    throw new \PDOException("Failed to execute delete query");
                }

                $rowCount = $stmt->rowCount();
                if ($rowCount === 0) {
                    throw new \PDOException("No rows were affected by the delete operation");
                }

                // 트랜잭션 커밋
                $this->db->commit();
                error_log("Comment successfully deleted: ID = {$id}");
                return true;

            } catch (\PDOException $e) {
                // 트랜잭션 롤백
                $this->db->rollBack();
                error_log("Comment deletion failed: " . $e->getMessage());
                return false;
            }
        } catch (\Exception $e) {
            error_log("Unexpected error in comment deletion: " . $e->getMessage());
            return false;
        }
    }

    public function report($commentId, $userId, $reason)
    {
        $sql = "INSERT INTO comment_reports 
                (comment_id, user_id, reason, created_at) 
                VALUES 
                (:comment_id, :user_id, :reason, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function block($commentId)
    {
        $sql = "UPDATE {$this->table} 
                SET is_blocked = 1, 
                    blocked_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $commentId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function addReaction($commentId, $userId, $reactionType)
    {
        $sql = "INSERT INTO comment_reactions 
                (comment_id, user_id, reaction_type, created_at) 
                VALUES 
                (:comment_id, :user_id, :reaction_type, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':reaction_type', $reactionType, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function removeReaction($commentId, $userId)
    {
        $sql = "DELETE FROM comment_reactions 
                WHERE comment_id = :comment_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}