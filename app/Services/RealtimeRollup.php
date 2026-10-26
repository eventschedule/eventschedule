<?php

namespace App\Services;

use App\Models\Role;
use App\Utils\RealtimeRows;
use App\Utils\RealtimeTracker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Counts realtime_hits into realtime_hourly before they are pruned, for the Trends card of
 * /admin/realtime (RealtimeTrends), and into realtime_breakdowns, for the longer ranges of its Top
 * pages, Sources and Countries cards (RealtimeDashboard::rangeBreakdowns()). See the migrations
 * that create them. Every total is split by the accounts its pages belong to (owners()), never by
 * who viewed them, so a blocked account's are left out of every range when they are read
 * (BlockedAccounts::withoutTheirCounts()); its own signed-in browsing is taken back at the block
 * for as long as the rows are kept (forgetViewsBy()).
 *
 * Called from RealtimeTracker::prune() itself, so every path that deletes rows (the scheduler's
 * realtime:prune on both cron rails, and pruneIfDue() on the request path when the cron has
 * stopped) counts them first. What it counts is what the live page counts as a page view: an
 * engaged row on the marketing site, a schedule page, the app or the sign-up pages, never an
 * admin's own and never a blocked account's (BlockedAccounts). An embedded calendar is not a page
 * of this site and is left out, as the live page leaves it out of its page views.
 *
 * Each page view is counted once, in the hour it started, at the run after it was engaged: the
 * cursor in realtime_rollup moves over engaged_at, and the read, the counts and the cursor are one
 * transaction holding that row's lock, so two runs at once (one on each rail) wait for each other.
 */
class RealtimeRollup
{
    /** The parts of the site a page view is counted under. */
    public const SURFACES = ['wp', 'gp', 'ap', 'auth'];

    /** Long enough for the 30-day range and the 30 days it is compared with. */
    public const KEEP_DAYS = 62;

    /** The breakdowns are not compared with anything: the longest range they offer is enough. */
    public const BREAKDOWN_KEEP_DAYS = 31;

    private const COLUMNS = [
        'realtime_hits.started_at', 'realtime_hits.surface', 'realtime_hits.is_entrance', 'realtime_hits.path',
        'realtime_hits.title', 'realtime_hits.role_id', 'realtime_hits.event_id', 'realtime_hits.country',
        'realtime_hits.source_channel', 'realtime_hits.source_name',
    ];

    /**
     * A row engaged in the last few seconds may still be in a beacon's transaction that has not
     * committed: left for the next run rather than skipped for good.
     */
    private const SETTLE_SECONDS = 30;

    private static ?bool $tablesExist = null;

    private static ?bool $breakdownsExist = null;

    /** @return int page views counted */
    public static function run(): int
    {
        if (! self::tablesExist()) {
            return 0;
        }

        $until = RealtimeTracker::ts(RealtimeTracker::now()->subSeconds(self::SETTLE_SECONDS));

        return DB::transaction(function () use ($until) {
            $through = self::lockCursor();

            if ($through >= $until) {
                return 0;
            }

            $hits = (new BlockedAccounts)->withoutTheirHits(self::countable())
                // last_seen_at is never earlier than engaged_at, and it is the column with an index.
                ->where('realtime_hits.last_seen_at', '>=', $through)
                ->where('realtime_hits.engaged_at', '>=', $through)
                ->where('realtime_hits.engaged_at', '<', $until)
                ->get(self::COLUMNS);

            $counted = self::apply($hits, 1);

            DB::table('realtime_rollup')->where('id', 1)->update(['through_at' => $until]);

            DB::table('realtime_hourly')
                ->where('hour', '<', RealtimeTracker::ts(RealtimeTracker::now()->subDays(self::KEEP_DAYS)))
                ->delete();

            if (self::breakdownsExist()) {
                DB::table('realtime_breakdowns')
                    ->where('hour', '<', RealtimeTracker::ts(RealtimeTracker::now()->subDays(self::BREAKDOWN_KEEP_DAYS)))
                    ->delete();
            }

            return $counted;
        });
    }

    /**
     * Take back what an account's own signed-in page views added, at the moment it is blocked.
     *
     * The totals never say who viewed a page (the privacy policy promises that), so after the
     * hour realtime_hits keeps them there is nothing to take back by; the owner columns cover
     * the views OF its schedules and events in every range, and this covers its own browsing for
     * as long as the rows are still here. Only rows the cursor has passed were counted.
     */
    public static function forgetViewsBy(int $userId): void
    {
        if (! self::tablesExist()) {
            return;
        }

        try {
            DB::transaction(function () use ($userId) {
                $through = self::lockCursor();

                $hits = self::countable()
                    ->where('realtime_hits.user_id', $userId)
                    ->whereNotNull('realtime_hits.engaged_at')
                    ->where('realtime_hits.engaged_at', '<', $through)
                    ->get(self::COLUMNS);

                // Not a view the owner columns already leave out (its own schedule's page): taken
                // back here as well, it would be missing twice over after an Unblock.
                self::apply($hits, -1, $userId);
            });
        } catch (Throwable $e) {
            // The block stands whatever happens here; the counts are only a picture.
            report($e);
        }
    }

