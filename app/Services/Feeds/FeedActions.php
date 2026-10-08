<?php

namespace App\Services\Feeds;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\EventLifecycleService;
use Illuminate\Support\Collection;

/**
 * What the people who run a schedule do to a feed and to what it made: the buttons on the
 * feed's page. Each is a decision by a person, so each names who took it.
 */
class FeedActions
{
    /** How long after its first read a feed can be undone. */
    public const UNDO_HOURS = 24;

    public function __construct(
        private EventLifecycleService $lifecycle,
        private FeedEventWriter $writer,
        private FeedImporter $importer,
    ) {}

    /** The items among $ids that are this feed's drafts waiting to be looked over. */
    private function waiting(EventFeed $feed, array $ids): Collection
    {
        return $feed->items()
            ->whereIn('id', $ids)
            ->where('state', EventFeedItem::STATE_IMPORTED)
            // A draft the source has since called off is not waiting to be published.
            ->whereHas('event', fn ($query) => $query->where('is_draft', true)->where('is_cancelled', false))
            ->with('event')
            ->get();
    }

    /** @return int How many were published. */
    public function publish(EventFeed $feed, Role $role, User $by, array $itemIds): int
    {
        $published = 0;

        foreach ($this->waiting($feed, $itemIds) as $item) {
            $published += $this->lifecycle->publish($item->event, $by->id, $role) === EventLifecycleService::PUBLISHED ? 1 : 0;
            $item->forceFill(['publish_requested_at' => null])->save();
        }

        $this->recount($feed);

        return $published;
    }

    /**
     * "Not this one." The draft is deleted and the feed does not make it again, however long
     * the source goes on listing it. It can be added after all from the feed's list of events.
     *
     * @return array{skipped: int, kept: int} How many were skipped, and how many were left
     *                                        because they hold somebody's work.
     */
    public function skip(EventFeed $feed, User $by, array $itemIds): array
    {
        $skipped = $kept = 0;

        foreach ($this->waiting($feed, $itemIds) as $item) {
            // A draft nobody can have signed up for. If somebody has all the same, it stays. And
            // so does one the owner has already worked on: Skip is "not this one", said of what
            // the feed brought, and it deletes for good. What you changed stays, here as when a
            // feed is removed or its first read undone.
            if ($this->writer->hasPeople($item->event) || $this->writer->hasOwnersWork($item->event, $item)) {
                $kept++;

                continue;
            }

            $event = $item->event;
            $item->forceFill(['state' => EventFeedItem::STATE_SKIPPED, 'pending' => null, 'imported' => null, 'publish_requested_at' => null])->save();
            $this->lifecycle->delete($event, $by->id);
            $skipped++;
        }

        $this->recount($feed);

        return ['skipped' => $skipped, 'kept' => $kept];
    }

    /**
     * Everything waiting, published. Asked for here and done by the runs that follow, a few at
     * a time: each publish pushes to the schedule's connected calendars, and a hundred of those
     * do not belong in one request. The asking lapses after an hour, and when the feed is paused.
     *
     * @return int How many were asked for.
     */
    public function publishAll(EventFeed $feed, Role $role, User $by): array
    {
        // A feed that is not being read (paused, or on a plan without feeds) has no run to do
        // it: the asking would sit there while the page said "being published". A page of them
        // is published now, in the request, and the button is pressed again for the next.
        if ($feed->isPaused() || ! EventFeed::allowedFor($role)) {
            $ids = $feed->items()
                ->where('state', EventFeedItem::STATE_IMPORTED)
                ->whereHas('event', fn ($query) => $query->where('is_draft', true)->where('is_cancelled', false))
                ->orderBy('starts_at')
                ->orderBy('id')
                ->limit(self::PUBLISH_AT_ONCE)
                ->pluck('id')
                ->all();

            return ['asked' => 0, 'published' => $this->publish($feed, $role, $by, $ids)];
        }

        $asked = $feed->items()
            ->where('state', EventFeedItem::STATE_IMPORTED)
            ->whereHas('event', fn ($query) => $query->where('is_draft', true)->where('is_cancelled', false))
            ->update(['publish_requested_at' => now()]);

        if ($asked) {
            $feed->forceFill(['next_check_at' => now()])->save();
        }

        return ['asked' => $asked, 'published' => 0];
    }

    /** How many are published in one request, where no run will do it. */
    public const PUBLISH_AT_ONCE = 25;

    /**
     * How many coming events have ALREADY left this feed and are still on the schedule, which
     * is what "Mark it cancelled" or "Remove it" would reach at the next read if the feed is
     * switched to it from "Leave it". Said on the Edit page, beside the choice, before Save.
     */
    public function alreadyGone(EventFeed $feed): int
    {
        return $feed->items()
            ->where('state', EventFeedItem::STATE_IMPORTED)
            ->where('missing_reads', '>=', FeedImporter::STRIKES)
            ->whereHas('event', fn ($query) => $query->where('is_cancelled', false)->where('starts_at', '>', now()->utc()->format('Y-m-d H:i:s')))
            ->count();
    }

