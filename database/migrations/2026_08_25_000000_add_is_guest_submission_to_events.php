<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks an event whose user_id is a stand-in rather than a real submitter.
     *
     * EventRepo::saveEvent() falls back to the receiving schedule's owner when there is no
     * authenticated user, because events.user_id is NOT NULL. That owner never asked anyone
     * for anything, so EventController::decline() must not mail them "your event request at
     * X has been declined". The isMember() check there only catches it when the declining
     * schedule is the one submitted to.
     *
     * Deliberately no ->after(): several events columns live in migrations dated later than
     * this one, so anchoring to them breaks a fresh migrate.
     *
     * Forward-only, with no backfill. Rows created before this migration keep false and still
     * mail the stand-in owner. Nothing recorded whether a historical row came from an anonymous
     * submission - user_id looks identical either way - so there is no signal to backfill from,
     * and guessing would mislabel real submissions and silence mail somebody should receive.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_guest_submission')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_guest_submission');
        });
    }
};
