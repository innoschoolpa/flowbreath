<?php
namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Core\Database;
use App\Models\BaseModel;
use App\Exceptions\ModelException;

class Resource2 extends BaseModel {
    protected string $table = 'resource2';
    protected array $fillable = [
        'title', 'url', 'description', 'content',
        'created_at', 'updated_at'
    ];

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    /**
     * 모든 리소스를 가져옵니다.
     *
     * @param int|null $limit 가져올 리소스의 수
     * @param int|null $offset 시작 위치
     * @return array 리소스 목록
     * @throws ModelException
     */
    public function getAll($limit = null, $offset = null): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->pdo->prepare($sql);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new ModelException("Failed to fetch resources: " . $e->getMessage());
        }
    }

    /**
     * ID로 리소스를 가져옵니다.
     *
     * @param int $id 리소스 ID
     * @return array|null 리소스 데이터
     * @throws ModelException
     */
    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new ModelException("Failed to fetch resource: " . $e->getMessage());
        }
    }
} 