    /** Read it on the next run instead of when it is due. Not more than once a minute. */
    public function readNow(EventFeed $feed): bool
    {
        if ($feed->isPaused() || ($feed->last_checked_at && $feed->last_checked_at->gt(now()->subMinute()))) {
            return false;
        }

        $feed->forceFill(['next_check_at' => now()])->save();

        return true;
    }

    public function pause(EventFeed $feed): void
    {
        EventFeed::pauseWhere(fn ($query) => $query->whereKey($feed->id), EventFeed::PAUSED_BY_OWNER);
    }

    /** Whoever resumes a feed has looked at it: it starts again with a clean slate, at once. */
    public function resume(EventFeed $feed): void
    {
        $feed->forceFill(['paused_at' => null, 'pause_reason' => null, 'failure_count' => 0, 'next_check_at' => now()])->save();
    }

    /**
     * The feed's own settings. They hold from the next read: what is already on the schedule
     * keeps its visibility and its sub-schedule. A new clock is different, because it says the
     * times already read were read wrong: the source is asked again at once, and a start that
     * still holds what the feed wrote follows it. One the owner set stays, and one people signed
     * up for becomes a decision, as any move at the source does.
     *
     * @return bool Whether the clock changed.
     */
    public function edit(EventFeed $feed, array $choices): bool
    {
        $zone = \App\Utils\TimezoneUtils::canonicalize($choices['source_timezone'] ?? null) ?? $feed->source_timezone;
        $left = $choices['left_action'] ?? $feed->left_action;
        // A stored name saved under the name it goes by now (Asia/Calcutta, Asia/Kolkata) is
        // the same clock: no times were read wrong, and nothing is asked for again.
        $clockChanged = $zone !== (\App\Utils\TimezoneUtils::canonicalize($feed->source_timezone) ?? $feed->source_timezone);

        $feed->forceFill([
            'name' => mb_substr(trim((string) ($choices['name'] ?? '')), 0, 120) ?: $feed->name,
            'publish_mode' => ($choices['publish_mode'] ?? $feed->publish_mode) === EventFeed::PUBLISH ? EventFeed::PUBLISH : EventFeed::DRAFT,
            // A source that cannot say an event is gone is not asked to act on it.
            'left_action' => $feed->can_see_leaving && in_array($left, EventFeed::LEFT_ACTIONS, true) ? $left : EventFeed::LEFT_KEEP,
            // Absent is not "none": only a caller that names the key clears it.
            'group_id' => array_key_exists('group_id', $choices) ? $choices['group_id'] : $feed->group_id,
            'category_id' => array_key_exists('category_id', $choices) ? $choices['category_id'] : $feed->category_id,
            'source_timezone' => $zone,
        ]);

        if ($clockChanged) {
            // The same bytes now mean other times, so "nothing changed since last time" is not
            // an answer to take, and a post's own page is read again though it was read today.
            $feed->forceFill(['etag' => null, 'last_modified' => null, 'next_check_at' => now()]);
            $feed->items()
                ->whereNotNull('detail_url')
                ->whereIn('state', [EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE])
                ->where(fn ($when) => $when->whereNull('starts_at')->orWhere('starts_at', '>', now()))
                ->update(['detail_checked_at' => null]);
        }

        $feed->save();

        return $clockChanged;
    }

    /**
     * Stop reading an address for good. Its events stay, as events like any other, unless the
     * owner asks for the coming ones to go too, and then only those that are the feed's alone:
     * nobody signed up, nothing of the owner's on them.
     *
     * @return int How many events were deleted with it.
     */
    public function remove(EventFeed $feed, User $by, bool $withItsEvents): int
    {
        $deleted = 0;

        if ($withItsEvents) {
            $now = now()->utc()->format('Y-m-d H:i:s');

            foreach ($feed->items()->where('state', EventFeedItem::STATE_IMPORTED)->whereNotNull('event_id')->with('event')->get() as $item) {
                $event = $item->event;

                if (! $event || ! $event->starts_at || $event->starts_at <= $now || $this->writer->hasPeople($event) || $this->writer->hasOwnersWork($event, $item)) {
                    continue;
                }

                $this->lifecycle->delete($event, $by->id);
                $deleted++;
            }
        }

        $feed->delete();

        return $deleted;
    }

    /** A decision: leave the event as it is. The same difference is not brought up again. */
    public function keep(EventFeed $feed, EventFeedItem $item): void
    {
        $decision = $item->pending['decide'] ?? null;

        if ($item->state !== EventFeedItem::STATE_DECIDE || ! $decision) {
            return;
        }

        $item->forceFill([
            'state' => EventFeedItem::STATE_IMPORTED,
            'decided_hash' => FeedEventWriter::hashOf($decision),
            'pending' => array_diff_key($item->pending, ['decide' => true]) ?: null,
        ])->save();

        $this->recount($feed);
    }

