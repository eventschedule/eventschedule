<?php

namespace App\Services\Feeds;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EventLifecycleService;
use App\Services\Feeds\Readers\FeedReader;
use App\Services\ScheduleEventMatcher;
use App\Utils\ImportedTime;
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
 * complete and listed something, this kind of source lists everything it has (and for a page,
 * which may show only "the next ten", the event is no later than the last one it showed), the
 * event is still to come, it was missing twice running and has been out of sight for an hour
 * and a half, and no other entry seen in this read is that same event under a new id. And when
 * a read would take a large share of a feed's coming events at once, none of it is done: that is
 * a source having a bad day far more often than forty cancellations, and it lets go by itself
 * when the events return.
 *
 * What the OWNER did is never undone. An event the feed cancelled and they restored stays; one
 * they cancelled stays cancelled though the source lists it again; a decision they gave about an
 * absence holds for that absence.
 *
 * And a source that cannot be finished with is not hammered. A page or a picture that does not
 * answer is asked a few times and then left; the run comes back within the minute only after
 * one that got somewhere, and only so many times in a row.
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

    /**
     * ...and only after this many failed reads running. A feed resumed after a long pause has
     * not had a good read for weeks either, and one timeout is not a reason to pause it again.
     */
    public const PAUSE_AFTER_FAILURES = 10;

    /** Misses are counted while the event is still to come. Past this the count says no more. */
    public const MISSING_CAP = 200;

    /**
     * How long an entry has to have been out of sight before its misses mean it is gone. Two
     * reads a minute apart (a run that came back to finish, or Read now) are one look, not two.
     */
    public const GONE_AFTER_MINUTES = 90;

    /** An item's own page that does not answer is asked this many times, then left out. */
    public const PAGE_TRIES = 5;

    /** A picture that cannot be had is asked for this many times. */
    public const PICTURE_TRIES = 3;

    /** How many runs in a row may come back within the minute to finish a read. */
    public const QUICK_RETURNS = 30;

    /** The first read is over after this long, whatever is still waiting to be made. */
    public const FIRST_READ_DAYS = 2;

    private float $deadline = 0.0;

    public function __construct(
        private FeedFetcher $fetcher,
        private FeedEventWriter $writer,
        private FeedVenueResolver $venues,
        private EventLifecycleService $lifecycle,
        private FeedNotifier $notifier,
    ) {}

    /** Mail waits while a run holds the lock on every feed, and goes when the run lets go of it. */
    public function holdMail(): void
    {
        $this->notifier->hold();
    }

    public function sendHeldMail(): void
    {
        $this->notifier->release();
    }

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
        $this->progress = false;
        $role = Role::find($feed->role_id);
        $owner = $role && $role->user_id ? User::find($role->user_id) : null;

        // Nothing is read, and nothing counted against the feed, while it has no schedule to
        // read into, nobody to make events as, or a plan that does not include it.
        if (! $role || $role->is_deleted || ! $owner || ! EventFeed::allowedFor($role) || $feed->isPaused()) {
            return ['status' => 'idle'];
        }

        // What the owner asked to be published does not wait on the source answering.
        if ($this->publish($feed, $role) > 0) {
            $this->count($feed);
        }

        // "Has it changed since?" is only asked while a whole read is recent. A list that never
        // changes still gains the next dates of a repeating entry as the days pass, and an item
        // nobody has seen for months is forgotten, which would bring a skipped draft back.
        $wholeAt = isset($feed->stats['whole_at']) ? Carbon::parse($feed->stats['whole_at']) : null;
        $recent = $wholeAt !== null && $wholeAt->gt(now()->subDay());
        $fetched = $this->fetcher->get($feed->url, $recent ? $feed->etag : null, $recent ? $feed->last_modified : null);

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
            $wholeAt = $reading->complete ? $now : $wholeAt;
        }

        $rows += $this->details($reader, $feed, $role, $now, $counts);
        $this->write($feed, $role, $owner, $rows, $counts);
        $this->pictures($feed);

        // The first read is over when nothing this read showed is still waiting to be made. An
        // entry that left the source before it was made, or that can never be read, does not
        // hold it open: while it is open every event carries the first read's mark, which keeps
        // it out of the digest to followers and inside what Undo first read removes.
        if ($feed->baseline_done_at === null) {
            $waiting = $fetched->ok() && $feed->items()
                ->where('state', EventFeedItem::STATE_NEW)
                ->whereNull('pending->left_out')
                ->whereIn('id', $this->inThisRead)
                ->exists();

            // Counted from when the feed was added, or from when its first read was undone.
            $from = isset($feed->stats['baseline_from']) ? Carbon::parse($feed->stats['baseline_from']) : $feed->created_at;

            if (($fetched->ok() && ! $waiting) || $from->lt($now->copy()->subDays(self::FIRST_READ_DAYS))) {
                $feed->baseline_done_at = $now;
            }
        }

        $this->count($feed);
        // Work left over (out of time, a venue or a picture for the next run) is picked up on
        // the next run, not in an hour. The list is asked for whole again then: a calendar's
        // rows are only in hand while it is being read, and "nothing changed" would lose them.
        if ($this->unfinished) {
            $feed->etag = null;
            $feed->last_modified = null;
        }

        // Back within the minute only to finish what a run could not, only after a run that got
        // somewhere, and only so many times running: ten pictures that will never load are not
        // a reason to ask a source for its whole list every minute.
        $quick = $this->unfinished
            && $this->progress
            && ! ($feed->stats['continues_tomorrow'] ?? false)
            && (int) ($feed->stats['quick'] ?? 0) < self::QUICK_RETURNS;

        $feed->forceFill([
            'last_checked_at' => $now,
            'last_success_at' => $now,
            'last_status' => FeedFetcher::OK,
            'failure_count' => 0,
            // About an hour, and not all feeds on the same minute.
            'next_check_at' => $quick ? $now->copy()->addMinute() : $now->copy()->addMinutes(random_int(55, 65)),
            'stats' => $this->remember($feed, $role, $owner, $now, $counts, $wholeAt, $quick),
        ])->save();

        $this->notifier->afterRead($feed, $role, $counts, $this->unfinished, $this->raised);

        return ['status' => FeedFetcher::OK] + $counts;
    }

    /** The list, every page of it. Null when the first page is not one this kind of feed reads. */
    private function wholeList(FeedReader $reader, EventFeed $feed, Role $role, FetchResult $fetched): ?FeedReading
    {
        $keepLocalClock = ! $role->isVenue();
        // Times with no zone are read on the feed's clock; everything is placed on the schedule's.
        $read = fn (FetchResult $page) => ImportedTime::onClock(
            $role->captureTimezone(),
            fn () => $reader->read($page, $feed->source_timezone, $keepLocalClock)
        );
        $reading = $read($fetched);

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
            $further = $more->ok() ? $read($more) : null;

            if (! $further) {
                $complete = false;

                break;
            }

            array_push($items, ...$further->items);
            $seen += $further->seen;
            $complete = $complete && $further->complete;
            $next = $further->next;
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

            // Back in the source after being away. What the owner decided about its absence
            // was about that absence: if it leaves again, that is a new question.
            if ($item->exists && $item->missing_reads > 0 && $item->decided_hash === FeedEventWriter::hashOf(['kind' => 'gone'])) {
                $item->decided_hash = null;
            }

            $item->forceFill([
                'last_seen_at' => $now,
                'missing_reads' => 0,
                'list_hash' => $entry['list_hash'],
                'detail_url' => $entry['detail_url'],
            ]);

            if ($entry['row'] !== null) {
                $item->starts_at = FeedTime::startOf($entry['row'], $role->captureTimezone());
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

    /** Whether this run got anywhere: made, changed or settled something. A run that did not is not worth coming straight back for. */
    private bool $progress = false;

    /**
     * What is no longer in the source. Returns how many events the breaker is holding.
     */
    private function leaving(EventFeed $feed, Role $role, FeedReading $reading, Carbon $now): int
    {
        // A read that stopped early, or a source that lists its newest few, cannot say that
        // anything is gone. Nor can a read that showed nothing at all: a calendar that is
        // suddenly empty is a source having a bad moment far more often than every event of
        // its being called off.
        if (! $reading->complete || ! $reading->listsEverything || ! $this->inThisRead) {
            return 0;
        }

        // A page may show "the next ten", and a provider its latest: an event further off than
        // the last one shown has not left, it has not been reached. A calendar is its whole list.
        $horizon = in_array($feed->kind, [EventFeed::KIND_PAGE, EventFeed::KIND_JOLIOO], true)
            ? $feed->items()->whereIn('id', $this->inThisRead)->max('starts_at')
            : false;

        if ($horizon === null) {
            return 0;
        }

        $nowUtc = $now->copy()->utc()->format('Y-m-d H:i:s');

        $missing = $feed->items()
            ->whereIn('state', [EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE])
            ->whereNotNull('event_id')
            ->whereNotIn('id', $this->inThisRead)
            ->with('event')
            ->get()
            // Only what is still to come: an event that has happened is not un-happened.
            ->filter(fn (EventFeedItem $item) => $item->event && $item->event->starts_at && $item->event->starts_at > $nowUtc)
            ->filter(fn (EventFeedItem $item) => $horizon === false || ($item->starts_at && $item->starts_at->format('Y-m-d H:i:s') <= $horizon));

        $gone = collect();
        $longEnough = $now->copy()->subMinutes(self::GONE_AFTER_MINUTES);

        foreach ($missing as $item) {
            if ($item->missing_reads < self::MISSING_CAP) {
                $item->increment('missing_reads');
            }

            if ($item->missing_reads >= self::STRIKES
                && (! $item->last_seen_at || $item->last_seen_at->lt($longEnough))
                && ! $this->listedElsewhere($feed, $item, $now)) {
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
            ->whereHas('event', fn ($query) => $query->where('starts_at', '>', $nowUtc))
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

    /**
     * The same event is still shown: by another entry of this feed seen in this very read (a
     * source that gave it a new id), or by another feed of the schedule that has seen it within
     * the day. It has not gone anywhere.
     */
    private function listedElsewhere(EventFeed $feed, EventFeedItem $item, Carbon $now): bool
    {
        return EventFeedItem::where('event_id', $item->event_id)
            ->where('id', '!=', $item->id)
            ->where(fn ($query) => $query
                ->where(fn ($here) => $here->where('event_feed_id', $feed->id)->whereIn('id', $this->inThisRead))
                ->orWhere(fn ($there) => $there
                    ->where('event_feed_id', '!=', $feed->id)
                    ->where('last_seen_at', '>', $now->copy()->subHours(26))
                    ->whereIn('event_feed_id', EventFeed::where('role_id', $feed->role_id)->whereNull('paused_at')->select('id'))))
            ->exists();
    }

    private function wouldAct(EventFeed $feed, EventFeedItem $item): bool
    {
        if ($feed->left_action === EventFeed::LEFT_KEEP || $item->state !== EventFeedItem::STATE_IMPORTED) {
            return false;
        }

        // The feed cancelled it and it is on again: the owner restored it, and it stays as they
        // put it. Without this their restore was undone by the next read, every hour.
        if ($item->cancelled_by_feed && ! $item->event->is_cancelled) {
            return false;
        }

        // The owner has already answered for this absence.
        if ($item->decided_hash === FeedEventWriter::hashOf(['kind' => 'gone'])) {
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
                $this->cancelAsFeed($feed, $item, $event, 'no longer in the feed');
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
        AuditService::log(AuditService::EVENT_DELETE, null, 'Event', $event->id, null, null, 'feed:'.$feed->id.' no longer in the feed');
        // "removed", not "dismissed": the owner did not delete it, and a source that lists it
        // again brings it back.
        $item->forceFill(['state' => EventFeedItem::STATE_REMOVED, 'event_id' => null, 'imported' => null, 'pending' => null])->save();
    }

    /**
     * Cancel as the feed, and remember that this cancellation is the feed's: the stamp it left
     * on the event. Only a cancellation that is still this one is ever taken back.
     */
    private function cancelAsFeed(EventFeed $feed, EventFeedItem $item, Event $event, string $why): void
    {
        $this->lifecycle->cancel($event, null, audit: false);
        AuditService::log(AuditService::EVENT_CANCEL, null, 'Event', $event->id, null, null, 'feed:'.$feed->id.' '.$why);
        $item->forceFill(['cancelled_by_feed' => true, 'feed_cancelled_at' => $event->fresh()?->cancelled_at])->save();
    }

    /** The source says the event is called off. */
    private function cancelledAtSource(EventFeed $feed, EventFeedItem $item): void
    {
        $event = $item->event_id ? Event::find($item->event_id) : null;

        if (! $event || $item->state !== EventFeedItem::STATE_IMPORTED || $event->is_cancelled) {
            return;
        }

        // We cancelled it and it is on again: the owner restored it, and it stays as they put
        // it. Nor is it asked about twice.
        if ($item->cancelled_by_feed || $item->decided_hash === FeedEventWriter::hashOf(['kind' => 'cancelled'])) {
            return;
        }

        if ($this->writer->hasPeople($event)) {
            $this->decide($item, ['kind' => 'cancelled']);

            return;
        }

        $this->cancelAsFeed($feed, $item, $event, 'cancelled at the source');
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
                // Never read, or changed in the list; or read more than a day ago and still to
                // come; or a new one whose page did not answer, asked again within the hour for
                // a few tries, so that a first read is not held open for days by one dead link.
                $query->whereNull('detail_checked_at')
                    ->orWhere(function ($again) use ($now) {
                        $again->where('detail_checked_at', '<', $now->copy()->subDay())
                            ->where(fn ($when) => $when->whereNull('starts_at')->orWhere('starts_at', '>', $now))
                            ->whereNull('pending->left_out');
                    })
                    ->orWhere(function ($retry) use ($now) {
                        $retry->where('state', EventFeedItem::STATE_NEW)
                            ->where('page_tries', '>', 0)
                            ->where('detail_checked_at', '<', $now->copy()->subMinutes(50))
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

        // Half of what time is left, so that there is some to write what the pages said.
        $until = microtime(true) + max(0.0, $this->deadline - microtime(true)) / 2;

        foreach ($due as $item) {
            if (microtime(true) >= $until) {
                $this->unfinished = true;

                break;
            }

            // A provider's posts are only ever asked for on the host its feed is on. One that
            // is elsewhere is not asked again on every run either.
            if ($feed->kind === EventFeed::KIND_JOLIOO && strtolower((string) parse_url($item->detail_url, PHP_URL_HOST)) !== $host) {
                $this->leaveOut($item, $now, 'unreadable', $counts);

                continue;
            }

            $fetched = $this->fetcher->get($item->detail_url);
            $item->detail_checked_at = $now;

            if (! $fetched->ok()) {
                // Its own address says it is gone. Twice, like everything else, and not within
                // the same hour.
                if (in_array($fetched->httpStatus, [404, 410], true) && $item->event_id && $item->state === EventFeedItem::STATE_IMPORTED) {
                    $item->missing_reads = min($item->missing_reads + 1, self::MISSING_CAP);
                    $item->save();

                    $item->setRelation('event', Event::find($item->event_id));

                    if ($item->missing_reads >= self::STRIKES
                        && $item->event
                        && $item->event->starts_at > $now->copy()->utc()->format('Y-m-d H:i:s')
                        && $this->wouldAct($feed, $item)
                        && ! $this->listedElsewhere($feed, $item, $now)) {
                        $this->gone($feed, $item);
                    }

                    continue;
                }

                // Anything else is a bad moment. It is stamped as tried, so that it goes to the
                // back of the line and is asked again later rather than on every run; and a new
                // item that never answers is left out after a few tries.
                $item->page_tries = min($item->page_tries + 1, 250);

                if ($item->state === EventFeedItem::STATE_NEW && $item->page_tries >= self::PAGE_TRIES) {
                    $this->leaveOut($item, $now, 'unreadable', $counts);
                } else {
                    $item->save();
                }

                // "Too many requests", or "not now": the rest of this run's pages can wait.
                if (in_array($fetched->httpStatus, [429, 503], true)) {
                    break;
                }

                continue;
            }

            $this->progress = true;
            $found = ImportedTime::onClock($role->captureTimezone(), fn () => $reader->detail(
                ['id' => $item->external_id, 'detail_url' => $item->detail_url, 'hint' => $item->pending['hint'] ?? []],
                $fetched, $feed->source_timezone, ! $role->isVenue()
            ));

            $item->missing_reads = 0;
            $item->page_tries = 0;

            if (is_array($found)) {
                $item->starts_at = FeedTime::startOf($found, $role->captureTimezone());
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

    /** A new item that is not going to become an event, said once and not asked about again. */
    private function leaveOut(EventFeedItem $item, Carbon $now, string $why, array &$counts): void
    {
        $item->detail_checked_at = $now;

        if ($item->state === EventFeedItem::STATE_NEW && ! isset($item->pending['left_out'])) {
            $item->pending = ['left_out' => $why] + ($item->pending ?? []);
            $counts['left_out']++;
        }

        $item->save();
    }

    /** New items become events; items with an event are brought up to date. */
    private function write(EventFeed $feed, Role $role, User $owner, array $rows, array &$counts): void
    {
        if (! $rows) {
            return;
        }

        $items = $feed->items()->whereIn('id', array_keys($rows))->with('event')->get()->sortBy(fn (EventFeedItem $item) => $item->starts_at)->values();
        // Rows are on the schedule's clock (ImportedTime::onClock()), and so is the matcher.
        $matcher = null;
        $known = $feed->stats['venues'] ?? [];
        $room = $this->room($role, $owner);
        $firstRead = $feed->baseline_done_at === null;

        $reached = [];

        foreach ($items as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            $reached[] = $item->id;
            $row = $rows[$item->id];

            // A link and nothing more, or the owner's "not this one": never written.
            if (in_array($item->state, [EventFeedItem::STATE_MATCHED, EventFeedItem::STATE_SKIPPED, EventFeedItem::STATE_DISMISSED], true)) {
                continue;
            }

            // One entry that cannot be saved (text that is not valid UTF-8, a region of three
            // hundred characters, a picture the disk refuses) is left out. It does not stop
            // the read for every other entry, on this run and on every run after.
            try {
                $made = $this->writeOne($feed, $role, $owner, $item, $row, $matcher, $known, $room, $firstRead, $counts);
            } catch (\Throwable $e) {
                report($e);
                $made = true;

                if ($item->exists && $item->state === EventFeedItem::STATE_NEW && ! $item->event_id) {
                    $item->forceFill(['pending' => ['left_out' => 'failed'] + ($item->pending ?? [])])->save();
                    $counts['left_out']++;
                }
            }

            // The feed was removed or paused while this run was going (Remove, Undo first read,
            // a change of hands): what it makes from here on would belong to no feed.
            if ($made === false) {
                $this->unfinished = false;

                break;
            }
        }

        // A row that came from an item's own page is only in hand for this run. One the run did
        // not get to is asked for again on the next, not in a day: with slow pages and many
        // posts, every run read pages until its time was up and none ever wrote a thing.
        if ($unreached = array_diff(array_keys($rows), $reached)) {
            $feed->items()->whereIn('id', $unreached)->whereNotNull('detail_url')->update(['detail_checked_at' => null]);
        }

        // The places a feed has named, so that each is looked up once. The most recent few
        // hundred: a feed that names a new place in every entry does not grow this for ever.
        $feed->stats = ['venues' => array_slice($known, -300, null, true)] + ($feed->stats ?? []);
    }

    /**
     * One row: brought up to date, linked, or made.
     *
     * @return bool False when the feed is no longer there to write for.
     */
    private function writeOne(EventFeed $feed, Role $role, User $owner, EventFeedItem $item, array $row, ?ScheduleEventMatcher &$matcher, array &$known, int &$room, bool $firstRead, array &$counts): bool
    {
        // Already on the schedule?
        if ($item->state === EventFeedItem::STATE_NEW && ! $item->event) {
            $matcher ??= new ScheduleEventMatcher($role, $role->captureTimezone());

            if ($matched = $matcher->match($row)) {
                $holder = $feed->items()
                    ->where('event_id', $matched)
                    ->where('id', '!=', $item->id)
                    ->whereIn('state', [EventFeedItem::STATE_IMPORTED, EventFeedItem::STATE_DECIDE])
                    ->whereNotIn('id', $this->inThisRead)
                    ->with('event')
                    ->first();

                if (! $holder) {
                    // Made by hand, by another import, or still listed under its other id:
                    // stand beside it, and never write it.
                    $item->forceFill(['state' => EventFeedItem::STATE_MATCHED, 'event_id' => $matched])->save();
                    $counts['matched']++;
                    $this->progress = true;

                    return true;
                }

                // The feed's own event, under a new id: the entry it was made from is no longer
                // shown and this one is it. The old item takes the new id and carries on, with
                // everything it knows of what the owner changed. Left as two items, the new one
                // only stood beside the event while the old one went missing, and the event was
                // cancelled or deleted with the source still listing it.
                $item = $this->takeOver($holder, $item);
            }
        }

        if ($item->event) {
            if ($item->cancelled_by_feed && $item->event->is_cancelled) {
                // Called off by the feed, and shown again. Put back only while that cancellation
                // is still the feed's own: one the owner restored and then cancelled themselves
                // is theirs, and so is one with people on it, who may have been told.
                $ours = $item->feed_cancelled_at && $item->event->cancelled_at && $item->feed_cancelled_at->equalTo($item->event->cancelled_at);

                if ($ours && ! $this->writer->hasPeople($item->event)) {
                    $this->lifecycle->restore($item->event, null, audit: false);
                    AuditService::log(AuditService::EVENT_UPDATE, null, 'Event', $item->event->id, null, null, 'feed:'.$feed->id.' restored: listed again');
                }

                $item->forceFill(['cancelled_by_feed' => false, 'feed_cancelled_at' => null])->save();
            }

            $venue = $this->venues->resolve($feed, $role, $row, $known);
            $wanted = $this->writer->wanted($feed, $role, $row, $venue['deferred'] ? $this->venueOf($item) : $venue['venue']);
            $result = $this->writer->update($feed, $role, $item, $item->event, $wanted);
            $counts['updated'] += $result['written'] ? 1 : 0;
            $this->progress = $this->progress || (bool) $result['written'];
            if ($result['raised']) {
                $this->raised[] = $item->id;
            }
            $this->notePicture($item, $wanted['flyer']);

            return true;
        }

        if ($room <= 0) {
            $feed->stats = ['continues_tomorrow' => true] + ($feed->stats ?? []);
            $this->unfinished = true;

            return true;
        }

        $venue = $this->venues->resolve($feed, $role, $row, $known);
        if ($venue['deferred']) {
            $this->unfinished = true;

            return true;
        }

        if (! EventFeed::whereKey($feed->id)->whereNull('paused_at')->exists()) {
            return false;
        }

        // An event the feed made that went some other way than the owner deleting it (state
        // still "imported", or "removed" by the feed and listed again) comes back for them to
        // look at, whatever the feed does with new events.
        $returning = $item->state !== EventFeedItem::STATE_NEW;
        $wanted = $this->writer->wanted($feed, $role, $row, $venue['venue']);
        $this->writer->create($feed, $role, $owner, $item, $wanted, $firstRead ? $feed->baseline_batch : null, $returning);

        $counts['created']++;
        $room--;
        $this->progress = true;

        return true;
    }

    /**
     * The same event under a new id: the item that holds it takes the new entry's place in the
     * ledger, and the row the new entry made is dropped (the ledger has one row per id).
     */
    private function takeOver(EventFeedItem $holder, EventFeedItem $fresh): EventFeedItem
    {
        $taken = $fresh->only(['external_key', 'external_id', 'list_hash', 'detail_url', 'detail_checked_at', 'starts_at', 'last_seen_at']);
        $hint = $fresh->pending['hint'] ?? null;
        $fresh->delete();

        $holder->forceFill($taken + [
            'missing_reads' => 0,
            'pending' => array_filter(['hint' => $hint] + array_diff_key($holder->pending ?? [], ['hint' => true]), fn ($value) => $value !== null) ?: null,
        ])->save();
        $this->inThisRead[] = $holder->id;

        return $holder;
    }

    private function venueOf(EventFeedItem $item): ?Role
    {
        $id = $item->imported['row']['venue_id'] ?? null;

        return $id ? Role::find($id) : null;
    }

    private function notePicture(EventFeedItem $item, ?string $source): void
    {
        if (FeedEventWriter::same($source, $item->imported['src']['flyer'] ?? null)) {
            return;
        }

        $sameAddress = $item->image_source === $source;

        // An address that has been asked for enough is not asked for again on every read. A new
        // address starts over.
        if ($sameAddress && $item->picture_tries >= self::PICTURE_TRIES) {
            return;
        }

        $item->forceFill(['image_pending' => true, 'image_source' => $source, 'picture_tries' => $sameAddress ? $item->picture_tries : 0])->save();
    }

    /**
     * How many more events this run may make: what is left of PER_DAY for this schedule's
     * feeds, and on the hosted service no more than what is left of the day's cap less the share
     * kept free for events added by hand.
     */
    private function room(Role $role, User $owner): int
    {
        $made = Event::where('creator_role_id', $role->id)
            ->where('import_source', Event::IMPORT_FEED)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
        $room = max(0, self::PER_DAY - $made);
        $left = $role->eventCreateAllowance($owner);

        if ($left !== null) {
            $room = min($room, max(0, $left - (int) ceil((int) $role->eventCreateDailyLimit() * self::LEAVE_FREE)));
        }

        return $room;
    }

    private function pictures(EventFeed $feed): void
    {
        // The ones tried least come first, so that a few that never load do not stand in front
        // of the rest on every run.
        $waiting = $feed->items()
            ->where('image_pending', true)
            ->whereNotNull('event_id')
            ->with('event')
            ->orderBy('picture_tries')
            ->orderBy('id')
            ->limit(self::PICTURES_PER_RUN)
            ->get();

        $this->unfinished = $this->unfinished || $waiting->count() >= self::PICTURES_PER_RUN;

        foreach ($waiting as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            try {
                $settled = ! $item->event || $this->writer->flyer($item, $item->event, $item->image_source);
            } catch (\Throwable $e) {
                report($e);
                $settled = false;
            }

            if ($settled) {
                $item->forceFill(['image_pending' => false, 'picture_tries' => 0])->save();
                $this->progress = true;

                continue;
            }

            // Not to be had (gone, refused to anyone but its own site, too large, not a kind
            // of picture kept here). Asked a few times, then left: the event stays without it.
            $tries = min($item->picture_tries + 1, 250);
            $item->forceFill(['picture_tries' => $tries, 'image_pending' => $tries < self::PICTURE_TRIES])->save();
        }
    }

    /**
     * What the owner asked to be published ("Publish all"), a few at a time. An hour later the
     * asking has lapsed. Done before the source is asked for anything, so that a source that is
     * down does not keep the owner's own drafts waiting.
     *
     * @return int How many were published.
     */
    private function publish(EventFeed $feed, Role $role): int
    {
        $feed->items()->whereNotNull('publish_requested_at')->where('publish_requested_at', '<', now()->subHour())->update(['publish_requested_at' => null]);
        $published = 0;

        foreach ($feed->items()->whereNotNull('publish_requested_at')->whereNotNull('event_id')->with('event')->orderBy('starts_at')->get() as $item) {
            if (! $this->hasTime()) {
                $this->unfinished = true;

                break;
            }

            if ($item->event && $item->event->is_draft && ! $item->event->is_cancelled) {
                $this->lifecycle->publish($item->event, $feed->added_by, $role);
                $published++;
                $this->progress = true;
            }

            $item->forceFill(['publish_requested_at' => null])->save();
        }

        return $published;
    }

    /**
     * Forget what no longer matters. The ledger is what stops an event being made twice, so an
     * item is kept for as long as its source could still show it to us:
     *
     *  - never while the event it made has not ended;
     *  - one the owner dismissed or skipped, until thirty days after its own start AND thirty
     *    days after the source last showed it (an exhibition is listed for months after it
     *    opens, and forgetting the item sooner brought the deleted event back);
     *  - anything else, thirty days after its event ended, or ninety days after the source last
     *    showed it;
     *  - and "the source has not shown it" is not counted against a paused feed, which is not
     *    looking: a feed resumed after four months must not find its ledger empty.
     *
     * @return int How many items were forgotten.
     */
    public function prune(): int
    {
        $now = now();

        $looking = EventFeed::whereNull('paused_at')->select('id');
        $ownersNo = [EventFeedItem::STATE_DISMISSED, EventFeedItem::STATE_SKIPPED];

        $dismissed = EventFeedItem::whereIn('state', $ownersNo)
            ->whereIn('event_feed_id', $looking)
            ->where(fn ($query) => $query
                ->where(fn ($dated) => $dated
                    ->where('starts_at', '<', $now->copy()->subDays(30))
                    ->where(fn ($unseen) => $unseen->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $now->copy()->subDays(30))))
                ->orWhere(fn ($undated) => $undated->whereNull('starts_at')->where('last_seen_at', '<', $now->copy()->subDays(90))))
            ->delete();

        $rest = EventFeedItem::whereNotIn('state', $ownersNo)
            ->where(fn ($query) => $query
                ->where('starts_at', '<', $now->copy()->subDays(31))
                ->orWhere(fn ($unseen) => $unseen->where('last_seen_at', '<', $now->copy()->subDays(90))->whereIn('event_feed_id', $looking)))
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
        [$feed->waiting_count, $feed->decide_count] = $feed->waitingNow();
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
        ])->save();

        // Many sources failing at once is likelier our fault than each source's: nobody is told
        // their feed is broken and none is paused for it. The platform's admins are told instead.
        // Asked after this failure is saved, so that it is one of those counted.
        $ours = EventFeed::manyFailing();
        $since = $feed->last_success_at ?? $feed->created_at;
        $pausing = ! $ours
            && $failures >= self::PAUSE_AFTER_FAILURES
            && $since
            && $since->lt($now->copy()->subDays(self::PAUSE_AFTER_DAYS));

        if ($pausing) {
            $feed->forceFill(['paused_at' => $now, 'pause_reason' => EventFeed::PAUSED_FAILING])->save();
            $feed->items()->whereNotNull('publish_requested_at')->update(['publish_requested_at' => null]);
        }

        if (! $ours) {
            $this->notifier->afterFailure($feed, $pausing);
        }

        return ['status' => $reason];
    }

    /**
     * A read that threw. Counted like any failed read, so that it backs off, is said in the
     * feed's status, is mailed about after a few days and pauses the feed in the end, instead of
     * failing quietly once an hour for ever.
     */
    public function crashed(EventFeed $feed): void
    {
        $this->failed($feed, 'failed', null, null);
    }

    /** The last few reads that did something, for the feed's own page. */
    private function remember(EventFeed $feed, Role $role, User $owner, Carbon $now, array $counts, ?Carbon $wholeAt = null, bool $quick = false): array
    {
        $stats = $feed->stats ?? [];
        // A good read: the last failure's status, and that somebody was told it was failing.
        unset($stats['last_http'], $stats['failing_told'], $stats['whole_at'], $stats['quick']);

        if ($wholeAt) {
            $stats['whole_at'] = $wholeAt->toIso8601String();
        }
        if ($quick) {
            $stats['quick'] = (int) ($feed->stats['quick'] ?? 0) + 1;
        }

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
