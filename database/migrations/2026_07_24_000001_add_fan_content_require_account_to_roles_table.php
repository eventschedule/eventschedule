<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-schedule switch for fan content: false (the default) lets visitors submit with just
     * a name and email, true requires them to sign in first. Mirrors roles.require_account,
     * which does the same job for event submissions.
     */
    public function up(): void
    {
        // No ->after() anchor: the fan_*_enabled columns are created by a later-dated
        // migration (2026_07_25_000000_split_fan_content_enabled_columns), so anchoring
        // to one would fail on a fresh migrate.
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('fan_content_require_account')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('fan_content_require_account');
        });
    }
};
