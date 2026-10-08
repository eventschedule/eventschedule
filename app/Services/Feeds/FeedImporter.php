<?php

namespace App\Services\Feeds;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\EventLifecycleService;
use App\Services\Feeds\Readers\FeedReader;
use App\Services\ScheduleEventMatcher;
use Carbon\Carbon;

/**
 * One read of one feed, start to finish, inside the time it is given.
 *
 *   1. The list is fetched (politely: FeedFetcher) and read (a reader per kind). An answer that
 *      cannot be read is a failed read, never an empty feed.
 *   2. The LEDGER takes it in: an item per thing the source shows, found by the source's own
 *      id. The ledger, not the list, is what the rest works from, so a read that runs out of
 *      time loses nothing and the next one carries on.
 *   3. Items whose list does not say when they are (a feed of posts, a provider) have their
 *      own page read, a few at a time.
 *   4. New items become events, unless the schedule already has that event, in which case the
 *      item stands beside it and never writes it. Items with an event are brought up to date
 *      (FeedEventWriter: your edits win).
 *   5. What the source cancelled, and what is gone from it, is acted on as the feed was told to,
 *      and never without the owner when people are signed up or the event carries their work.
 *   6. Pictures, and what the owner asked to be published, in what time is left.
 *
 * An event is only ever GONE when the source could have told us it was there: the read was
 * complete, this kind of source lists everything it has, the event is still to come, and it
 * was missing twice running. And when a read would take a large share of a feed's coming
 * events at once, none of it is done: that is a source having a bad day far more often than
 * forty cancellations, and it lets go by itself when the events return.
 */
class FeedImporter
{
    /** A read that would take more than this share of a feed's coming events is held whole. */
    public const BREAKER_SHARE = 0.3;

    /** ...when that is at least this many: one of two events going is not a bad day. */
    public const BREAKER_AT_LEAST = 5;

    /** How many reads running an event has to be missing before it is gone. */
    public const STRIKES = 2;

    /** The share of the day's allowance a feed leaves for events added by hand. */
    public const LEAVE_FREE = 0.2;

    /** Where there is no daily cap (selfhost), a feed still makes at most this many a day. */
    public const PER_DAY = 500;

    /** Pages of one list, and item pages, read in one run. */
    private const MAX_PAGES = 20;

    private const DETAILS_PER_RUN = 25;

    private const PICTURES_PER_RUN = 10;

    /** After this long without one good read, a feed is paused. */
    public const PAUSE_AFTER_DAYS = 14;

    private float $deadline = 0.0;

    public function __construct(
        private FeedFetcher $fetcher,
        private FeedEventWriter $writer,
        private FeedVenueResolver $venues,
        private EventLifecycleService $lifecycle,
        private FeedNotifier $notifier,
    ) {}

