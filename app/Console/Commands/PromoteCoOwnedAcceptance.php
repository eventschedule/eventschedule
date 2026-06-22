<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteCoOwnedAcceptance extends Command
{
    protected $signature = 'app:promote-co-owned-acceptance
        {--curator= : Limit to a single curator schedule by subdomain}
        {--dry-run : Show what would change without writing}';

    protected $description = 'Accept curator events on the curator owner\'s own venue/talent schedules. When one user owns both a curator and the venue an event sits at, the venue-side event_role.is_accepted is left false/null by importers, so the event hides on the venue\'s own page. This promotes those links to accepted.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $curatorOption = $this->option('curator');

        $curators = Role::query()
            ->where('type', 'curator')
            ->where('is_deleted', false)
            ->when($curatorOption, fn ($q) => $q->where('subdomain', $curatorOption))
            ->get(['id', 'subdomain', 'name']);

        if ($curators->isEmpty()) {
            $this->warn($curatorOption
                ? "No curator schedule found for subdomain '{$curatorOption}'."
                : 'No curator schedules found.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made.');
        }

        $totalPromoted = 0;

        foreach ($curators as $curator) {
            // Owner/admin users of the curator.
            $ownerUserIds = DB::table('role_user')
                ->where('role_id', $curator->id)
                ->whereIn('level', ['owner', 'admin'])
                ->pluck('user_id')
                ->all();

            if (empty($ownerUserIds)) {
                continue;
            }

            // Venue/talent schedules co-owned by one of the curator's owners. These are the
            // schedules whose own pages should mirror what the owner accepted on the curator.
            $coOwnedRoleIds = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->whereIn('role_user.user_id', $ownerUserIds)
                ->whereIn('role_user.level', ['owner', 'admin'])
                ->whereIn('roles.type', ['venue', 'talent'])
                ->where('roles.id', '!=', $curator->id)
                ->distinct()
                ->pluck('roles.id')
                ->all();

            if (empty($coOwnedRoleIds)) {
                continue;
            }

            // Events on a co-owned schedule that aren't accepted there yet...
            $pendingEventIds = DB::table('event_role')
                ->whereIn('role_id', $coOwnedRoleIds)
                ->where(fn ($q) => $q->whereNull('is_accepted')->orWhere('is_accepted', false))
                ->pluck('event_id')
                ->unique();

            if ($pendingEventIds->isEmpty()) {
                continue;
            }

            // ...that this curator has accepted. Materialised (no event_role subquery inside the
            // UPDATE) to avoid MySQL error 1093 (can't update a table referenced by a subquery in
            // its own WHERE).
            $acceptedEventIds = DB::table('event_role')
                ->where('role_id', $curator->id)
                ->where('is_accepted', true)
                ->whereIn('event_id', $pendingEventIds)
                ->pluck('event_id');

            if ($acceptedEventIds->isEmpty()) {
                continue;
            }

            $promote = DB::table('event_role')
                ->whereIn('role_id', $coOwnedRoleIds)
                ->whereIn('event_id', $acceptedEventIds)
                ->where(fn ($q) => $q->whereNull('is_accepted')->orWhere('is_accepted', false));

            $count = $dryRun ? $promote->count() : $promote->update(['is_accepted' => true]);

            if ($count === 0) {
                continue;
            }

            $totalPromoted += $count;
            $this->line($dryRun
                ? "  {$curator->subdomain}: would accept {$count} event link(s) on co-owned schedules."
                : "  {$curator->subdomain}: accepted {$count} event link(s) on co-owned schedules.");
        }

        $verb = $dryRun ? 'Would promote' : 'Promoted';
        $this->info("{$verb} {$totalPromoted} event link(s) total.");

        return self::SUCCESS;
    }
}
