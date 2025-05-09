<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogleFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique();
            $table->string('profile_picture')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('auth_provider')->default('local');
            
            $table->index('google_id');
            $table->index('auth_provider');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'profile_picture', 'email_verified', 'auth_provider']);
        });
    }
} 