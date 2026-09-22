<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When this account accepted the terms of service and privacy policy.
 *
 * Consent was being CHECKED and then thrown away: RegisteredUserController::store() validates
 * `terms => accepted` on hosted, and nothing wrote the answer anywhere - so the app could not say
 * who had agreed to what, or when. The Google path did not even ask, and it is roughly half of all
 * accounts.
 *
 * Deliberately nullable with no backfill. Every account created before this migration genuinely
 * has no recorded consent, and stamping them all with today's date would invent a fact. A null
 * here means "not recorded", which is the truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('terms_accepted_at');
        });
    }
};
