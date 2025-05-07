<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('description')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('private');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // 인덱스
            $table->index(['user_id', 'status']);
            $table->index(['visibility', 'status']);
            $table->index('published_at');
        });

        // 리소스-태그 중간 테이블
        Schema::create('resource_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // 복합 유니크 인덱스
            $table->unique(['resource_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resource_tags');
        Schema::dropIfExists('resources');
    }
}; 