    /**
     * @param  float  $deadline  microtime(true) at which the read has to have stopped starting
     *                           new work.
     * @return array{status: string, created?: int, updated?: int, matched?: int, held?: int}
     */
    public function read(EventFeed $feed, float $deadline): array
    {
        $this->deadline = $deadline;
        $this->inThisRead = [];
        $this->raised = [];
        $this->unfinished = false;
        $role = Role::find($feed->role_id);
        $owner = $role && $role->user_id ? User::find($role->user_id) : null;

        // Nothing is read, and nothing counted against the feed, while it has no schedule to
        // read into, nobody to make events as, or a plan that does not include it.
        if (! $role || $role->is_deleted || ! $owner || ! EventFeed::allowedFor($role) || $feed->isPaused()) {
            return ['status' => 'idle'];
        }

        $fetched = $this->fetcher->get($feed->url, $feed->etag, $feed->last_modified);

        if (! $fetched->ok() && ! $fetched->unchanged()) {
            return $this->failed($feed, $fetched->status, $fetched->httpStatus, $fetched->retryAfter);
        }

        $reader = FeedKind::reader($feed->kind);
        $now = now();
        $rows = [];
        $counts = ['created' => 0, 'updated' => 0, 'matched' => 0, 'held' => 0, 'left_out' => 0];

        if ($fetched->ok()) {
            $reading = $this->wholeList($reader, $feed, $role, $fetched);

            if (! $reading) {
                return $this->failed($feed, 'not_readable', $fetched->httpStatus, null);
            }

            $rows = $this->absorb($feed, $role, $reading, $now);
            $counts['held'] = $this->leaving($feed, $role, $reading, $now);

            // The validators are only worth sending next time if this read took everything in.
            $feed->etag = $reading->complete ? $fetched->etag : null;
            $feed->last_modified = $reading->complete ? $fetched->lastModified : null;
        }

        $rows += $this->details($reader, $feed, $role, $now, $counts);
        $this->write($feed, $role, $owner, $rows, $counts);
        $this->pictures($feed);
        $this->publish($feed, $role);

        $firstRead = $feed->baseline_done_at === null;
        if ($firstRead && ! $feed->items()->where('state', EventFeedItem::STATE_NEW)->whereNull('pending->left_out')->exists()) {
            $feed->baseline_done_at = $now;
        }

        $this->count($feed);
        // Work left over (out of time, a venue or a picture for the next run) is picked up on
        // the next run, not in an hour. The list is asked for whole again then: a calendar's
        // rows are only in hand while it is being read, and "nothing changed" would lose them.
        if ($this->unfinished) {
            $feed->etag = null;
            $feed->last_modified = null;
        }

        $feed->forceFill([
            'last_checked_at' => $now,
            'last_success_at' => $now,
            'last_status' => FeedFetcher::OK,
            'failure_count' => 0,
            // About an hour, and not all feeds on the same minute.
            'next_check_at' => $this->unfinished && ! ($feed->stats['continues_tomorrow'] ?? false)
                ? $now->copy()->addMinute()
                : $now->copy()->addMinutes(random_int(55, 65)),
            'stats' => $this->remember($feed, $role, $owner, $now, $counts),
        ])->save();

        $this->notifier->afterRead($feed, $role, $counts, $this->unfinished, $this->raised);

        return ['status' => FeedFetcher::OK] + $counts;
    }

    /** The list, every page of it. Null when the first page is not one this kind of feed reads. */
    private function wholeList(FeedReader $reader, EventFeed $feed, Role $role, FetchResult $fetched): ?FeedReading
    {
        $keepLocalClock = ! $role->isVenue();
        $reading = $reader->read($fetched, $feed->source_timezone, $keepLocalClock);

        if (! $reading) {
            return null;
        }

        $items = $reading->items;
        $seen = $reading->seen;
        $complete = $reading->complete;
        $next = $reading->next;
        $asked = [$feed->url => true];

        for ($page = 1; $next !== null; $page++) {
            // A feed that pages forever, or back to where it began, is not walked to the end.
            if ($page >= self::MAX_PAGES || isset($asked[$next]) || ! $this->hasTime()) {
                $complete = false;

                break;
            }
            $asked[$next] = true;

            $more = $this->fetcher->get($next);
            $read = $more->ok() ? $reader->read($more, $feed->source_timezone, $keepLocalClock) : null;

            if (! $read) {
                $complete = false;

                break;
            }

            array_push($items, ...$read->items);
            $seen += $read->seen;
            $complete = $complete && $read->complete;
            $next = $read->next;
        }

        return new FeedReading($items, $seen, $complete, $reading->listsEverything, null, $reading->skipped);
    }

