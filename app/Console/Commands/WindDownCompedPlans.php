<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\AuditService;
use App\Services\DemoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Ends the admin-granted ("comped") plans, which are the bulk of the apparent paid base.
 *
 * Most schedules on a non-free plan never bought it: an admin granted it and it has renewed
 * ever since on a far-future plan_expires. That hides the real subscription business and makes
 * the referral reward worthless (a free month of Pro means nothing to someone already on a
 * permanent free Pro).
 *
 * Two segments, deliberately treated differently:
 *   - ADDRESSABLE - the schedule is getting real value (has sold a paid ticket, or has an
 *     audience). Gets a dated trial so there is a clear runway and a reason to convert.
 *   - DORMANT - no events and no followers. Gets a short grace and is left to lapse quietly.
 *     Chasing these costs goodwill and yields nothing.
 *
 * Safety rules, all load-bearing:
 *   - Dry run unless --apply is passed.
 *   - NEVER extends a plan. A role whose plan already expires sooner than the new date is
 *     skipped, so re-running can only ever bring dates forward.
 *   - Skips any role that has ever held a Stripe subscription row, and any plan_source other
 *     than 'admin' (notably 'referral', which someone earned).
 *   - Idempotent: a role that already carries a trial_ends_at has been processed.
 *
 * Note this moves plan_expires as well as trial_ends_at. Role::isPro() falls back to
 * `plan_expires >= today && plan_type == 'pro'`, so setting only the trial date would change
 * nothing at all.
 */
class WindDownCompedPlans extends Command
{
    protected $signature = 'app:wind-down-comped-plans
        {--apply : Write the changes. Without this the command only reports.}
        {--trial-days=90 : Runway for schedules that are getting real value.}
        {--lapse-days=30 : Grace for dormant schedules before the plan simply ends.}
        {--min-views=100 : Views in the last 90 days that count as having an audience.}
        {--min-followers=5 : Followers that count as having an audience.}';

