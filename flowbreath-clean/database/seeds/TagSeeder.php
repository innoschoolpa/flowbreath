<?php

namespace App\Database\Seeds;

use App\Database\Seed;

class TagSeeder extends Seed
{
    public function run()
    {
        // 기본 태그 생성
        $tags = [
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'HTML', 'slug' => 'html'],
            ['name' => 'CSS', 'slug' => 'css'],
            ['name' => 'Database', 'slug' => 'database'],
            ['name' => 'MySQL', 'slug' => 'mysql'],
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'Node.js', 'slug' => 'nodejs']
        ];

        foreach ($tags as $tag) {
            $this->insert('tags', $tag);
        }

        echo "기본 태그가 생성되었습니다.\n";
    }
} 