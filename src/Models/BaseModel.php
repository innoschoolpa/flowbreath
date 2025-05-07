<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use App\Exceptions\ModelException;

/**
 * 기본 모델 클래스
 * 모든 모델의 기본이 되는 클래스입니다.
 */
abstract class BaseModel {
    protected PDO $pdo;
    protected string $table;
    protected array $fillable = [];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 모든 레코드 조회
     */
    public function all(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::findAllError($e->getMessage(), $context);
        }
    }

    /**
     * ID로 레코드 조회
     */
    public function find(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::findError($e->getMessage(), $context);
        }
    }

    /**
     * 새 레코드 생성
     */
    public function create(array $data): ?array {
        try {
            $data = array_intersect_key($data, array_flip($this->fillable));
            
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->find((int)$this->pdo->lastInsertId());
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'data' => $data,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::createError($e->getMessage(), $context);
        }
    }

    /**
     * 레코드 업데이트
     */
    public function update(int $id, array $data): ?array {
        try {
            $data = array_intersect_key($data, array_flip($this->fillable));
            
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }
            $fields = implode(', ', $fields);
            
            $sql = "UPDATE {$this->table} SET $fields WHERE id = :id";
            $data['id'] = $id;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->find($id);
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::updateError($e->getMessage(), $context);
        }
    }

    /**
     * 레코드 삭제
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::deleteError($e->getMessage(), $context);
        }
    }

    /**
     * 조건에 맞는 레코드 조회
     */
    public function where(string $field, mixed $value): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $field = :value");
            $stmt->execute(['value' => $value]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'field' => $field,
                'value' => $value,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::whereError($e->getMessage(), $context);
        }
    }

    /**
     * 레코드 수 조회
     */
    public function count(): int {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $context = [
                'table' => $this->table,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            throw ModelException::countError($e->getMessage(), $context);
        }
    }
} 