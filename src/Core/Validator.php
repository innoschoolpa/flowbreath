<?php

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function validate(array $rules): bool
    {
        $this->errors = [];
        $this->data = $_POST;

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;

            // Required check
            if (($fieldRules['required'] ?? false) && empty($value)) {
                $this->errors[$field] = $fieldRules['message'] ?? "The {$field} field is required.";
                continue;
            }

            // Skip other validations if the field is empty and not required
            if (empty($value) && !($fieldRules['required'] ?? false)) {
                continue;
            }

            // Min length check
            if (isset($fieldRules['min']) && strlen($value) < $fieldRules['min']) {
                $this->errors[$field] = $fieldRules['message'] ?? "The {$field} must be at least {$fieldRules['min']} characters.";
            }

            // Max length check
            if (isset($fieldRules['max']) && strlen($value) > $fieldRules['max']) {
                $this->errors[$field] = $fieldRules['message'] ?? "The {$field} must not exceed {$fieldRules['max']} characters.";
            }

            // Email check
            if (($fieldRules['email'] ?? false) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $fieldRules['message'] ?? "The {$field} must be a valid email address.";
            }

            // Pattern check
            if (isset($fieldRules['pattern']) && !preg_match($fieldRules['pattern'], $value)) {
                $this->errors[$field] = $fieldRules['message'] ?? "The {$field} format is invalid.";
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return reset($this->errors) ?: null;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
} 