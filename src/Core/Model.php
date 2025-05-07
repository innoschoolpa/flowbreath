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

    public function create(array $data): ?array
    {
        $data = array_intersect_key($data, array_flip($this->fillable));
        $id = $this->db->insert($this->table, $data);
        return $this->find($id);
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