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
    }

    /**
     * 문자열을 암호화합니다.
     *
     * @param string $data 암호화할 데이터
     * @return string 암호화된 데이터
     */
    public function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * 암호화된 문자열을 복호화합니다.
     *
     * @param string $data 복호화할 데이터
     * @return string|false 복호화된 데이터 또는 실패 시 false
     */
    public function decrypt($data)
    {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
} 