    /** The page views the totals count: engaged, not an admin's own, on a page of this site. */
    private static function countable(): \Illuminate\Database\Query\Builder
    {
        return DB::table('realtime_hits')
            ->whereNotNull('realtime_hits.engaged_at')
            ->where('realtime_hits.is_admin', false)
            ->whereIn('realtime_hits.surface', self::SURFACES);
    }

    /**
     * Add ($sign 1) or take back ($sign -1) a set of page views: the hourly totals by part of the
     * site and, once its table exists, the three breakdowns. Each is attributed to the accounts
     * its page belongs to (owners()). Returns the page views counted.
     */
    private static function apply(Collection $hits, int $sign, ?int $unlessOwnedBy = null): int
    {
        if ($hits->isEmpty()) {
            return 0;
        }

        [$roleOwners, $events] = self::owners($hits);
        $hourly = [];
        $breakdowns = [];

        foreach ($hits as $hit) {
            $hour = substr((string) $hit->started_at, 0, 13).':00:00';
            $owners = [
                'role_owner_id' => $hit->role_id ? ($roleOwners[$hit->role_id] ?? 0) : 0,
                'event_user_id' => $hit->event_id ? ($events[$hit->event_id]['user'] ?? 0) : 0,
                'event_owner_id' => $hit->event_id ? ($events[$hit->event_id]['owner'] ?? 0) : 0,
            ];
            if ($unlessOwnedBy !== null && in_array($unlessOwnedBy, $owners, true)) {
                continue;
            }
            $ownerKey = implode(':', $owners);

            $key = $hour.'|'.$hit->surface.'|'.$ownerKey;
            $hourly[$key] ??= ['hour' => $hour, 'surface' => $hit->surface] + $owners + ['page_views' => 0, 'visits' => 0];
            $hourly[$key]['page_views']++;
            $hourly[$key]['visits'] += $hit->is_entrance ? 1 : 0;

            $page = RealtimeRows::pageKey($hit->surface, $hit->role_id, $hit->event_id, $hit->path);
            $add = function (string $dimension, string $item, array $label) use (&$breakdowns, $hour, $owners, $ownerKey) {
                $key = $hour.'|'.$dimension.'|'.$item.'|'.$ownerKey;
                $breakdowns[$key] ??= [
                    'hour' => $hour,
                    'dimension' => $dimension,
                    'item_hash' => sha1($item),
                    'item' => mb_substr($item, 0, 500),
                    'surface' => null,
                    'role_id' => 0,
                    'event_id' => 0,
                    'title' => null,
                    'channel' => null,
                ] + $owners + ['views' => 0];
                $breakdowns[$key] = array_merge($breakdowns[$key], array_filter($label, fn ($value) => $value !== null));
                $breakdowns[$key]['views']++;
            };

            $add('page', $page, [
                'surface' => $hit->surface,
                'role_id' => (int) $hit->role_id,
                'event_id' => (int) $hit->event_id,
                'title' => $hit->title !== null ? mb_substr((string) $hit->title, 0, 255) : null,
            ]);

            if ($hit->country) {
                $add('country', (string) $hit->country, []);
            }

            // Visits, not page views, as on the live card: only an entrance carries a source for
            // a visitor who has not accepted cookies.
            if ($hit->is_entrance && ($source = RealtimeRows::sourceKey($hit->source_channel, $hit->source_name))) {
                $add('source', $source, ['channel' => mb_substr((string) $hit->source_channel, 0, 32)]);
            }
        }

        if (self::breakdownsExist()) {
            self::write('realtime_hourly', $hourly, ['hour', 'surface', 'role_owner_id', 'event_user_id', 'event_owner_id'], ['page_views', 'visits'], $sign);
            self::write('realtime_breakdowns', $breakdowns, ['hour', 'dimension', 'item_hash', 'role_owner_id', 'event_user_id', 'event_owner_id'], ['views'], $sign);
        } else {
            // Code ahead of its migration (a selfhost update before `migrate`): the old key.
            $plain = [];
            foreach ($hourly as $row) {
                $key = $row['hour'].'|'.$row['surface'];
                $plain[$key] ??= ['hour' => $row['hour'], 'surface' => $row['surface'], 'page_views' => 0, 'visits' => 0];
                $plain[$key]['page_views'] += $row['page_views'];
                $plain[$key]['visits'] += $row['visits'];
            }
            self::write('realtime_hourly', $plain, ['hour', 'surface'], ['page_views', 'visits'], $sign);
        }

        return array_sum(array_column($hourly, 'page_views'));
    }

