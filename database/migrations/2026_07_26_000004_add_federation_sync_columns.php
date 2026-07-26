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
        Schema::table('federated_events', function (Blueprint $table) {
            // Mark-and-sweep token for reconcile. A manifest larger than one request is
            // sent in chunks; every chunk stamps the rows it names with the same
            // per-run token, and the final call deletes whatever still carries an old
            // one. Without this the final chunk would delete every row the earlier
            // chunks carried.
            $table->string('manifest_token', 64)->nullable();
            $table->index('manifest_token');
        });

        Schema::table('events', function (Blueprint $table) {
            // Hash of the resolved occurrence set last sent, so a recurring event is
            // only re-pushed when its dates actually change. Lives on the row it
            // describes rather than in a shared blob: it stays O(1) to read, cannot
            // leak entries for deleted events, and needs no cache invalidation.
            // Instance-local sync state, like federated_at - never exported.
            $table->string('federated_hash', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('federated_events', function (Blueprint $table) {
            $table->dropIndex(['manifest_token']);
            $table->dropColumn('manifest_token');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('federated_hash');
        });
    }
};
