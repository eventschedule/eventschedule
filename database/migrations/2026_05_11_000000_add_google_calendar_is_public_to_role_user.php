<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->boolean('google_calendar_is_public')->nullable()->after('google_calendar_id');
        });

        // Backfill the flag for existing schedules with Google Calendar sync so
        // the GP /calendar icon appears immediately after deploy (it fails closed
        // on null/false). Wrapped so a Google API outage can't block schema work.
        try {
            \Artisan::call('google:refresh-public-status');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function down(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropColumn('google_calendar_is_public');
        });
    }
};
