<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Sync watermark: null means "needs pushing". A nullable timestamp rather
            // than a boolean because EventRepo::saveEvent() coerces NOT NULL booleans
            // through has()/boolean(), which this sidesteps entirely.
            // Instance-local sync state - deliberately not exported by BackupService.
            $table->timestamp('federated_at')->nullable();
        });

        Schema::table('roles', function (Blueprint $table) {
            // Per-schedule opt-out. Defaults on, but only ever consulted when the
            // system-level Setting('federation_enabled') is also on, so enabling it
            // stays the operator's decision.
            $table->boolean('federation_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('federated_at');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('federation_enabled');
        });
    }
};
