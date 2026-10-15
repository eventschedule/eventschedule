<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The team member a type is booked with (AppointmentHostService).
     *
     * host_user_id names an owner or admin of the type's schedule. Their other appointments, on
     * any schedule, and their days away on this one are what block the type's times; the
     * schedule's own events do not. Plain index, no foreign key, the same choice
     * events.appointment_type_id made: a host is released by the app (releaseHost()) before the
     * account or the membership goes, and that is what turns the type off and tells the owner.
     *
     * host_released_at is when that happened, so the types list can say "host left the team"
     * rather than a bare "inactive". Cleared when the owner chooses a host again.
     *
     * Neither column is fillable, and BackupService leaves both out: a user id from this install
     * means nobody, or somebody else, on the install a backup is restored to.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('appointment_types', 'host_user_id')) {
            Schema::table('appointment_types', function (Blueprint $table) {
                $table->unsignedBigInteger('host_user_id')->nullable();
                $table->index('host_user_id');
            });
        }

        if (! Schema::hasColumn('appointment_types', 'host_released_at')) {
            Schema::table('appointment_types', function (Blueprint $table) {
                $table->timestamp('host_released_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointment_types', 'host_released_at')) {
            Schema::table('appointment_types', function (Blueprint $table) {
                $table->dropColumn('host_released_at');
            });
        }

        if (Schema::hasColumn('appointment_types', 'host_user_id')) {
            Schema::table('appointment_types', function (Blueprint $table) {
                $table->dropIndex(['host_user_id']);
                $table->dropColumn('host_user_id');
            });
        }
    }
};
