<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds google_oauth_id to separate OAuth identity from Calendar identity.
     * Previously, google_id was used for both purposes, causing collisions when
     * a user links a Google Calendar from a different account than their OAuth account.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_oauth_id')->nullable()->after('google_id');
            $table->index('google_oauth_id');
        });

        // Migrate existing data: copy google_id to google_oauth_id for OAuth users
        // OAuth users are those with google_id who either:
        // - Have no password (signed up via Google)
        // - Have no calendar tokens (haven't linked a different calendar account)
        DB::table('users')
            ->whereNotNull('google_id')
            ->where(function ($query) {
                $query->whereNull('password')
                    ->orWhereNull('google_token');
            })
            ->update(['google_oauth_id' => DB::raw('google_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_oauth_id']);
            $table->dropColumn('google_oauth_id');
        });
    }
};
