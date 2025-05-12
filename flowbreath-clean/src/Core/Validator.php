<?php

namespace App\Core;

class Validator
{
    private $errors = [];
    private $data = [];

    public function validate(array $rules)
    {
        $this->errors = [];
        $this->data = $_POST;

        foreach ($rules as $field => $rule) {
            $value = $this->data[$field] ?? null;

            // Required 체크
            if (!empty($rule['required']) && empty($value)) {
                $this->errors[$field] = $rule['message'] ?? "{$field}는 필수 입력 항목입니다.";
                continue;
            }

            // 값이 비어있고 required가 아닌 경우 다음 규칙으로
            if (empty($value) && empty($rule['required'])) {
                continue;
            }

            // 최소 길이 체크
            if (!empty($rule['min']) && mb_strlen($value) < $rule['min']) {
                $this->errors[$field] = $rule['message'] ?? "{$field}는 최소 {$rule['min']}자 이상이어야 합니다.";
            }

            // 최대 길이 체크
            if (!empty($rule['max']) && mb_strlen($value) > $rule['max']) {
                $this->errors[$field] = $rule['message'] ?? "{$field}는 최대 {$rule['max']}자까지 입력 가능합니다.";
            }

            // 이메일 형식 체크
            if (!empty($rule['email']) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $rule['message'] ?? "올바른 이메일 형식이 아닙니다.";
            }

            // 숫자 범위 체크
            if (isset($rule['min_value']) && $value < $rule['min_value']) {
                $this->errors[$field] = $rule['message'] ?? "{$field}는 최소 {$rule['min_value']} 이상이어야 합니다.";
            }
            if (isset($rule['max_value']) && $value > $rule['max_value']) {
                $this->errors[$field] = $rule['message'] ?? "{$field}는 최대 {$rule['max_value']} 이하여야 합니다.";
            }

            // 정규식 패턴 체크
            if (!empty($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $this->errors[$field] = $rule['message'] ?? "올바른 형식이 아닙니다.";
            }
        }

        return empty($this->errors);
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError()
    {
        return reset($this->errors);
    }
} 