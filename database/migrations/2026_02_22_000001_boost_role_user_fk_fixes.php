<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change boost_campaigns.role_id from cascadeOnDelete to nullOnDelete
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->foreignId('role_id')->nullable()->change();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        // Change boost_campaigns.user_id from cascadeOnDelete to nullOnDelete
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->foreignId('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }
};