    /**
     * Take a reading into the ledger.
     *
     * @return array<int, array> The rows that came with the list, by item id, for the write.
     */
    private function absorb(EventFeed $feed, Role $role, FeedReading $reading, Carbon $now): array
    {
        $existing = $feed->items()->get()->keyBy('external_key');
        $rows = [];

        foreach ($reading->items as $entry) {
            $key = EventFeedItem::keyFor($entry['id']);
            $item = $existing[$key] ?? new EventFeedItem([
                'event_feed_id' => $feed->id,
                'external_key' => $key,
                'external_id' => $entry['id'],
                'state' => EventFeedItem::STATE_NEW,
                'first_seen_at' => $now,
            ]);

            $changed = $item->list_hash !== $entry['list_hash'];

            $item->forceFill([
                'last_seen_at' => $now,
                'missing_reads' => 0,
                'list_hash' => $entry['list_hash'],
                'detail_url' => $entry['detail_url'],
            ]);

            if ($entry['row'] !== null) {
                $item->starts_at = FeedTime::startOf($entry['row'], $feed->source_timezone, $role->captureTimezone());
            } elseif ($changed) {
                // What the list says about it changed: its own page is read again.
                $item->detail_checked_at = null;
            }

            // What the list knows and the item's own page may not repeat. Kept with the item:
            // its page may be read on a later run, when the list answered "nothing changed".
            if ($entry['row'] === null && ($changed || ! isset($item->pending['hint']))) {
                $item->pending = ['hint' => $entry['hint']] + ($item->pending ?? []);
            }

            $item->save();
            $existing[$key] = $item;
            $this->inThisRead[] = $item->id;

            if ($entry['row'] !== null) {
                $rows[$item->id] = $entry['row'];
            }
        }

        // Still in the source, and not something to add: over, further off than is taken,
        // private, unreadable, or called off there.
        foreach ($reading->seen as $id => $reason) {
            $item = $existing[EventFeedItem::keyFor((string) $id)] ?? null;

            if (! $item) {
                continue;
            }

            $item->forceFill(['last_seen_at' => $now, 'missing_reads' => 0])->save();
            $this->inThisRead[] = $item->id;

            if ($reason === 'cancelled') {
                $this->cancelledAtSource($feed, $item);
            }
        }

        return $rows;
    }

    /** @var list<int> The items this read found in the source, listed or not. */
    private array $inThisRead = [];

    /** Whether the read stopped with work still to do, which the next run should not wait an hour for. */
    private bool $unfinished = false;

    /** @var list<int> The items that became a decision in this read: what the owner is told about. */
    private array $raised = [];

    /**
     * What is no longer in the source. Returns how many events the breaker is holding.
     */
    private function leaving(EventFeed $feed, Role $role, FeedReading $reading, Carbon $now): int
    {
        // A read that stopped early, or a source that lists its newest few, cannot say that
        // anything is gone.
        if (! $reading->complete || ! $reading->listsEverything) {
            return 0;
        }

        $missing = $feed->items()
            ->whereIn('state', [EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE])
            ->whereNotNull('event_id')
            ->whereNotIn('id', $this->inThisRead)
            ->with('event')
            ->get()
            // Only what is still to come: an event that has happened is not un-happened.
            ->filter(fn (EventFeedItem $item) => $item->event && $item->event->starts_at && $item->event->starts_at > $now->copy()->utc()->format('Y-m-d H:i:s'));

        $gone = collect();

        foreach ($missing as $item) {
            $item->increment('missing_reads');

            if ($item->missing_reads >= self::STRIKES && ! $this->listedElsewhere($feed, $item)) {
                $gone->push($item);
            }
        }

        if ($gone->isEmpty()) {
            $feed->held_leaving = null;

            return 0;
        }

        $coming = $feed->items()
            ->where('state', EventFeedItem::STATE_IMPORTED)
            ->whereNotNull('event_id')
            ->whereHas('event', fn ($query) => $query->where('starts_at', '>', $now->copy()->utc()->format('Y-m-d H:i:s')))
            ->count();

        // Only what has not been acted on yet counts towards a bad day: an event already
        // cancelled or awaiting a decision is not going again.
        $fresh = $gone->filter(fn (EventFeedItem $item) => $this->wouldAct($feed, $item));

        if ($fresh->count() >= self::BREAKER_AT_LEAST && $fresh->count() > $coming * self::BREAKER_SHARE) {
            $feed->held_leaving = $fresh->pluck('id')->all();

            return $fresh->count();
        }

        $feed->held_leaving = null;

        foreach ($fresh as $item) {
            $this->gone($feed, $item);
        }

        return 0;
    }

    /** Another feed of the same schedule still shows the same event: it has not gone anywhere. */
    private function listedElsewhere(EventFeed $feed, EventFeedItem $item): bool
    {
        return EventFeedItem::where('event_id', $item->event_id)
            ->where('event_feed_id', '!=', $feed->id)
            ->where('missing_reads', 0)
            ->whereIn('event_feed_id', EventFeed::where('role_id', $feed->role_id)->whereNull('paused_at')->select('id'))
            ->exists();
    }

