<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Utils\PlanPriceUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Why can a team member not see a schedule's ticket sales?
 *
 * Support question, asked often enough and answered wrongly often enough to be worth a command.
 * The Sales page, the check-in dashboard and the scan picker are all scoped by
 * Event::managedBy()/scannableBy(), whose non-owner branch is an EXISTS on event_role. The OWNER
 * reaches the same rows through events.user_id and is therefore immune to every fault inside that
 * EXISTS - which is why "the owner sees them, the admin does not" is the normal shape of this
 * report, and why guessing from the symptom alone has failed twice.
 *
 * Read-only. It NEVER re-implements the rule: every verdict comes from the real scope, and the raw
 * event_role read exists only to explain the verdict the scope already gave. A diagnostic that
 * derives the rule a second time can agree with itself while disagreeing with the app.
 */
class DiagnoseTeamAccess extends Command
{
    protected $signature = 'app:diagnose-team-access {email : The team member} {subdomain : The schedule}';

    protected $description = 'Explain why a team member can or cannot see a schedule\'s ticket sales';

    /** How many of the schedule's revenue-bearing events to explain individually. */
    private const EVENT_LIMIT = 20;

    public function handle(): int
    {
        $member = User::whereEmail($this->argument('email'))->first();

        if (! $member) {
            $this->error('No user with email '.$this->argument('email'));

            return self::FAILURE;
        }

        $role = Role::subdomain($this->argument('subdomain'))->first();

        if (! $role) {
            $this->error('No schedule with subdomain '.$this->argument('subdomain'));

            return self::FAILURE;
        }

        $this->membership($member, $role);
        $this->plan($role);
        $this->roleSets($member, $role);
        $this->events($member, $role);

        return self::SUCCESS;
    }

    private function membership(User $member, Role $role): void
    {
        $this->info('== Membership ==');

        $pivot = DB::table('role_user')
            ->where('user_id', $member->id)
            ->where('role_id', $role->id)
            ->first();

        $this->line('member: '.$member->email.' (users.id '.$member->id.')');
        $this->line('schedule: '.$role->subdomain.' (roles.id '.$role->id.', type '.$role->type.')');
        $this->line('role_user.level: '.($pivot->level ?? '<no pivot row>'));
        $this->line('roles.user_id: '.$role->user_id.($role->user_id == $member->id ? ' (this member owns it)' : ' (someone else owns it)'));
        $this->line('roles.is_deleted: '.var_export((bool) $role->is_deleted, true));

        if (! $pivot) {
            $this->warn('No role_user row at all - the member was never added to this schedule.');
        } elseif (! in_array($pivot->level, ['owner', 'admin'])) {
            $this->warn('Level "'.$pivot->level.'" is outside User::editor(), so the Sales page cannot list this schedule by design.');
            $this->warn('viewer is still inside User::member() and keeps the /scan picker.');
        }

        $this->newLine();
    }

    private function plan(Role $role): void
    {
        $this->info('== Plan ==');

        // The two branches of Role::isEnterprise(), reported separately: an admin-panel badge shows
        // actualPlanTier(), which agrees with isEnterprise() by construction, so a disagreement
        // here means the price config has drifted rather than that the customer downgraded.
        $subscription = $role->subscription('default');
        $enterpriseIds = PlanPriceUtils::enterpriseIds();

        if ($subscription) {
            $this->line('subscription: '.$subscription->stripe_status.', price '.($subscription->stripe_price ?: '<none on the subscription row>'));
            $this->line('price recognized as Enterprise: '.var_export(in_array($subscription->stripe_price, $enterpriseIds, true), true));

            if ($subscription->stripe_price && ! in_array($subscription->stripe_price, $enterpriseIds, true)) {
                $this->warn('Price is not in STRIPE_ENTERPRISE_PRICE_MONTHLY/_YEARLY. A subscriber left on a retired');
                $this->warn('Price object keeps being billed while Enterprise is withdrawn from them.');
            }
        } else {
            $this->line('subscription: <none>');
        }

        $this->line('hasActiveEnterpriseSubscription(): '.var_export($role->hasActiveEnterpriseSubscription(), true));
        $this->line('legacy plan_type/plan_expires: '.($role->plan_type ?: 'null').' / '.($role->plan_expires ?: 'null'));
        $this->line('isEnterprise(): '.var_export($role->isEnterprise(), true));
        $this->line('actualPlanTier(): '.$role->actualPlanTier());
        $this->newLine();
    }

