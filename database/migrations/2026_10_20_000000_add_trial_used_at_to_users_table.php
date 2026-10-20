<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When this person first had a plan's free trial, or paid for a plan. One trial per
            // login (RoleBillable::isEligibleForTrial()): it was once per schedule, and stacked
            // on the card-free selling trial, so a second schedule or a just-ended selling trial
            // gave another week. On users because deleting a schedule removes its row, and
            // because roles is at its row limit. Not fillable.
            $table->timestamp('trial_used_at')->nullable();
        });

        // Everyone who already had one: the owner of any schedule with a subscription that
        // started (a trial or a payment; not a declined card or an abandoned 3DS step). The
        // selling trial is read from ticket_trial_used_at directly. Deliberately not a comped
        // plan (plan_expires, or the wind-down's trial_ends_at): an operator gave that, the owner
        // never started a trial, and it still rules out a trial on its own schedule.
        DB::table('users')
            ->whereNull('trial_used_at')
            ->whereIn('id', DB::table('roles')
                ->select('roles.user_id')
                ->whereNotNull('roles.user_id')
                ->whereExists(fn ($q) => $q->select(DB::raw(1))
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.role_id', 'roles.id')
                    ->whereNotIn('subscriptions.stripe_status', ['incomplete', 'incomplete_expired'])))
            ->update(['trial_used_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('trial_used_at');
        });
    }
};