    private function wouldAct(EventFeed $feed, EventFeedItem $item): bool
    {
        if ($feed->left_action === EventFeed::LEFT_KEEP || $item->state !== EventFeedItem::STATE_IMPORTED) {
            return false;
        }

        return ! ($feed->left_action === EventFeed::LEFT_CANCEL && $item->event->is_cancelled);
    }

    /** An event that is gone from the source, done with as the feed was told. */
    private function gone(EventFeed $feed, EventFeedItem $item): void
    {
        $event = $item->event;

        if ($feed->left_action === EventFeed::LEFT_CANCEL) {
            if ($this->writer->hasPeople($event)) {
                $this->decide($item, ['kind' => 'gone']);
            } else {
                $this->lifecycle->cancel($event, null, audit: false);
                $item->forceFill(['cancelled_by_feed' => true])->save();
            }

            return;
        }

        // Deleting takes with it whatever hangs on the event, so it is only for one that is the
        // feed's alone.
        if ($this->writer->hasPeople($event) || $this->writer->hasOwnersWork($event, $item)) {
            $this->decide($item, ['kind' => 'gone']);

            return;
        }

        $this->lifecycle->delete($event, null, audit: false);
        // "removed", not "dismissed": the owner did not delete it, and a source that lists it
        // again brings it back.
        $item->forceFill(['state' => EventFeedItem::STATE_REMOVED, 'event_id' => null, 'imported' => null, 'pending' => null])->save();
    }

    /** The source says the event is called off. */
    private function cancelledAtSource(EventFeed $feed, EventFeedItem $item): void
    {
        $event = $item->event_id ? Event::find($item->event_id) : null;

        if (! $event || $item->state !== EventFeedItem::STATE_IMPORTED || $event->is_cancelled) {
            return;
        }

        // We cancelled it and it is on again: the owner restored it, and it stays as they put it.
        if ($item->cancelled_by_feed) {
            return;
        }

        if ($this->writer->hasPeople($event)) {
            $this->decide($item, ['kind' => 'cancelled']);

            return;
        }

        $this->lifecycle->cancel($event, null, audit: false);
        $item->forceFill(['cancelled_by_feed' => true])->save();
    }

    /** Leave it to the owner, unless they have already decided about exactly this. */
    private function decide(EventFeedItem $item, array $decision): void
    {
        if ($item->decided_hash === FeedEventWriter::hashOf($decision)) {
            return;
        }

        // New to the owner, so they are told. Both callers come here only for an item that is
        // not already waiting (gone() and cancelledAtSource() act on `imported` alone), which is
        // what keeps a read that finds it still waiting from telling anybody again.
        $this->raised[] = $item->id;

        $item->forceFill([
            'state' => EventFeedItem::STATE_DECIDE,
            'pending' => ['decide' => $decision] + ($item->pending ?? []),
        ])->save();
    }

