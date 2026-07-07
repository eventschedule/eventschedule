<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First-touch onboarding-funnel milestones: when the user first reached
            // the new-schedule form and the add-event form. Stamped once (see
            // RoleController::create / EventController::create), never overwritten.
            $table->timestamp('schedule_form_viewed_at')->nullable();
            $table->timestamp('event_form_viewed_at')->nullable();

            // The funnel adds several verified-users + created_at range/GROUP BY scans;
            // users.created_at was previously unindexed. Composite matches the ubiquitous
            // "email_verified_at is not null AND created_at between ..." filter.
            $table->index(['email_verified_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_verified_at', 'created_at']);
            $table->dropColumn(['schedule_form_viewed_at', 'event_form_viewed_at']);
        });
    }
};
