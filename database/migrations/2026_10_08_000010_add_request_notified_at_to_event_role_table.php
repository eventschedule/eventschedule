<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When a pending request was told to the schedule's owners in an email that spells it out
     * (RequestNotifier). Null means not yet, which is how a mail knows which requests are new:
     * the count it used to carry (roles.last_notified_request_count) cannot say WHICH ones.
     *
     * On the request's own row, not on roles: roles is at MySQL's row size limit.
     *
     * What is waiting today is marked as told, or the first mail after the upgrade would spell
     * out every pending request on the install as though it had just arrived. One exception: on
     * a schedule with more waiting than its last mail counted, a request from the last day was
     * very likely never announced (it arrived within a quarter of an hour of another, and the
     * noon summary has not run since). Those are left unmarked, and the next mail spells them
     * out. Older ones on such a schedule are marked: the count is also reset whenever the owner
     * answers a request, so "more than counted" alone would have re-announced a schedule's five
     * oldest requests to an owner who answered one yesterday.
     *
     * Deliberately no ->after(): several event_role columns live in migrations dated later than
     * the ones around them, so anchoring to one breaks a fresh migrate. hasColumn: a failure in
     * the data step must not leave a re-run stopped at "duplicate column".
     */
    public function up(): void
    {
        if (! Schema::hasColumn('event_role', 'request_notified_at')) {
            Schema::table('event_role', function (Blueprint $table) {
                $table->timestamp('request_notified_at')->nullable();
            });
        }

        DB::table('event_role')->whereNull('is_accepted')->update(['request_notified_at' => now()]);

        $due = DB::table('event_role')
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->whereNull('event_role.is_accepted')
            ->where('roles.accept_requests', true)
            ->where('roles.require_approval', true)
            ->groupBy('event_role.role_id', 'roles.last_notified_request_count')
            ->havingRaw('COUNT(*) > COALESCE(roles.last_notified_request_count, 0)')
            ->pluck('event_role.role_id');

        foreach ($due->chunk(1000) as $roleIds) {
            $recent = DB::table('event_role')
                ->join('events', 'events.id', '=', 'event_role.event_id')
                ->whereNull('event_role.is_accepted')
                ->whereIn('event_role.role_id', $roleIds->all())
                ->where('events.created_at', '>=', now()->subDay())
                ->pluck('event_role.id');

            foreach ($recent->chunk(1000) as $ids) {
                DB::table('event_role')->whereIn('id', $ids->all())->update(['request_notified_at' => null]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('request_notified_at');
        });
    }
};