    /**
     * The pages of items whose list does not say when they are.
     *
     * @return array<int, array> Rows, by item id.
     */
    private function details(FeedReader $reader, EventFeed $feed, Role $role, Carbon $now, array &$counts): array
    {
        $due = $feed->items()
            ->whereNotNull('detail_url')
            ->whereIn('state', [EventFeedItem::STATE_NEW, EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE, EventFeedItem::STATE_REMOVED])
            ->where(function ($query) use ($now) {
                // Never read, or changed in the list; or read more than a day ago and still to come.
                $query->whereNull('detail_checked_at')->orWhere(function ($again) use ($now) {
                    $again->where('detail_checked_at', '<', $now->copy()->subDay())
                        ->where(fn ($when) => $when->whereNull('starts_at')->orWhere('starts_at', '>', $now))
                        ->whereNull('pending->left_out');
                });
            })
            ->orderByRaw('detail_checked_at IS NULL DESC')
            ->orderBy('detail_checked_at')
            ->limit(self::DETAILS_PER_RUN)
            ->get();

        $host = strtolower((string) parse_url($feed->url, PHP_URL_HOST));
        $rows = [];
        // A full batch means there may be more waiting behind it.
        $this->unfinished = $this->unfinished || $due->count() >= self::DETAILS_PER_RUN;

        foreach ($due as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            // A provider's posts are only ever asked for on the host its feed is on.
            if ($feed->kind === EventFeed::KIND_JOLIOO && strtolower((string) parse_url($item->detail_url, PHP_URL_HOST)) !== $host) {
                continue;
            }

            $fetched = $this->fetcher->get($item->detail_url);
            $item->detail_checked_at = $now;

            if (! $fetched->ok()) {
                // Its own address says it is gone. Twice, like everything else.
                if (in_array($fetched->httpStatus, [404, 410], true) && $item->event_id && $item->state === EventFeedItem::STATE_IMPORTED) {
                    $item->missing_reads++;
                    $item->save();

                    $item->setRelation('event', Event::find($item->event_id));

                    if ($item->missing_reads >= self::STRIKES && $item->event && $this->wouldAct($feed, $item) && ! $this->listedElsewhere($feed, $item)) {
                        $this->gone($feed, $item);
                    }

                    continue;
                }

                // Anything else is a bad moment: it is asked again on a later read.
                $item->detail_checked_at = null;
                $item->save();

                continue;
            }

            $found = $reader->detail(
                ['id' => $item->external_id, 'detail_url' => $item->detail_url, 'hint' => $item->pending['hint'] ?? []],
                $fetched, $feed->source_timezone, ! $role->isVenue()
            );

            $item->missing_reads = 0;

            if (is_array($found)) {
                $item->starts_at = FeedTime::startOf($found, $feed->source_timezone, $role->captureTimezone());
                $item->pending = array_diff_key($item->pending ?? [], ['left_out' => true]) ?: null;
                $item->save();
                $rows[$item->id] = $found;

                continue;
            }

            if ($found === 'cancelled') {
                $item->save();
                $this->cancelledAtSource($feed, $item);

                continue;
            }

            // Not an event, an event with no date, over, or too far off: not something to add.
            if ($item->state === EventFeedItem::STATE_NEW) {
                $item->pending = ['left_out' => $found] + ($item->pending ?? []);
                $counts['left_out']++;
            }
            $item->save();
        }

        return $rows;
    }

    /** New items become events; items with an event are brought up to date. */
    private function write(EventFeed $feed, Role $role, User $owner, array $rows, array &$counts): void
    {
        if (! $rows) {
            return;
        }

        $items = $feed->items()->whereIn('id', array_keys($rows))->with('event')->get()->sortBy(fn (EventFeedItem $item) => $item->starts_at)->values();
        $matcher = null;
        $known = $feed->stats['venues'] ?? [];
        $room = $this->room($role, $owner);
        $firstRead = $feed->baseline_done_at === null;

        foreach ($items as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            $row = $rows[$item->id];

            // A link and nothing more, or the owner's "not this one": never written.
            if (in_array($item->state, [EventFeedItem::STATE_MATCHED, EventFeedItem::STATE_SKIPPED, EventFeedItem::STATE_DISMISSED], true)) {
                continue;
            }

            if ($item->event) {
                if ($item->cancelled_by_feed && $item->event->is_cancelled) {
                    // Called off at the source, and on again there.
                    $this->lifecycle->restore($item->event, null, audit: false);
                    $item->forceFill(['cancelled_by_feed' => false])->save();
                }

                $venue = $this->venues->resolve($feed, $role, $row, $known);
                $wanted = $this->writer->wanted($feed, $role, $row, $venue['deferred'] ? $this->venueOf($item) : $venue['venue']);
                $result = $this->writer->update($feed, $role, $item, $item->event, $wanted);
                $counts['updated'] += $result['written'] ? 1 : 0;
                if ($result['raised']) {
                    $this->raised[] = $item->id;
                }
                $this->notePicture($item, $wanted['flyer']);

                continue;
            }

            // Already on the schedule, made by hand or by another import: stand beside it.
            $matcher ??= new ScheduleEventMatcher($role, $feed->source_timezone);
            if ($item->state === EventFeedItem::STATE_NEW && ($matched = $matcher->match($row, FeedTime::zoneOf($row, $feed->source_timezone, $role->captureTimezone())))) {
                $item->forceFill(['state' => EventFeedItem::STATE_MATCHED, 'event_id' => $matched])->save();
                $counts['matched']++;

                continue;
            }

            if ($room <= 0) {
                $feed->stats = ['continues_tomorrow' => true] + ($feed->stats ?? []);
                $this->unfinished = true;

                continue;
            }

            $venue = $this->venues->resolve($feed, $role, $row, $known);
            if ($venue['deferred']) {
                $this->unfinished = true;

                continue;
            }

            // An event the feed made that went some other way than the owner deleting it
            // (state still "imported", or "removed" by the feed and listed again) comes back
            // for them to look at, whatever the feed does with new events.
            $returning = $item->state !== EventFeedItem::STATE_NEW;
            $wanted = $this->writer->wanted($feed, $role, $row, $venue['venue']);
            $event = $this->writer->create($feed, $role, $owner, $item, $wanted, $firstRead ? $feed->baseline_batch : null);

            if ($returning && ! $event->is_draft) {
                $event->forceFill(['is_draft' => true])->save();
            }

            $counts['created']++;
            $room--;
        }

        $feed->stats = ['venues' => $known] + ($feed->stats ?? []);
    }

