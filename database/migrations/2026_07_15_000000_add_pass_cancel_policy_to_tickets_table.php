<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Deadline for cancelling an advance booking with the visit credited
            // back to the pass, in hours before the occurrence starts. Null =
            // no deadline (cancel any time, always credited); 0 = credited
            // until the occurrence starts.
            $table->unsignedInteger('pass_cancel_cutoff_hours')->nullable()->after('pass_seats_per_occurrence');
            // What happens to a cancellation attempted after the deadline:
            // 'forfeit' = the booking may still be cancelled but the visit is
            // not credited back (the seat returns to the pool); 'block' = the
            // booking can no longer be cancelled at all.
            $table->string('pass_late_cancel_policy', 10)->default('forfeit')->after('pass_cancel_cutoff_hours');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['pass_cancel_cutoff_hours', 'pass_late_cancel_policy']);
        });
    }
};
