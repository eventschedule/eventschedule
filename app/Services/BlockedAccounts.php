<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;

/**
 * What belongs to a blocked account, which the operator's two overview pages (/admin/dashboard
 * and /admin/realtime) leave out: the account itself, every schedule it owns, and every event it
 * made or one of those schedules owns.
 *
 * A block takes the account's schedules down (AccountBlockService), but that is not all of it:
 * the sign-up, the visits and the last day's activity stay in the record, and an event it put on
 * somebody else's venue or curator outlives its own schedule. Those pages are for everyone else;
 * /admin/blocked is where a block is looked at.
 *
 * Read once per page and handed to each query as ids, as the demo's schedules are. Blocked
 * accounts are few and users.blocked_at has no index, so the ids come off account_blocks, which
 * has a row for every block (AccountBlockService writes both in one transaction); blocked_at is
 * still asked, because it is the fact every request reads. With nobody blocked, every method
 * leaves its query as it was.
 */
class BlockedAccounts
{
    /** @var list<int>|null */
    private ?array $userIds = null;

    /** @var list<int>|null */
    private ?array $scheduleIds = null;

    /** @return list<int> */
    public function userIds(): array
    {
        return $this->userIds ??= DB::table('account_blocks')
            ->join('users', 'users.id', '=', 'account_blocks.user_id')
            ->whereNotNull('users.blocked_at')
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function any(): bool
    {
        return $this->userIds() !== [];
    }

    /**
     * Every schedule a blocked account owns, by the rule its block takes them down by
     * (AccountBlockService::schedules(): Role::scopeOwned()), the ones taken down included: an
     * event keeps its creator_role_id after its schedule is deleted. Not every row whose user_id
     * is the account's: a venue made from a curator's event location is written with the
     * curator's user_id and attached to it only as a follower, and the block leaves it up.
     *
     * @return list<int>
     */
    public function scheduleIds(): array
    {
        if (! $this->any()) {
            return [];
        }

        return $this->scheduleIds ??= Role::query()
            ->whereIn('roles.user_id', $this->userIds())
            ->owned()
            ->pluck('roles.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * The events among $eventIds that are a blocked account's.
     *
     * @param  iterable<int|string|null>  $eventIds
     * @return array<int, true>
     */
    public function eventsAmong(iterable $eventIds): array
    {
        $ids = collect($eventIds)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        if ($ids === [] || ! $this->any()) {
            return [];
        }

        return DB::table('events')
            ->whereIn('id', $ids)
            ->where(fn ($query) => $this->theirEvents($query))
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * For whereNotIn('<table>.id', ...): a blocked account's events, read once from the two
     * foreign-key indexes. Asked by id, as AdminDashboard asks for the demo's events, and not by
     * testing user_id and creator_role_id on each row: a count that one index answers alone (the
     * dashboard's all-time total) would otherwise read every event there is.
     */
    public function eventIds(): \Illuminate\Database\Query\Builder
    {
        return DB::table('events as their_events')
            ->select('their_events.id')
            ->where(fn ($query) => $this->theirEvents($query, 'their_events'));
    }

    /**
     * Leave a blocked account's events out of a query on `events`.
     *
     * @template TQuery of \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    public function withoutTheirEvents($query)
    {
        return $this->any() ? $query->whereNotIn('events.id', $this->eventIds()) : $query;
    }

    /**
     * Leave out of a query on `realtime_hits` a blocked account's own page views, and every view
     * of its schedules' and events' pages, whoever made it. Only guest pages and embeds carry a
     * schedule's id (layouts/app-guest); the admin panel carries none, so a team member's own
     * visit to the schedule's settings stays theirs. Each column is nullable, so each test lets a
     * null through.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function withoutTheirHits($query)
    {
        if (! $this->any()) {
            return $query;
        }

        $query->where(fn ($q) => $q->whereNull('realtime_hits.user_id')->orWhereNotIn('realtime_hits.user_id', $this->userIds()));

        if ($schedules = $this->scheduleIds()) {
            $query->where(fn ($q) => $q->whereNull('realtime_hits.role_id')->orWhereNotIn('realtime_hits.role_id', $schedules));
        }

        $query->where(fn ($q) => $q->whereNull('realtime_hits.event_id')->orWhereNotIn('realtime_hits.event_id', $this->eventIds()));

        return $query;
    }

    /**
     * The visitor keys a blocked account was signed in on since $since (a RealtimeTracker::ts()
     * string), for RealtimeDashboard: an anonymous page view on one of them belongs to nobody.
     * The list and the expanded timeline both ask over the timeline's hour, so a key the account
     * used forty minutes ago cannot join a row to someone in the list and not in the timeline.
     * With the account's own rows gone, such a key can look as though it saw one user, and the
     * account's browsing from before it signed in would be handed to an honest person who shares
     * its network and browser.
     *
     * @return array<string, true>
     */
    public function visitorKeys(string $since): array
    {
        if (! $this->any()) {
            return [];
        }

        return DB::table('realtime_hits')
            ->whereIn('user_id', $this->userIds())
            ->where('last_seen_at', '>=', $since)
            ->whereNotNull('visitor_key')
            ->distinct()
            ->pluck('visitor_key')
            ->mapWithKeys(fn ($key) => [(string) $key => true])
            ->all();
    }

    /**
     * What makes an event a blocked account's: it made it (on its own schedule, or sent it to
     * somebody else's, which saves it under theirs), or one of its schedules owns it (a team
     * member made it there, and a venue or curator it was also put on keeps it).
     */
    private function theirEvents($query, string $table = 'events'): void
    {
        $query->whereIn($table.'.user_id', $this->userIds());

        if ($schedules = $this->scheduleIds()) {
            $query->orWhereIn($table.'.creator_role_id', $schedules);
        }
    }
}