    /**
     * The accounts the pages of $hits belong to, by BlockedAccounts' rule: the owner of a schedule
     * (Role::scopeOwned(): a venue made from a curator's event location carries the curator's
     * user_id without being theirs), and for an event the account that made it and the owner of
     * the schedule it belongs to.
     *
     * @return array{0: array<int, int>, 1: array<int, array{user: int, owner: int}>}
     */
    private static function owners(Collection $hits): array
    {
        $eventRows = DB::table('events')
            ->whereIn('id', $hits->pluck('event_id')->filter()->unique()->values()->all() ?: [0])
            ->get(['id', 'user_id', 'creator_role_id']);

        $roleIds = $hits->pluck('role_id')->merge($eventRows->pluck('creator_role_id'))->filter()->unique()->values()->all();

        $roleOwners = $roleIds === [] ? [] : Role::query()
            ->whereIn('roles.id', $roleIds)
            ->owned()
            ->pluck('roles.user_id', 'roles.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $events = [];
        foreach ($eventRows as $event) {
            $events[$event->id] = [
                'user' => (int) $event->user_id,
                'owner' => $event->creator_role_id ? ($roleOwners[$event->creator_role_id] ?? 0) : 0,
            ];
        }

        return [$roleOwners, $events];
    }

    /**
     * Add the counts to their rows, or take them back. An upsert that adds on conflict
     * (values() rather than MySQL 8's alias, which MariaDB does not read); a take-back never goes
     * below zero, because a row that was pruned meanwhile has nothing left to take.
     */
    private static function write(string $table, array $rows, array $key, array $counts, int $sign): void
    {
        if ($rows === []) {
            return;
        }

        if ($sign > 0) {
            foreach (array_chunk(array_values($rows), 500) as $chunk) {
                DB::table($table)->upsert($chunk, $key, array_combine($counts, array_map(
                    fn ($column) => DB::raw("`{$column}` + values(`{$column}`)"),
                    $counts,
                )));
            }

            return;
        }

        foreach ($rows as $row) {
            DB::table($table)
                ->where(array_intersect_key($row, array_flip($key)))
                ->update(array_combine($counts, array_map(
                    fn ($column) => DB::raw('GREATEST(0, CAST(`'.$column.'` AS SIGNED) - '.(int) $row[$column].')'),
                    $counts,
                )));
        }
    }

    /**
     * The cursor, locked for this transaction. The first run starts at the oldest engaged row
     * realtime_hits still holds, so the hour before the tables existed is counted too.
     */
    private static function lockCursor(): string
    {
        $through = DB::table('realtime_rollup')->where('id', 1)->lockForUpdate()->value('through_at');

        if ($through === null) {
            $oldest = DB::table('realtime_hits')->min('engaged_at');
            DB::table('realtime_rollup')->insertOrIgnore([
                'id' => 1,
                'through_at' => $oldest ?? RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(RealtimeTracker::RETENTION_MINUTES)),
            ]);
            $through = DB::table('realtime_rollup')->where('id', 1)->lockForUpdate()->value('through_at');
        }

        return (string) $through;
    }

    /** Where the counts begin, for "counting since": null until anything has been counted. */
    public static function since(): ?string
    {
        if (! self::tablesExist()) {
            return null;
        }

        return DB::table('realtime_hourly')->min('hour');
    }

    /**
     * Whether both tables exist yet: a selfhost install can run this code before `migrate`.
     */
    public static function tablesExist(): bool
    {
        if (self::$tablesExist !== null) {
            return self::$tablesExist;
        }

        try {
            return self::$tablesExist = Schema::hasTable('realtime_hourly') && Schema::hasTable('realtime_rollup');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Whether the breakdowns' table, and with it the owner columns of realtime_hourly, exist yet.
     */
    public static function breakdownsExist(): bool
    {
        if (self::$breakdownsExist !== null) {
            return self::$breakdownsExist;
        }

        try {
            return self::$breakdownsExist = Schema::hasTable('realtime_breakdowns');
        } catch (Throwable) {
            return false;
        }
    }

    /** Where the breakdowns' counts begin, for "counted from": null until anything has been. */
    public static function breakdownsSince(): ?string
    {
        return self::breakdownsExist() ? DB::table('realtime_breakdowns')->min('hour') : null;
    }

    /** For tests that create or drop the tables. */
    public static function forget(): void
    {
        self::$tablesExist = null;
        self::$breakdownsExist = null;
    }
}