    private function venueOf(EventFeedItem $item): ?Role
    {
        $id = $item->imported['row']['venue_id'] ?? null;

        return $id ? Role::find($id) : null;
    }

    private function notePicture(EventFeedItem $item, ?string $source): void
    {
        if (! FeedEventWriter::same($source, $item->imported['src']['flyer'] ?? null)) {
            $item->forceFill(['image_pending' => true, 'image_source' => $source])->save();
        }
    }

    /**
     * How many more events this run may make. On the hosted service, what is left of the day's
     * cap less the share kept free for events added by hand; where there is no cap, what is
     * left of PER_DAY. Null is never returned for a feed: it always has a bound.
     */
    private function room(Role $role, User $owner): int
    {
        $left = $role->eventCreateAllowance($owner);

        if ($left !== null) {
            return max(0, $left - (int) ceil((int) $role->eventCreateDailyLimit() * self::LEAVE_FREE));
        }

        $made = Event::where('creator_role_id', $role->id)
            ->where('import_source', Event::IMPORT_FEED)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        return max(0, self::PER_DAY - $made);
    }

    private function pictures(EventFeed $feed): void
    {
        $waiting = $feed->items()->where('image_pending', true)->whereNotNull('event_id')->with('event')->limit(self::PICTURES_PER_RUN)->get();

        $this->unfinished = $this->unfinished || $waiting->count() >= self::PICTURES_PER_RUN;

        foreach ($waiting as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            if (! $item->event || $this->writer->flyer($item, $item->event, $item->image_source)) {
                $item->forceFill(['image_pending' => false])->save();
            }
        }
    }

    /** What the owner asked to be published ("Publish all"), a few at a time. An hour later the asking has lapsed. */
    private function publish(EventFeed $feed, Role $role): void
    {
        $feed->items()->whereNotNull('publish_requested_at')->where('publish_requested_at', '<', now()->subHour())->update(['publish_requested_at' => null]);

        foreach ($feed->items()->whereNotNull('publish_requested_at')->whereNotNull('event_id')->with('event')->orderBy('starts_at')->get() as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            if ($item->event && $item->event->is_draft) {
                $this->lifecycle->publish($item->event, $feed->added_by, $role);
            }

            $item->forceFill(['publish_requested_at' => null])->save();
        }
    }

