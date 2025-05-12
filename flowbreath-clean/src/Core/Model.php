<?php

namespace App\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $fillable = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function findOrFail(int $id): array
    {
        $result = $this->find($id);
        if (!$result) {
            throw new \Exception("Record not found");
        }
        return $result;
    }

    /**
     * 새로운 레코드 생성
     * 
     * @param array $data
     * @return int|null 생성된 레코드의 ID 또는 실패 시 null
     */
    public function create(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->table,
                implode(', ', $fields),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            $id = (int)$this->db->lastInsertId();
            
            $this->db->commit();
            return $id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Failed to create record in {$this->table}: " . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data): ?array
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $this->db->update($this->table, $data, 'id = ?', [$id]);
        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete($this->table, 'id = ?', [$id]) > 0;
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function where(string $conditions, array $params = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE " . $conditions;
        return $this->db->fetchAll($sql, $params);
    }

    public function first(string $conditions, array $params = []): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE " . $conditions;
        return $this->db->fetch($sql, $params);
    }
} 