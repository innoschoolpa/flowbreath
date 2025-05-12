<?php

namespace App\Database\Seeds;

use App\Database\Seed;

class UserSeeder extends Seed
{
    public function run()
    {
        // 관리자 계정 생성
        $adminId = $this->insert('users', [
            'email' => 'admin@flowbreath.com',
            'password' => password_hash('admin123!@#', PASSWORD_DEFAULT),
            'name' => '관리자'
        ]);

        // 테스트 사용자 계정 생성
        $userId = $this->insert('users', [
            'email' => 'user@flowbreath.com',
            'password' => password_hash('user123!@#', PASSWORD_DEFAULT),
            'name' => '테스트 사용자'
        ]);

        echo "기본 사용자 계정이 생성되었습니다.\n";
        echo "관리자 - admin@flowbreath.com / admin123!@#\n";
        echo "사용자 - user@flowbreath.com / user123!@#\n";
    }
} 