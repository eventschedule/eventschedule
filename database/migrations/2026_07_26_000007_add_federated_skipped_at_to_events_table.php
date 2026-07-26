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
            // "The network will not take this one." Distinct from federated_at, which
            // means "delivered".
            //
            // Without the distinction the two states are conflated and the sync loops
            // forever: push stamps federated_at for an event it could not build or that
            // the nexus rejected, reconcile then reports that id as missing upstream,
            // the watermark is cleared, and the same event is retried every hour for
            // the life of the install - crowding out real ones through the per-run
            // budget.
            //
            // Cleared alongside federated_at whenever the event changes, so fixing the
            // cause (adding a flyer, renaming) makes it retry.
            $table->timestamp('federated_skipped_at')->nullable();
            $table->index('federated_skipped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['federated_skipped_at']);
            $table->dropColumn('federated_skipped_at');
        });
    }
};