    /**
     * Forget what no longer matters. The ledger is what stops an event being made twice, so an
     * item is kept for as long as its source could still show it to us:
     *
     *  - never while the event it made has not ended;
     *  - one the owner dismissed, until thirty days after its own start (the source may go on
     *    listing it until then, and forgetting it sooner would bring the event back);
     *  - anything else, thirty days after its event ended, or ninety days after the source last
     *    showed it.
     *
     * @return int How many items were forgotten.
     */
    public function prune(): int
    {
        $now = now();

        $dismissed = EventFeedItem::where('state', EventFeedItem::STATE_DISMISSED)
            ->where(fn ($query) => $query
                ->where('starts_at', '<', $now->copy()->subDays(30))
                ->orWhere(fn ($undated) => $undated->whereNull('starts_at')->where('last_seen_at', '<', $now->copy()->subDays(90))))
            ->delete();

        $rest = EventFeedItem::where('state', '!=', EventFeedItem::STATE_DISMISSED)
            ->where(fn ($query) => $query
                ->where('starts_at', '<', $now->copy()->subDays(31))
                ->orWhere(fn ($unseen) => $unseen->where('last_seen_at', '<', $now->copy()->subDays(90))))
            // An event with a length of days, or one the owner moved, may still be on.
            ->whereNotExists(fn ($event) => $event->from('events')
                ->whereColumn('events.id', 'event_feed_items.event_id')
                ->where(fn ($on) => $on
                    ->where('events.starts_at', '>=', $now->copy()->subDay())
                    ->orWhereRaw('DATE_ADD(events.starts_at, INTERVAL COALESCE(events.duration, 0) HOUR) >= ?', [$now->copy()->subDay()])))
            ->delete();

        return $dismissed + $rest;
    }

    /** What the tab's badge shows: events waiting to be looked over, and decisions. */
    public function count(EventFeed $feed): void
    {
        $feed->waiting_count = $feed->items()
            ->where('state', EventFeedItem::STATE_IMPORTED)
            ->whereHas('event', fn ($query) => $query->where('is_draft', true))
            ->count();
        $feed->decide_count = $feed->items()->where('state', EventFeedItem::STATE_DECIDE)->count();
    }

    /** A read that did not happen: said in a key, tried again later, and paused if it goes on. */
    private function failed(EventFeed $feed, string $reason, ?int $httpStatus, ?int $retryAfter): array
    {
        $now = now();
        $failures = $feed->failure_count + 1;
        // An hour, two, six, then once a day; or as long as the server asked for, if longer.
        $wait = max([60, 120, 360][$failures - 1] ?? 1440, (int) ceil(($retryAfter ?? 0) / 60));

        $feed->forceFill([
            'last_checked_at' => $now,
            'last_status' => $reason,
            'failure_count' => min($failures, 65000),
            'next_check_at' => $now->copy()->addMinutes($wait),
            'stats' => ['last_http' => $httpStatus] + ($feed->stats ?? []),
        ]);

        // Many feeds failing at once is likelier our fault than each source's: nobody is told
        // their feed is broken and none is paused for it. The platform's admins are told instead.
        $ours = EventFeed::manyFailing();
        $since = $feed->last_success_at ?? $feed->created_at;
        $pausing = ! $ours && $since && $since->lt($now->copy()->subDays(self::PAUSE_AFTER_DAYS));

        if ($pausing) {
            $feed->forceFill(['paused_at' => $now, 'pause_reason' => EventFeed::PAUSED_FAILING]);
            $feed->items()->whereNotNull('publish_requested_at')->update(['publish_requested_at' => null]);
        }

        $feed->save();

        if (! $ours) {
            $this->notifier->afterFailure($feed, $pausing);
        }

        return ['status' => $reason];
    }

    /** The last few reads that did something, for the feed's own page. */
    private function remember(EventFeed $feed, Role $role, User $owner, Carbon $now, array $counts): array
    {
        $stats = $feed->stats ?? [];
        // A good read: the last failure's status, and that somebody was told it was failing.
        unset($stats['last_http'], $stats['failing_told']);

        if (array_sum($counts) > 0) {
            $stats['reads'] = array_slice(array_merge([['at' => $now->toIso8601String()] + array_filter($counts)], $stats['reads'] ?? []), 0, 10);
        }

        if ($this->room($role, $owner) > 0) {
            unset($stats['continues_tomorrow']);
        }

        return $stats;
    }

    private function hasTime(): bool
    {
        return microtime(true) < $this->deadline;
    }
}