    protected $description = 'Put admin-granted plans on a dated trial (or let dormant ones lapse)';

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return 0;
        }

        $apply = (bool) $this->option('apply');
        $trialDays = max(0, (int) $this->option('trial-days'));
        $lapseDays = max(0, (int) $this->option('lapse-days'));
        $minViews = max(0, (int) $this->option('min-views'));
        $minFollowers = max(0, (int) $this->option('min-followers'));

        $roles = $this->compedRoles();

        if ($roles->isEmpty()) {
            $this->info('No admin-granted plans to wind down.');

            return 0;
        }

        $signals = $this->valueSignals($roles->pluck('id')->all());

        $trialEnds = Carbon::now()->addDays($trialDays);
        $lapseEnds = Carbon::now()->addDays($lapseDays);

        $counts = ['addressable' => 0, 'dormant' => 0, 'skipped_expires_sooner' => 0, 'skipped_already_done' => 0];

        foreach ($roles as $role) {
            $signal = $signals[$role->id] ?? null;

            if ($role->trial_ends_at !== null) {
                $counts['skipped_already_done']++;

                continue;
            }

            $addressable = $signal && (
                $signal->paid_tickets > 0
                || $signal->views_90d >= $minViews
                || $signal->followers >= $minFollowers
            );

            $target = $addressable ? $trialEnds : $lapseEnds;

            // Never extend. A plan already ending sooner than the target keeps its own date.
            if ($role->plan_expires !== null && $role->plan_expires < $target->format('Y-m-d')) {
                $counts['skipped_expires_sooner']++;

                continue;
            }

            $counts[$addressable ? 'addressable' : 'dormant']++;

            if (! $apply) {
                continue;
            }

            $old = ['plan_expires' => $role->plan_expires, 'trial_ends_at' => $role->trial_ends_at];

            // Direct assignment, not update(): none of the plan columns are mass-assignable.
            $role->plan_expires = $target->format('Y-m-d');
            if ($addressable) {
                $role->trial_ends_at = $target;
            }
            $role->save();

            AuditService::log(
                AuditService::ADMIN_PLAN_UPDATE,
                null,
                'Role',
                $role->id,
                $old,
                ['plan_expires' => $role->plan_expires, 'trial_ends_at' => $role->trial_ends_at],
                'wind-down-comped-plans: '.($addressable ? 'addressable' : 'dormant'),
            );
        }

        $this->report($roles->count(), $counts, $trialEnds, $lapseEnds, $apply);

        return 0;
    }

    /**
     * Admin-granted, still live, and never a Stripe customer.
     *
     * The subscriptions check is defensive rather than redundant: a schedule that was comped
     * and later subscribed properly must not be dragged back onto a trial.
     */
    private function compedRoles()
    {
        return Role::query()
            ->where('plan_source', 'admin')
            ->where('plan_type', '!=', 'free')
            ->where('is_deleted', false)
            ->whereNotNull('user_id')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.role_id', 'roles.id');
            })
            // A redeemed referral stacks 30 days onto whatever plan the schedule already had,
            // and ReferralController sets plan_source with ??= - deliberately, because
            // overwriting 'admin' would strip the comped credit chip from an admin grant. So a
            // role that earned a month still reads plan_source = 'admin', and without this the
            // wind-down would pull plan_expires back past the month somebody actually earned.
            // Same shape as the subscriptions guard above: the docblock's promise to leave
            // earned plans alone has to be enforced on the referral row, not on plan_source.
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('referrals')
                    ->whereColumn('referrals.credited_role_id', 'roles.id')
                    ->where('referrals.status', 'credited');
            })
            ->get(['id', 'subdomain', 'plan_type', 'plan_expires', 'trial_ends_at']);
    }

    /**
     * Paid tickets, 90-day views and followers per role, in three set-based queries rather
     * than three per role.
     */
    private function valueSignals(array $roleIds)
    {
        $cutoff = now()->copy()->subDays(90)->toDateString();

        $paid = DB::table('sales')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->join('event_role', 'event_role.event_id', '=', 'events.id')
            ->whereIn('event_role.role_id', $roleIds)
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
            ->selectRaw('event_role.role_id, COUNT(*) as c')
            ->groupBy('event_role.role_id')
            ->pluck('c', 'role_id');

        $views = DB::table('analytics_daily')
            ->whereIn('role_id', $roleIds)
            ->where('date', '>=', $cutoff)
            ->selectRaw('role_id, SUM(desktop_views + mobile_views + tablet_views + unknown_views) as v')
            ->groupBy('role_id')
            ->pluck('v', 'role_id');

        $followers = DB::table('role_user')
            ->whereIn('role_id', $roleIds)
            ->where('level', 'follower')
            ->selectRaw('role_id, COUNT(*) as c')
            ->groupBy('role_id')
            ->pluck('c', 'role_id');

        $out = [];
        foreach ($roleIds as $id) {
            $out[$id] = (object) [
                'paid_tickets' => (int) ($paid[$id] ?? 0),
                'views_90d' => (int) ($views[$id] ?? 0),
                'followers' => (int) ($followers[$id] ?? 0),
            ];
        }

        return $out;
    }

    private function report(int $total, array $counts, Carbon $trialEnds, Carbon $lapseEnds, bool $apply): void
    {
        $this->newLine();
        $this->info($apply ? 'Applied.' : 'DRY RUN - nothing was written. Re-run with --apply.');
        $this->newLine();

        $this->table(['segment', 'schedules', 'outcome'], [
            ['addressable', $counts['addressable'], 'trial + plan ends '.$trialEnds->format('Y-m-d')],
            ['dormant', $counts['dormant'], 'plan ends '.$lapseEnds->format('Y-m-d')],
            ['skipped (expires sooner)', $counts['skipped_expires_sooner'], 'left alone - never extend'],
            ['skipped (already wound down)', $counts['skipped_already_done'], 'has a trial_ends_at'],
            ['TOTAL comped', $total, ''],
        ]);
    }
}