    private function roleSets(User $member, Role $role): void
    {
        $this->info('== Role sets the ticketing pages use ==');

        $manageable = $member->manageableRoles();
        $scannable = $member->scannableRoles();

        $this->line('manageableRoles(): '.($manageable->isEmpty() ? '<empty>' : $manageable->pluck('subdomain')->implode(', ')));
        $this->line('scannableRoles():  '.($scannable->isEmpty() ? '<empty>' : $scannable->pluck('subdomain')->implode(', ')));
        $this->line('this schedule in manageableRoles(): '.var_export($manageable->contains('id', $role->id), true));

        if (! $manageable->contains('id', $role->id)) {
            $this->warn('The schedule was dropped before any event was considered, so EVERY ticketing list is empty.');
            $this->warn('On hosted that is User::planAllowsTeamAccess(): a schedule you do not own must be Enterprise.');

            return;
        }

        // scopeManagedBy() passes the non-curator subset as the curator-exempt list, so a curator
        // qualifies only for what it created - the is_accepted arm cannot fire for it at all.
        if ($role->isCurator()) {
            $this->warn('Schedule type is curator, so it is excluded from the curator-exempt list in');
            $this->warn('Event::scopeManagedBy(). Only events whose creator_role_id IS this schedule can match.');
        }

        $this->newLine();
    }

    private function events(User $member, Role $role): void
    {
        $this->info('== Events with sales ==');

        // Found by creator_role_id OR the pivot, never by the pivot alone: an event whose
        // event_role row went missing is exactly the case worth finding, and $role->events()
        // cannot see it.
        $events = Event::where(function ($q) use ($role) {
            $q->where('creator_role_id', $role->id)
                ->orWhereExists(fn ($sub) => $sub->selectRaw('1')
                    ->from('event_role')
                    ->whereColumn('event_role.event_id', 'events.id')
                    ->where('event_role.role_id', $role->id));
        })
            ->whereHas('sales')
            ->orderByDesc('starts_at')
            ->limit(self::EVENT_LIMIT)
            ->get();

        if ($events->isEmpty()) {
            $this->line('No events on this schedule carry sales. Nothing here can explain an empty page.');
            $this->newLine();

            return;
        }

        $rows = [];

        foreach ($events as $event) {
            $pivot = DB::table('event_role')
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            // The verdict comes from the REAL scope, so it cannot drift from the page.
            $visible = Event::managedBy($member)->whereKey($event->id)->exists();

            $rows[] = [
                $event->id,
                \Illuminate\Support\Str::limit($event->name, 24),
                $event->user_id,
                $event->creator_role_id ?: 'NULL',
                $pivot ? 'yes' : 'MISSING',
                $pivot ? var_export($pivot->is_accepted, true) : '-',
                $visible ? 'yes' : 'NO',
                $visible ? '' : $this->explain($event, $role, $pivot),
            ];
        }

        $this->table(
            ['event', 'name', 'user_id', 'creator_role_id', 'pivot', 'is_accepted', 'member sees', 'why not'],
            $rows
        );

        $this->salesCounts($member, $role, $events);
    }

    /**
     * Why the scope refused this event. Explanation only - the verdict above is the scope's.
     */
    private function explain(Event $event, Role $role, ?object $pivot): string
    {
        if (! $pivot) {
            return 'no event_role row (fatal on its own; nothing rescues it)';
        }

        $isCreator = $event->creator_role_id && (int) $event->creator_role_id === (int) $role->id;

        if ($isCreator) {
            return 'pivot and creator both look right - check manageableRoles() above';
        }

        $reason = $event->creator_role_id
            ? 'creator_role_id points at role '.$event->creator_role_id
            : 'creator_role_id is NULL';

        if ($role->isCurator()) {
            return $reason.' and this schedule is a curator, so the is_accepted arm cannot apply';
        }

        return $reason.', and is_accepted is '.var_export($pivot->is_accepted, true).' rather than true';
    }

    private function salesCounts(User $member, Role $role, $events): void
    {
        $this->newLine();
        $this->info('== Sales ==');

        $eventIds = $events->pluck('id');

        $total = Sale::whereIn('event_id', $eventIds)->where('is_deleted', false)->count();
        $visible = Sale::whereIn('event_id', $eventIds)
            ->where('is_deleted', false)
            ->whereHas('event', fn ($q) => $q->managedBy($member))
            ->count();

        $this->line('sales on those events: '.$total);
        $this->line('visible to this member: '.$visible);

        // The Sales page hides past events unless include_past=1, which hits owner and member
        // alike - worth ruling out before chasing a permissions fault.
        $upcoming = Sale::whereIn('event_id', $eventIds)
            ->where('is_deleted', false)
            ->where('event_date', '>=', now()->subDay()->startOfDay())
            ->count();

        $this->line('of those, inside the default (non-past) Sales window: '.$upcoming);

        if ($total > 0 && $upcoming === 0) {
            $this->warn('Every sale is on a past event, so /sales is empty for EVERYONE until');
            $this->warn('"Include past events" is toggled. That is not a permissions problem.');
        }

        $this->newLine();
    }
}
