<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps a curator's calendar in step with the talent/venue schedules listed in role_sources.
 *
 * Rather than teaching every "events belonging to a schedule" query about sources - the
 * predicate is hand-written inline at ~35 read sites and there is no shared scope - this
 * writes the event_role rows those queries already read. Rows it writes carry
 * is_auto_sourced = 1, so they can be withdrawn again without touching anything the curator
 * curated by hand. Same idea as the push-side Role::autoCurateEvent().
 *
 * The whole thing is two set queries sharing one eligibility rule, so it is idempotent and
 * self-healing: the scheduled app:sync-curator-sources pass is what makes it correct, and the
 * single hook in EventRepo::saveEvent() only makes it immediate.
 */
class CuratorSourceService
{
    /**
     * The one eligibility rule both set queries share. Expects `role_sources as rs` and the
     * event aliased as $eventAlias to already be in the query.
     *
     * is_draft excludes Internal too (Internal implies is_draft) and is_private keeps Unlisted
     * events unlisted. No date bound - past and future both. Cancelled events are kept: they
     * still render, struck through, on the source's own page.
     *
     * Both roles are checked for is_deleted because a role can be retired without the FK
     * cascade firing: RoleController::unfollow() sets is_deleted on an unclaimed schedule that
     * loses its last follower, as does ApiScheduleController. Without this a retired source
     * keeps feeding events into every curator that lists it. Matches the filter on
     * Role::connectedRoleIds() and on the add path in RoleController::saveEventSources().
     *
     * The curator's type is re-checked for the same reason: RoleController::update() mass-fills
     * from the request and `type` is fillable, so a curator can be flipped to a venue while its
     * role_sources rows survive. saveEventSources() refuses that combination when the source is
     * added; this refuses it on every pass afterwards.
     */
    protected function applyEligibility(Builder $query, string $eventAlias): Builder
    {
        return $query
            ->join('roles as source_role', function ($join) {
                $join->on('source_role.id', '=', 'rs.source_role_id')
                    ->where('source_role.is_deleted', '=', false)
                    // The SOURCE's type is re-checked for exactly the reason the curator's is
                    // below: type is fillable and RoleController::update() mass-fills from the
                    // request, so a talent or venue can be flipped to a curator while its
                    // role_sources rows survive. saveEventSources() refuses that combination when
                    // the source is added - a curator pulling from a curator chains one
                    // aggregation onto another, and the parent fills with events from schedules
                    // it never listed - and this refuses it on every pass afterwards.
                    ->whereIn('source_role.type', ['talent', 'venue']);
            })
            ->join('roles as curator_role', function ($join) {
                $join->on('curator_role.id', '=', 'rs.role_id')
                    ->where('curator_role.is_deleted', '=', false)
                    ->where('curator_role.type', '=', 'curator');
            })
            ->where($eventAlias.'.is_draft', false)
            ->where($eventAlias.'.is_private', false);
    }

    /**
     * Link every eligible event that has no event_role row for the curator yet.
     *
     * A row that already exists is never overwritten, which is what preserves hand-curated
     * rows and the is_accepted = false tombstones left by Uncurate or by unticking the
     * curator on the event form.
     */
    protected function linkMissing(?int $curatorId, ?int $eventId): int
    {
        $batch = (int) config('usage.curator_source_batch', 50000);

        $select = DB::table('role_sources as rs')
            ->join('event_role as src', function ($join) {
                $join->on('src.role_id', '=', 'rs.source_role_id')
                    ->where('src.is_accepted', '=', true);
            })
            ->join('events as e', 'e.id', '=', 'src.event_id')
            ->leftJoin('event_role as existing', function ($join) {
                $join->on('existing.role_id', '=', 'rs.role_id')
                    ->on('existing.event_id', '=', 'src.event_id');
            })
            ->whereNull('existing.id')
            ->when($curatorId, fn ($q) => $q->where('rs.role_id', $curatorId))
            ->when($eventId, fn ($q) => $q->where('src.event_id', $eventId))
            // Two sources can cover one event; the earliest-added one wins, so which
            // sub-schedule the event lands in is deterministic rather than plan-dependent.
            ->orderBy('rs.id')
            ->limit($batch)
            ->select([
                'src.event_id',
                'rs.role_id',
                DB::raw('1'),
                DB::raw('1'),
                'rs.group_id',
            ]);

        $this->applyEligibility($select, 'e');

        $added = DB::table('event_role')->insertOrIgnoreUsing(
            ['event_id', 'role_id', 'is_accepted', 'is_auto_sourced', 'group_id'],
            $select
        );

        if ($added >= $batch) {
            Log::info('CuratorSourceService: hit the per-run link ceiling; the rest follows next run', [
                'batch' => $batch,
                'curator_id' => $curatorId,
            ]);
        }

        return $added;
    }

