<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            // Drop existing foreign key, make role_id nullable, re-add FK
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable()->change();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();

            $table->string('type', 20)->default('schedule')->after('role_id');
            $table->index(['type', 'status']);
        });

        Schema::table('newsletter_segments', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable()->change();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::table('newsletter_ab_tests', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable()->change();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('admin_newsletter_unsubscribed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropColumn('type');
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::table('newsletter_segments', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::table('newsletter_ab_tests', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_newsletter_unsubscribed_at');
        });
    }
};
