<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `ticket_reminder_settings.enabled` becomes nullable, NULL meaning "the owner has not chosen":
 * the schedule then follows Role::optionalMailOnByDefault() (2026-11-12). On the hosted install,
 * optional mail that would go out through the platform's own mail server is OFF by default unless
 * the schedule pays, and that answer changes with the plan, so it cannot be a stored value.
 *
 * Rows already stored keep their value: they were written by a selfhost install (v1.0.141), where
 * the default is on, so nothing changes for them. A small table, so the ALTER is quick.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_reminder_settings', function (Blueprint $table) {
            $table->boolean('enabled')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('ticket_reminder_settings')->whereNull('enabled')->update(['enabled' => true]);

        Schema::table('ticket_reminder_settings', function (Blueprint $table) {
            $table->boolean('enabled')->nullable(false)->default(true)->change();
        });
    }
};