    /**
     * A decision: do what the feed says. An event that is gone or called off at the source is
     * cancelled, never deleted here: people signed up for it, and their records go with a
     * deleted event. One that moved is moved. Either way the people who signed up can be told.
     *
     * @return ?string What was done: `cancelled` or `moved`. Null when there was nothing to do.
     */
    public function apply(EventFeed $feed, Role $role, EventFeedItem $item, User $by, bool $notify, ?string $note): ?string
    {
        $decision = $item->pending['decide'] ?? null;
        $event = $item->event_id ? Event::find($item->event_id) : null;

        if ($item->state !== EventFeedItem::STATE_DECIDE || ! $decision || ! $event) {
            return null;
        }

        if (($decision['kind'] ?? null) === 'moved') {
            $done = $this->writer->applyHeld($feed, $role, $item, $event, $notify, $note) ? 'moved' : null;
        } else {
            $this->lifecycle->cancel($event, $by->id, notifyAttendees: $notify, note: $note);
            // The owner's cancellation, not the feed's: a source that lists the event again
            // does not put it back, least of all after people were told it is off.
            $item->forceFill([
                'state' => EventFeedItem::STATE_IMPORTED,
                'cancelled_by_feed' => false,
                'feed_cancelled_at' => null,
                'decided_hash' => FeedEventWriter::hashOf($decision),
                'pending' => array_diff_key($item->pending, ['decide' => true]) ?: null,
            ])->save();
            $done = 'cancelled';
        }

        $this->recount($feed);

        return $done;
    }

    public function canUndoFirstRead(EventFeed $feed): bool
    {
        // Just undone, and not read since: there is nothing of a first read to take back.
        if ($feed->baseline_batch === null || $feed->pause_reason === EventFeed::PAUSED_UNDO || ! $feed->items()->exists()) {
            return false;
        }

        // While the first read is still going it can be taken back, and for a day after it
        // ended. The importer ends a first read within a couple of days whatever is left
        // (FeedImporter::FIRST_READ_DAYS), so "still going" cannot last for months.
        return $feed->baseline_done_at === null || $feed->baseline_done_at->gt(now()->subHours(self::UNDO_HOURS));
    }

    /**
     * Take back a first read: the events it made go, unless somebody has signed up for one or
     * the owner has already worked on it, and the feed is left paused. For a day, for the case
     * the first read exists to be undone for: the wrong address, or the wrong clock.
     *
     * A true start over. What was removed is forgotten, and the first read is open again, so a
     * feed resumed after the setting is put right reads as it did the first time: published or
     * held as its setting says, with the first read's mark, and with nothing said to followers.
     * (Kept as "removed" items, every event came back as a draft whatever the setting, and
     * publishing them announced each one.)
     *
     * @return array{removed: int, kept: int}
     */
    public function undoFirstRead(EventFeed $feed, User $by): array
    {
        $removed = $kept = 0;

        if (! $this->canUndoFirstRead($feed)) {
            return ['removed' => 0, 'kept' => 0];
        }

        $this->pause($feed);
        $feed->forceFill(['pause_reason' => EventFeed::PAUSED_UNDO])->save();

        $items = $feed->items()
            ->whereIn('state', [EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE])
            ->whereHas('event', fn ($query) => $query->where('import_batch', $feed->baseline_batch)->where('import_source', Event::IMPORT_FEED))
            ->with('event')
            ->get();

        foreach ($items as $item) {
            $event = $item->event;

            if ($this->writer->hasPeople($event) || $this->writer->hasOwnersWork($event, $item)) {
                $kept++;

                continue;
            }

            $item->delete();
            $this->lifecycle->delete($event, $by->id);
            $removed++;
        }

        // What was matched, not yet made, or removed by the feed is forgotten too.
        $feed->items()->whereIn('state', [EventFeedItem::STATE_NEW, EventFeedItem::STATE_MATCHED, EventFeedItem::STATE_REMOVED])->delete();
        $feed->forceFill([
            'etag' => null,
            'last_modified' => null,
            'held_leaving' => null,
            // The first read, again: a new mark, so that what was kept is not this read's to undo.
            'baseline_done_at' => null,
            'baseline_batch' => strtolower(\Illuminate\Support\Str::random(12)),
            'stats' => ['baseline_from' => now()->toIso8601String()] + array_diff_key($feed->stats ?? [], ['whole_at' => true, 'quick' => true, 'to_tell' => true, 'continues_tomorrow' => true]),
        ])->save();
        $this->recount($feed);

        return ['removed' => $removed, 'kept' => $kept];
    }

    private function recount(EventFeed $feed): void
    {
        $this->importer->count($feed);
        $feed->save();
    }
}
