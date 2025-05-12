<?php
/**
 * src/Model/Tag.php
 * 태그 모델
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Core\Database;
use App\Core\Model;

class Tag extends Model {
    protected $table = 'tags';
    protected $fillable = ['name'];

    public function __construct() {
        parent::__construct(Database::getInstance());
    }
    
    /**
     * 태그 이름으로 태그 찾기
     */
    public function findByName(string $name): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM tags WHERE name = ?", [$name]) ?: null;
        } catch (PDOException $e) {
            error_log("Tag::findByName error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 새 태그 생성
     * 
     * @param array $data 태그 데이터
     * @return int|null 생성된 태그 id 또는 null
     */
    public function create(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            $id = $this->db->insert($this->table, ['name' => $data['name']]);
            
            $this->db->commit();
            
            return $id;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in Tag::create: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 태그 검색 (자동완성 등을 위해)
     */
    public function search(string $query): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM tags WHERE name LIKE ? ORDER BY name LIMIT 10",
                ["%$query%"]
            );
        } catch (PDOException $e) {
            error_log("Tag::search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 인기 태그 가져오기
     */
    public function getPopularTags($limit = 8)
    {
        try {
            $sql = "SELECT t.*, COUNT(rt.resource_id) as resource_count
                    FROM tags t
                    LEFT JOIN resource_tags rt ON t.id = rt.tag_id
                    GROUP BY t.id
                    ORDER BY resource_count DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        } catch (\PDOException $e) {
            error_log("Error in getPopularTags: " . $e->getMessage());
            throw new \Exception("인기 태그를 조회하는 중 오류가 발생했습니다.");
        }
    }
    
    /**
     * 리소스에 연결된 태그 가져오기
     */
    public function getByResourceId(int $resourceId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT t.id, t.name 
                FROM tags t 
                JOIN resource_tags rt ON t.id = rt.tag_id 
                WHERE rt.resource_id = ?
                ORDER BY t.name",
                [$resourceId]
            );
        } catch (PDOException $e) {
            error_log("Tag::getByResourceId error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 모든 태그 가져오기
     */
    public function getAll(): array
    {
        try {
            return $this->db->fetchAll("SELECT * FROM tags ORDER BY name");
        } catch (PDOException $e) {
            error_log("Tag::getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 태그 삭제
     */
    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            
            // 연관된 resource_tags 삭제
            $this->db->delete('resource_tags', 'tag_id = ?', [$id]);
            
            // 태그 삭제
            $result = parent::delete($id);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Tag::delete error: " . $e->getMessage());
            return false;
        }
    }

    public function findOrCreate($name)
    {
        try {
            // 기존 태그 찾기
            $sql = "SELECT * FROM tags WHERE name = ?";
            $tag = $this->db->fetch($sql, [$name]);

            if ($tag) {
                return $tag;
            }

            // 새 태그 생성
            $sql = "INSERT INTO tags (name, created_at) VALUES (?, NOW())";
            $this->db->query($sql, [$name]);
            
            return [
                'id' => $this->db->lastInsertId(),
                'name' => $name
            ];
        } catch (\PDOException $e) {
            error_log("Error in findOrCreate: " . $e->getMessage());
            throw new \Exception("태그를 생성하는 중 오류가 발생했습니다.");
        }
    }

    public function getResourceTags($resourceId)
    {
        try {
            $sql = "SELECT t.*
                    FROM tags t
                    JOIN resource_tags rt ON t.id = rt.tag_id
                    WHERE rt.resource_id = ?
                    ORDER BY t.name";
            
            return $this->db->fetchAll($sql, [$resourceId]);
        } catch (\PDOException $e) {
            error_log("Error in getResourceTags: " . $e->getMessage());
            throw new \Exception("리소스의 태그를 조회하는 중 오류가 발생했습니다.");
        }
    }
}