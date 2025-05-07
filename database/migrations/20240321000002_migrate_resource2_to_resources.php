<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // resource2의 데이터를 resources로 마이그레이션
        $resource2Data = DB::table('resource2')->get();
        
        foreach ($resource2Data as $data) {
            DB::table('resources')->insert([
                'user_id' => 1, // 기본 사용자 ID 설정
                'title' => $data->title,
                'slug' => \Illuminate\Support\Str::slug($data->title),
                'content' => $data->content ?? $data->description,
                'description' => $data->description,
                'visibility' => 'public',
                'status' => 'published',
                'published_at' => $data->created_at,
                'view_count' => 0,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at
            ]);
        }
    }

    public function down()
    {
        // 마이그레이션 롤백 시 resource2에서 가져온 데이터 삭제
        // slug를 기준으로 삭제
        $resource2Data = DB::table('resource2')->get();
        foreach ($resource2Data as $data) {
            DB::table('resources')
                ->where('slug', \Illuminate\Support\Str::slug($data->title))
                ->delete();
        }
    }
}; 