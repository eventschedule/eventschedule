<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // How to handle an event deleted in a connected calendar (Google / Microsoft):
            // 'ignore' (keep it here), 'cancel' (hide via is_cancelled), or 'delete' (remove).
            // Defaults to 'ignore' so existing schedules see no behavior change on upgrade.
            $table->string('calendar_delete_action', 20)->nullable()->default('ignore');

            // Google Calendar incremental-sync cursor (nextSyncToken). Text: can exceed 255 chars.
            // Transient + calendar-specific, so it is intentionally excluded from backups.
            $table->text('google_sync_token')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['calendar_delete_action', 'google_sync_token']);
        });
    }
};