    /**
     * Drop auto-sourced rows whose event no longer qualifies from any of that curator's
     * sources - the source was removed, it declined the event, or the event went draft,
     * private or detached.
     *
     * Done as select-then-delete rather than a correlated DELETE: MySQL refuses a subquery
     * on the table being deleted from (error 1093), and event_role appears on both sides.
     *
     * `is_accepted = false` rows are left alone unless $pruneDeclined is set. They are the
     * tombstones Uncurate and the event form leave behind, and the docs promise a removed event
     * "stays gone even though the source is still connected" - so the source owner unpublishing
     * and republishing must not quietly undo it. Only editing the source list itself
     * (RoleController::saveEventSources) clears them, which is what makes re-adding a source
     * restore everything it covers.
     */
    protected function unlinkStale(?int $curatorId, ?int $eventId, bool $pruneDeclined = false): int
    {
        $batch = (int) config('usage.curator_source_batch', 50000);

        $ids = DB::table('event_role as er')
            ->where('er.is_auto_sourced', true)
            ->when(! $pruneDeclined, fn ($q) => $q->where(function ($q) {
                $q->whereNull('er.is_accepted')->orWhere('er.is_accepted', '!=', false);
            }))
            ->when(
                $curatorId,
                fn ($q) => $q->where('er.role_id', $curatorId),
                // Scoped to schedules that are still live curators with sources: the eligibility
                // subquery below cannot do it, because for a retired curator it simply matches
                // nothing and NOT EXISTS then deletes every row it has.
                fn ($q) => $q->whereIn('er.role_id', fn ($sub) => $sub
                    ->select('rs.role_id')
                    ->from('role_sources as rs')
                    ->join('roles as cr', 'cr.id', '=', 'rs.role_id')
                    ->where('cr.is_deleted', false)
                    ->where('cr.type', 'curator'))
            )
            ->when($eventId, fn ($q) => $q->where('er.event_id', $eventId))
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('role_sources as rs')
                    ->join('event_role as src', function ($join) {
                        $join->on('src.role_id', '=', 'rs.source_role_id')
                            ->on('src.event_id', '=', 'er.event_id')
                            ->where('src.is_accepted', '=', true);
                    })
                    ->join('events as e', 'e.id', '=', 'src.event_id')
                    ->whereColumn('rs.role_id', 'er.role_id');

                $this->applyEligibility($q, 'e');
            })
            // Same ceiling linkMissing() uses. Only the DELETE was chunked before, so the id
            // set itself was materialised in full - a curator dropping several large sources
            // pulled every affected event_role id into memory at once. The remainder is picked
            // up by the next pass, which runs every five minutes.
            ->limit($batch)
            ->pluck('er.id');

        if ($ids->count() >= $batch) {
            Log::info('Curator source unlink hit its batch ceiling; the rest follows next pass', [
                'batch' => $batch,
                'curator_id' => $curatorId,
            ]);
        }

        $removed = 0;

        foreach ($ids->chunk(1000) as $chunk) {
            $removed += DB::table('event_role')->whereIn('id', $chunk->all())->delete();
        }

        return $removed;
    }

    /**
     * Bring event_role in step with role_sources. Optionally narrowed to one curator
     * (adding or removing a source) or one event (a save).
     *
     * $pruneDeclined is set only by the source-list editor: outside that, a declined row is a
     * deliberate per-event removal and has to survive. See unlinkStale().
     *
     * @return array{added: int, removed: int}
     */
    public function reconcile(?Role $curator = null, ?int $eventId = null, bool $pruneDeclined = false): array
    {
        // A retired or re-typed schedule is skipped rather than stripped. The unscoped query
        // filters this itself; a scoped call has to be told.
        if ($curator && ($curator->is_deleted || ! $curator->isCurator())) {
            return ['added' => 0, 'removed' => 0];
        }

        $curatorId = $curator?->id;

        return [
            'added' => $this->linkMissing($curatorId, $eventId),
            'removed' => $this->unlinkStale($curatorId, $eventId, $pruneDeclined),
        ];
    }

    /**
     * Reconcile a single event. Called after EventRepo::saveEvent() has synced the event's
     * schedules, which both repairs the rows that sync() just detached and makes a brand
     * new event show up on its curators straight away.
     */
    public function syncEvent(Event $event): array
    {
        if (! $event->exists) {
            return ['added' => 0, 'removed' => 0];
        }

        return $this->reconcile(null, $event->id);
    }

    /**
     * Move a source's already-linked events into a different sub-schedule after the curator
     * changes the source's sub-schedule. Only auto-sourced rows move, so a group the curator
     * set on an individual event by hand is left alone.
     *
     * Where two sources cover one event the later call wins, matching "last edit applies"
     * rather than the earliest-source rule used at link time.
     */
    public function refileSource(Role $curator, int $sourceRoleId, ?int $groupId): int
    {
        $ids = DB::table('event_role as er')
            ->join('event_role as src', function ($join) use ($sourceRoleId) {
                $join->on('src.event_id', '=', 'er.event_id')
                    ->where('src.role_id', '=', $sourceRoleId);
            })
            ->where('er.role_id', $curator->id)
            ->where('er.is_auto_sourced', true)
            ->pluck('er.id');

        $updated = 0;

        foreach ($ids->chunk(1000) as $chunk) {
            $updated += DB::table('event_role')->whereIn('id', $chunk->all())->update(['group_id' => $groupId]);
        }

        return $updated;
    }
}
