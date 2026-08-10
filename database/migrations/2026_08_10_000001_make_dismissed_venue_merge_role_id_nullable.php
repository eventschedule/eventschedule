<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Venue-merge dismissals used to belong to one curator schedule. The account-wide merge page
 * ("Merge Duplicate Venues" reached from Following) has no schedule behind it, so those rows
 * carry role_id = NULL.
 *
 * Note the dvms_user_role_hash_unique index stops constraining NULL rows: MySQL treats NULLs as
 * distinct, so two dismissals of the same group could co-exist. That is harmless here - the read
 * path plucks the hashes and does an in_array(), and firstOrCreate() still finds an existing row
 * because a null value in where() compiles to whereNull - so a duplicate row is simply inert.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dismissed_venue_merge_suggestions', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Account-wide dismissals have no schedule to fall back on, so they cannot survive the
        // column becoming NOT NULL again.
        \DB::table('dismissed_venue_merge_suggestions')->whereNull('role_id')->delete();

        Schema::table('dismissed_venue_merge_suggestions', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable(false)->change();
        });
    }
};
