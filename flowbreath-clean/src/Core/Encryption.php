<?php

namespace App\Core;

class Encryption
{
    private $key;
    private $cipher = 'aes-256-cbc';

    public function __construct()
    {
        // 환경 변수나 설정 파일에서 암호화 키를 가져옵니다
        $this->key = getenv('ENCRYPTION_KEY') ?: 'your-secret-key-here';
        
        // 키가 32바이트(256비트)가 되도록 조정
        $this->key = hash('sha256', $this->key, true);
        
        error_log("Encryption initialized with key length: " . strlen($this->key));
    }

    /**
     * 문자열을 암호화합니다.
     *
     * @param string $data 암호화할 데이터
     * @return string 암호화된 데이터
     */
    public function encrypt($data)
    {
        try {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
            $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
            if ($encrypted === false) {
                error_log("Encryption failed: " . openssl_error_string());
                return false;
            }
            return base64_encode($iv . $encrypted);
        } catch (\Exception $e) {
            error_log("Encryption error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 암호화된 문자열을 복호화합니다.
     *
     * @param string $data 복호화할 데이터
     * @return string|false 복호화된 데이터 또는 실패 시 false
     */
    public function decrypt($data)
    {
        try {
            error_log("Attempting to decrypt data: " . substr($data, 0, 20) . "...");
            
            // Base64 디코딩
            $decoded = base64_decode($data);
            if ($decoded === false) {
                error_log("Base64 decode failed");
                return false;
            }
            
            // IV 길이 확인
            $ivLength = openssl_cipher_iv_length($this->cipher);
            if (strlen($decoded) <= $ivLength) {
                error_log("Decoded data too short");
                return false;
            }
            
            // IV와 암호화된 데이터 분리
            $iv = substr($decoded, 0, $ivLength);
            $encrypted = substr($decoded, $ivLength);
            
            // 복호화 시도
            $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
            if ($decrypted === false) {
                error_log("Decryption failed: " . openssl_error_string());
                return false;
            }
            
            error_log("Successfully decrypted data");
            return $decrypted;
        } catch (\Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return false;
        }
    }
} 