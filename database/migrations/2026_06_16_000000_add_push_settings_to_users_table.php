<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Account-scoped push notification preferences, e.g.
            // {"enabled": true, "opted_in_at": "2026-06-16T00:00:00Z"}.
            // Per-schedule push routing reuses role_user.notification_settings.
            $table->json('push_settings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('push_settings');
        });
    }
};
