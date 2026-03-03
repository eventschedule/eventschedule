<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('fan_comments_enabled')->default(true)->after('fan_content_enabled');
            $table->boolean('fan_photos_enabled')->default(true)->after('fan_comments_enabled');
            $table->boolean('fan_videos_enabled')->default(true)->after('fan_photos_enabled');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('fan_comments_enabled')->nullable()->after('fan_content_enabled');
            $table->boolean('fan_photos_enabled')->nullable()->after('fan_comments_enabled');
            $table->boolean('fan_videos_enabled')->nullable()->after('fan_photos_enabled');
        });

        // Migrate existing data
        DB::statement('UPDATE roles SET fan_comments_enabled = fan_content_enabled, fan_photos_enabled = fan_content_enabled, fan_videos_enabled = fan_content_enabled');
        DB::statement('UPDATE events SET fan_comments_enabled = fan_content_enabled, fan_photos_enabled = fan_content_enabled, fan_videos_enabled = fan_content_enabled WHERE fan_content_enabled IS NOT NULL');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('fan_content_enabled');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('fan_content_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('fan_content_enabled')->default(true)->after('feedback_delay_hours');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('fan_content_enabled')->nullable()->after('feedback_enabled');
        });

        DB::statement('UPDATE roles SET fan_content_enabled = (fan_comments_enabled AND fan_photos_enabled AND fan_videos_enabled)');
        DB::statement('UPDATE events SET fan_content_enabled = fan_comments_enabled WHERE fan_comments_enabled IS NOT NULL');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['fan_comments_enabled', 'fan_photos_enabled', 'fan_videos_enabled']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['fan_comments_enabled', 'fan_photos_enabled', 'fan_videos_enabled']);
        });
    }
};
