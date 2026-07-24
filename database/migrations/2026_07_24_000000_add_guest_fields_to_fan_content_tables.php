<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['event_comments', 'event_photos', 'event_videos'];

    /**
     * Fan content can be submitted without an account, so the submitter's name and email
     * are stored on the row itself. user_id stays null for those (already nullable via
     * 2026_08_01_000000_make_fan_content_user_id_nullable).
     */
    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('guest_name')->nullable()->after('user_id');
                $table->string('guest_email')->nullable()->after('guest_name');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['guest_name', 'guest_email']);
            });
        }
    }
};
