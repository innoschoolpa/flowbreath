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
use App\Models\BaseModel;
use Config\Database;

// Database 클래스가 이미 로드되었는지 확인
if (!class_exists('Config\Database')) {
    error_log("Database class not found, attempting to load database.php");
    $databasePath = __DIR__ . '/../config/database.php';
    if (!file_exists($databasePath)) {
        error_log("Error: database.php not found at: " . $databasePath);
        throw new Exception("Database configuration file not found at: " . $databasePath);
    }
    require_once $databasePath;
}

class Tag extends BaseModel {
    protected string $table = 'tags';
    protected array $fillable = ['tag_name'];

    public function __construct() {
        try {
            $pdo = Database::getInstance()->getConnection();
            parent::__construct($pdo);
        } catch (Exception $e) {
            error_log("Tag model initialization failed: " . $e->getMessage());
            throw new Exception("Tag model initialization failed: " . $e->getMessage());
        }
    }
    
    /**
     * 태그 이름으로 태그 찾기
     */
    public function findByName(string $name): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE tag_name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Tag::findByName error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 새 태그 생성
     * 
     * @param array $data 태그 데이터
     * @return array|null 생성된 태그 데이터 또는 null
     */
    public function create(array $data): ?array
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO {$this->table} (name) VALUES (:name)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['name' => $data['name']]);
            
            $id = (int)$this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            return $this->find($id);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
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
            $stmt = $this->pdo->prepare("SELECT * FROM tags WHERE tag_name LIKE ? ORDER BY tag_name LIMIT 10");
            $stmt->execute(["%$query%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Tag::search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 인기 태그 가져오기
     */
    public function getPopularTags(int $limit = 20): array
    {
        try {
            $sql = "SELECT t.tag_id, t.tag_name, COUNT(rt.resource_id) as count
                    FROM tags t
                    JOIN resource_tags rt ON t.tag_id = rt.tag_id
                    GROUP BY t.tag_id, t.tag_name
                    ORDER BY count DESC
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Tag::getPopularTags error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 리소스에 연결된 태그 가져오기
     */
    public function getByResourceId(int $resourceId): array
    {
        try {
            $sql = "SELECT t.tag_id, t.tag_name 
                    FROM tags t 
                    JOIN resource_tags rt ON t.tag_id = rt.tag_id 
                    WHERE rt.resource_id = ?
                    ORDER BY t.tag_name";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$resourceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $stmt = $this->pdo->query("SELECT * FROM tags ORDER BY tag_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Tag::getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 태그 삭제
     */
    public function delete(int $tagId): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("DELETE FROM resource_tags WHERE tag_id = ?");
            $stmt->execute([$tagId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM tags WHERE tag_id = ?");
            $result = $stmt->execute([$tagId]);
            
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Tag::delete error: " . $e->getMessage());
            return false;
        }
    }
}