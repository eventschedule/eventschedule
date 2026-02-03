<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedSmallInteger('translation_attempts')->default(0);
            $table->timestamp('last_translated_at')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedSmallInteger('translation_attempts')->default(0);
            $table->timestamp('last_translated_at')->nullable();
        });

        Schema::table('event_parts', function (Blueprint $table) {
            $table->unsignedSmallInteger('translation_attempts')->default(0);
            $table->timestamp('last_translated_at')->nullable();
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->unsignedSmallInteger('translation_attempts')->default(0);
            $table->timestamp('last_translated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['translation_attempts', 'last_translated_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['translation_attempts', 'last_translated_at']);
        });

        Schema::table('event_parts', function (Blueprint $table) {
            $table->dropColumn(['translation_attempts', 'last_translated_at']);
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn(['translation_attempts', 'last_translated_at']);
        });
    }
};
