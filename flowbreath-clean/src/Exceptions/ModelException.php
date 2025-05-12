<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ModelException extends Exception
{
    public const ERROR_CREATE = '데이터 생성 중 오류가 발생했습니다';
    public const ERROR_UPDATE = '데이터 업데이트 중 오류가 발생했습니다';
    public const ERROR_DELETE = '데이터 삭제 중 오류가 발생했습니다';
    public const ERROR_FIND = '데이터 조회 중 오류가 발생했습니다';
    public const ERROR_FIND_ALL = '데이터 목록 조회 중 오류가 발생했습니다';
    public const ERROR_COUNT = '데이터 수 조회 중 오류가 발생했습니다';
    public const ERROR_WHERE = '조건 검색 중 오류가 발생했습니다';

    private array $context;

    public function __construct(string $message, array $context = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function createError(string $message, array $context = []): self
    {
        return new self(self::ERROR_CREATE . ': ' . $message, $context);
    }

    public static function updateError(string $message, array $context = []): self
    {
        return new self(self::ERROR_UPDATE . ': ' . $message, $context);
    }

    public static function deleteError(string $message, array $context = []): self
    {
        return new self(self::ERROR_DELETE . ': ' . $message, $context);
    }

    public static function findError(string $message, array $context = []): self
    {
        return new self(self::ERROR_FIND . ': ' . $message, $context);
    }

    public static function findAllError(string $message, array $context = []): self
    {
        return new self(self::ERROR_FIND_ALL . ': ' . $message, $context);
    }

    public static function countError(string $message, array $context = []): self
    {
        return new self(self::ERROR_COUNT . ': ' . $message, $context);
    }

    public static function whereError(string $message, array $context = []): self
    {
        return new self(self::ERROR_WHERE . ': ' . $message, $context);
    }
} 