<?php

namespace Tests\Feature;

use App\Jobs\NotifyEventCancelled;
use App\Jobs\NotifyEventChange;
use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedActions;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The buttons on a feed's page: what the people who run a schedule do to a feed and to what it
 * made. Each is driven here as the page drives it, against events a real read made.
 */
class FeedActionsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    private array $entries = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar']));

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna', 'show_event_interest' => true]);
    }

    private function entry(string $uid, string $name, int $days = 10, string $more = ''): string
    {
        $start = now('Europe/Vienna')->addDays($days)->setTime(19, 30);

        return "UID:{$uid}\nSUMMARY:{$name}\nDTSTART;TZID=Europe/Vienna:".$start->format('Ymd\THis')."\nDTEND;TZID=Europe/Vienna:".$start->copy()->addHours(2)->format('Ymd\THis').($more ? "\n".$more : '');
    }

    private function calendar(): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($entry) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($entry))."\r\nEND:VEVENT\r\n", $this->entries))
            ."END:VCALENDAR\r\n";
    }

    private function feed(array $attrs = []): EventFeed
    {
        $url = 'https://93.184.216.34/'.($attrs['file'] ?? 'calendar').'.ics';
        unset($attrs['file']);

        return EventFeed::create($attrs + [
            'role_id' => $this->role->id, 'added_by' => $this->owner->id, 'name' => 'Town calendar', 'url' => $url,
            'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34', 'kind' => EventFeed::KIND_CALENDAR,
            'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true, 'publish_mode' => EventFeed::DRAFT,
            'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    private function read(EventFeed $feed): void
    {
        app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 30);
    }

    /**
     * Read it again a while later. An entry has to have been out of sight for an hour and a
     * half before its absence means anything (FeedImporter::GONE_AFTER_MINUTES): two reads a
     * minute apart are one look, not two.
     */
    private function readLater(EventFeed $feed): void
    {
        \Illuminate\Support\Facades\DB::table('event_feed_items')->update(['last_seen_at' => \Illuminate\Support\Facades\DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);

        $this->read($feed);
    }

    private function actions(): FeedActions
    {
        return app(FeedActions::class);
    }

    private function named(string $name): ?Event
    {
        return Event::where('name', $name)->first();
    }

    private function itemOf(EventFeed $feed, string $uid): EventFeedItem
    {
        return $feed->items()->where('external_key', EventFeedItem::keyFor($uid))->firstOrFail();
    }

    /** Somebody asked to be told about this event: a person who has signed up, and can be mailed. */
    private function signUp(Event $event): void
    {
        $event->forceFill(['is_draft' => false])->save();
        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->fresh()->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();
    }

    public function test_drafts_are_published_one_by_one_or_several_and_only_this_feeds(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11), $this->entry('c', 'Three', 12)];
        $this->read($feed);
        $other = $this->feed(['file' => 'other']);
        $this->entries = [$this->entry('x', 'Somebody else\'s draft', 13)];
        $this->read($other);
        $this->assertSame(3, $feed->fresh()->waiting_count);

        $ids = [$this->itemOf($feed, 'a')->id, $this->itemOf($feed, 'b')->id, $this->itemOf($other, 'x')->id];
        $this->assertSame(2, $this->actions()->publish($feed->fresh(), $this->role, $this->owner, $ids));

        $this->assertFalse((bool) $this->named('One')->is_draft);
        $this->assertFalse((bool) $this->named('Two')->is_draft);
        $this->assertTrue((bool) $this->named('Three')->is_draft);
        $this->assertTrue((bool) $this->named('Somebody else\'s draft')->is_draft, 'an item of another feed was published through this one');
        $this->assertSame(1, $feed->fresh()->waiting_count);
        // Pressed twice, it publishes nothing more.
        $this->assertSame(0, $this->actions()->publish($feed->fresh(), $this->role, $this->owner, $ids));
    }

    /** "Not this one" holds for as long as the source goes on listing it. */
    public function test_a_skipped_draft_is_deleted_and_not_made_again(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Wanted'), $this->entry('b', 'Not wanted', 11)];
        $this->read($feed);

        $this->assertSame(['skipped' => 1, 'kept' => 0], $this->actions()->skip($feed->fresh(), $this->owner, [$this->itemOf($feed, 'b')->id]));

        $this->assertNull($this->named('Not wanted'));
        $this->assertSame(EventFeedItem::STATE_SKIPPED, $this->itemOf($feed, 'b')->state);
        $this->assertSame(1, $feed->fresh()->waiting_count);

        $this->entries = [$this->entry('a', 'Wanted'), $this->entry('b', 'Not wanted (now with a new title)', 11)];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(['Wanted'], Event::pluck('name')->all());

        // A published event is not a draft to skip.
        $this->actions()->publish($feed->fresh(), $this->role, $this->owner, [$this->itemOf($feed, 'a')->id]);
        $this->assertSame(['skipped' => 0, 'kept' => 0], $this->actions()->skip($feed->fresh(), $this->owner, [$this->itemOf($feed, 'a')->id]));
        $this->assertNotNull($this->named('Wanted'));
    }

    /**
     * Skip is "not this one", said of what the feed brought, and it deletes for good. A draft
     * the owner has already worked on is theirs: it stays, as it does when a feed is removed or
     * its first read undone, and the page says so.
     */
    public function test_a_draft_the_owner_has_worked_on_is_not_skipped(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'As it came'), $this->entry('b', 'Rewritten', 11)];
        $this->read($feed);
        $this->named('Rewritten')->forceFill(['description' => 'Our own words about it.'])->save();

        $ids = [$this->itemOf($feed, 'a')->id, $this->itemOf($feed, 'b')->id];
        $this->assertSame(['skipped' => 1, 'kept' => 1], $this->actions()->skip($feed->fresh(), $this->owner, $ids));

        $this->assertNull($this->named('As it came'));
        $this->assertNotNull($this->named('Rewritten'));
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $this->itemOf($feed, 'b')->state);
        $this->assertSame(1, $feed->fresh()->waiting_count);
    }

    /**
     * Publish all asks the next run to do it. A feed that no run reads, paused or on a plan
     * without feeds, has no next run: the asking sat there while the page said "being
     * published", in the very state where it also said "You can still publish".
     */
    public function test_publish_all_on_a_feed_that_is_not_being_read_publishes_in_the_request_a_page_at_a_time(): void
    {
        $feed = $this->feed();
        $this->entries = array_map(fn ($n) => $this->entry('e'.$n, 'Event '.$n, 10 + $n), range(1, FeedActions::PUBLISH_AT_ONCE + 2));
        $this->read($feed);
        $this->assertSame(FeedActions::PUBLISH_AT_ONCE + 2, Event::where('is_draft', true)->count());
        $this->actions()->pause($feed->fresh());

        $this->assertSame(['asked' => 0, 'published' => FeedActions::PUBLISH_AT_ONCE], $this->actions()->publishAll($feed->fresh(), $this->role, $this->owner));
        // Soonest first, so what is left is what happens last.
        $this->assertEqualsCanonicalizing(['Event 26', 'Event 27'], Event::where('is_draft', true)->pluck('name')->all());
        $this->assertSame(0, $feed->items()->whereNotNull('publish_requested_at')->count(), 'nothing is left asked for with nobody to do it');
        $this->assertSame(2, $feed->fresh()->waiting_count);

        // Not paused, on a plan that has no feeds: the same.
        $this->actions()->resume($feed->fresh());
        $this->role->forceFill(['plan_type' => 'pro'])->save();
        $this->assertSame(['asked' => 0, 'published' => 2], $this->actions()->publishAll($feed->fresh(), $this->role->fresh(), $this->owner));
        $this->assertSame(0, Event::where('is_draft', true)->count());
    }

    /** A draft the source has since called off is not waiting for anybody to publish it. */
    public function test_a_draft_called_off_at_the_source_is_not_waiting_to_be_published(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Goes ahead'), $this->entry('b', 'Called off', 11)];
        $this->read($feed);
        $this->entries = [$this->entry('a', 'Goes ahead'), $this->entry('b', 'Called off', 11, 'STATUS:CANCELLED')];
        $this->read($feed);
        $this->assertTrue((bool) $this->named('Called off')->is_cancelled);
        $this->assertTrue((bool) $this->named('Called off')->is_draft);

        $this->assertSame(1, $feed->fresh()->waiting_count);
        $this->assertSame(0, $this->actions()->publish($feed->fresh(), $this->role, $this->owner, [$this->itemOf($feed, 'b')->id]));
        $this->assertTrue((bool) $this->named('Called off')->is_draft);

        $this->actions()->pause($feed->fresh());
        $this->assertSame(['asked' => 0, 'published' => 1], $this->actions()->publishAll($feed->fresh(), $this->role, $this->owner));
        $this->assertTrue((bool) $this->named('Called off')->is_draft);
    }

    /**
     * "Leave it" lets events go by that the other two choices would have acted on. Changing it
     * reaches back to every one of them at the next read, so the Edit page says how many first.
     */
    public function test_how_many_events_a_change_of_what_happens_to_a_gone_event_would_reach(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_KEEP]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Left', 11), $this->entry('c', 'Left too', 12)];
        $this->read($feed);
        $this->assertSame(0, $this->actions()->alreadyGone($feed->fresh()));

        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->assertSame(0, $this->actions()->alreadyGone($feed->fresh()), 'missed once is not gone');
        $this->readLater($feed);
        $this->assertSame(2, $this->actions()->alreadyGone($feed->fresh()));
        $this->assertFalse((bool) $this->named('Left')->is_cancelled, 'and under "Leave it" nothing was done to them');

        // One that is cancelled already, or over, is not something a change would reach.
        $this->named('Left')->forceFill(['is_cancelled' => true])->save();
        $this->assertSame(1, $this->actions()->alreadyGone($feed->fresh()));
        $this->named('Left too')->forceFill(['starts_at' => now()->subDay()->utc()->format('Y-m-d H:i:s')])->save();
        $this->assertSame(0, $this->actions()->alreadyGone($feed->fresh()));
    }

    /**
     * The tab's number is kept on the feed by each read. A draft published or deleted from the
     * event's own form changed nothing there, so the tab said "3 waiting" for up to an hour.
     */
    public function test_the_stored_counts_follow_what_is_done_from_the_events_own_form(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Deleted from its form'), $this->entry('b', 'Published from its form', 11), $this->entry('c', 'Still waiting', 12)];
        $this->read($feed);
        $this->assertSame(3, $feed->fresh()->waiting_count);

        // Deleted: counted at once, by the same call that keeps the feed from bringing it back.
        $this->actingAs($this->owner)->delete(route('event.delete', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->named('Deleted from its form')->id)]));
        $this->assertNull($this->named('Deleted from its form'));
        $this->assertSame(2, $feed->fresh()->waiting_count);

        // Published: nothing tells the feed, so the page counts when it opens.
        $this->named('Published from its form')->forceFill(['is_draft' => false])->save();
        $this->assertSame(2, $feed->fresh()->waiting_count);
        $this->actingAs($this->owner)->get(route('role.feeds.show', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)]))->assertOk();
        $this->assertSame(1, $feed->fresh()->waiting_count);

        // And so does the tab that lists the feeds, whose query loads a count beside each.
        $this->named('Still waiting')->forceFill(['is_draft' => false])->save();
        $this->actingAs($this->owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']))->assertOk()
            ->assertDontSee(trans_choice('messages.feeds_waiting', 1, ['count' => 1]));
        $this->assertSame(0, $feed->fresh()->waiting_count);
    }

    /**
     * A hundred publishes each push to connected calendars, so they are asked for and done by
     * the runs that follow. The asking lapses: pressed by someone who then left, or an hour ago,
     * it must not fire.
     */
    public function test_publish_all_is_asked_for_and_done_by_the_next_run_and_the_asking_lapses(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $feed->forceFill(['next_check_at' => now()->addHour()])->save();

        $this->assertSame(['asked' => 2, 'published' => 0], $this->actions()->publishAll($feed->fresh(), $this->role, $this->owner));
        $this->assertSame(2, Event::where('is_draft', true)->count(), 'nothing is published in the request itself');
        $this->assertTrue($feed->fresh()->next_check_at->lte(now()), 'the next run picks it up');

        $this->read($feed);
        $this->assertSame(0, Event::where('is_draft', true)->count());
        $this->assertSame(0, $feed->fresh()->waiting_count);

        // Asked for, and then an hour passes with no run.
        $this->entries[] = $this->entry('c', 'Three', 12);
        $this->read($feed);
        $this->actions()->publishAll($feed->fresh(), $this->role, $this->owner);
        $this->travel(61)->minutes();
        $this->read($feed);
        $this->assertTrue((bool) $this->named('Three')->is_draft);

        // Asked for, and then the feed is paused.
        $this->actions()->publishAll($feed->fresh(), $this->role, $this->owner);
        $this->actions()->pause($feed->fresh());
        $this->assertSame(0, $feed->items()->whereNotNull('publish_requested_at')->count());
    }

    public function test_a_feed_can_be_read_now_paused_and_resumed(): void
    {
        $feed = $this->feed(['next_check_at' => now()->addMinutes(50), 'last_checked_at' => now()->subMinutes(10), 'failure_count' => 6]);

        $this->assertTrue($this->actions()->readNow($feed->fresh()));
        $this->assertTrue($feed->fresh()->next_check_at->lte(now()));

        // Not more than once a minute: it was read a moment ago.
        $feed->forceFill(['last_checked_at' => now()->subSeconds(20), 'next_check_at' => now()->addHour()])->save();
        $this->assertFalse($this->actions()->readNow($feed->fresh()));
        $this->assertTrue($feed->fresh()->next_check_at->gt(now()));

        $this->actions()->pause($feed->fresh());
        $this->assertSame(EventFeed::PAUSED_BY_OWNER, $feed->fresh()->pause_reason);
        $feed->forceFill(['last_checked_at' => now()->subHour()])->save();
        $this->assertFalse($this->actions()->readNow($feed->fresh()), 'a paused feed is not read');

        // Whoever resumes it has looked at it: it starts again at once with a clean slate.
        $this->actions()->resume($feed->fresh());
        $feed->refresh();
        $this->assertSame([null, null, 0], [$feed->paused_at, $feed->pause_reason, $feed->failure_count]);
        $this->assertTrue($feed->next_check_at->lte(now()));
    }

    public function test_removing_a_feed_keeps_its_events_unless_asked_and_then_only_the_ones_that_are_its_alone(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Kept')];
        $this->read($feed);
        $event = $this->named('Kept');
        $this->assertTrue($event->isFromFeed());

        $this->assertSame(0, $this->actions()->remove($feed->fresh(), $this->owner, false));

        $this->assertSame(0, EventFeed::count());
        $this->assertSame(0, EventFeedItem::count());
        $this->assertNotNull($event->fresh());
        // An event like any other now: nothing is keeping it up to date.
        $this->assertFalse($event->fresh()->isFromFeed());
        $this->assertSame(Event::IMPORT_FEED, $event->fresh()->import_source);

        // With its events: the coming ones nobody signed up for and nobody worked on.
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'second']);
        $this->entries = [$this->entry('p', 'Plain'), $this->entry('q', 'Has a fan', 11), $this->entry('r', 'Renamed by hand', 12), $this->entry('s', 'Tomorrow', 1)];
        $this->read($feed);
        $this->signUp($this->named('Has a fan'));
        $this->named('Renamed by hand')->forceFill(['name' => 'Our own title'])->save();
        $this->travel(2)->days();

        $this->assertSame(1, $this->actions()->remove($feed->fresh(), $this->owner, true));

        $this->assertEqualsCanonicalizing(['Kept', 'Has a fan', 'Our own title', 'Tomorrow'], Event::pluck('name')->all());
    }

    /** Keeping is an answer: the same difference is not put in front of the owner again. */
    public function test_a_decision_to_keep_an_event_is_remembered(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Gone from the feed', 11)];
        $this->read($feed);
        $this->signUp($this->named('Gone from the feed'));
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $item = $this->itemOf($feed, 'b');
        $this->assertSame(EventFeedItem::STATE_DECIDE, $item->state);

        $this->actions()->keep($feed->fresh(), $item);

        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertSame(0, $feed->fresh()->decide_count);
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertFalse((bool) $this->named('Gone from the feed')->is_cancelled);
    }

    public function test_a_decision_to_follow_the_feed_cancels_the_event_and_can_tell_the_people_who_signed_up(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Gone from the feed', 11)];
        $this->read($feed);
        $this->signUp($this->named('Gone from the feed'));
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $item = $this->itemOf($feed, 'b');

        Bus::fake();
        $this->assertSame('cancelled', $this->actions()->apply($feed->fresh(), $this->role, $item, $this->owner, true, 'The hall is flooded.'));

        $event = $this->named('Gone from the feed');
        $this->assertTrue((bool) $event->is_cancelled);
        Bus::assertDispatched(NotifyEventCancelled::class);
        $item->refresh();
        // The owner's cancellation, not the feed's: nothing takes it back when the source lists it again.
        $this->assertSame([EventFeedItem::STATE_IMPORTED, false], [$item->state, $item->cancelled_by_feed]);
        $this->assertSame(0, $feed->fresh()->decide_count);
        // Done once: the next reads neither raise it again nor undo it.
        $this->assertNull($this->actions()->apply($feed->fresh(), $this->role, $item, $this->owner, true, null));
        $this->read($feed);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
    }

    public function test_a_decision_to_follow_a_move_moves_the_event_and_tells_them_when_asked(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Open mic')];
        $this->read($feed);
        $event = $this->named('Open mic');
        $this->signUp($event);
        $was = $event->fresh()->starts_at;
        $this->entries = [$this->entry('a', 'Open mic', 11)];
        $this->read($feed);
        $item = $this->itemOf($feed, 'a');
        $this->assertSame('moved', $item->pending['decide']['kind']);
        $this->assertSame($was, $event->fresh()->starts_at);

        Bus::fake();
        $this->assertSame('moved', $this->actions()->apply($feed->fresh(), $this->role, $item, $this->owner, true, null));

        $this->assertSame(now('Europe/Vienna')->addDays(11)->setTime(19, 30)->utc()->format('Y-m-d H:i:s'), $event->fresh()->starts_at);
        Bus::assertDispatched(NotifyEventChange::class);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertNull($item->fresh()->pending);

        // It follows the source again from here: the next read has nothing to hold or to write.
        $stamp = $event->fresh()->updated_at;
        $this->read($feed);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $item->fresh()->state);
        $this->assertEquals($stamp, $event->fresh()->updated_at);

        // Without the tick, nobody is mailed.
        $this->entries = [$this->entry('a', 'Open mic', 12)];
        $this->read($feed);
        Bus::fake();
        $this->actions()->apply($feed->fresh(), $this->role, $item->fresh(), $this->owner, false, null);
        Bus::assertNotDispatched(NotifyEventChange::class);
    }

    /**
     * The wrong address, or the wrong clock: for a day the first read can be taken back. What
     * somebody signed up for, or the owner already worked on, stays.
     */
    public function test_a_first_read_can_be_undone_for_a_day(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Made by hand']);
        $this->entries = [$this->entry('a', 'Plain'), $this->entry('b', 'Has a fan', 11), $this->entry('c', 'Renamed by hand', 12)];
        $this->read($feed);
        $this->signUp($this->named('Has a fan'));
        $this->named('Renamed by hand')->forceFill(['name' => 'Our own title'])->save();
        // The first read is over. What the feed adds an hour later is not part of it.
        $this->entries[] = $this->entry('d', 'Arrived an hour later', 13);
        $this->read($feed);
        $this->assertNull($this->named('Arrived an hour later')->import_batch);
        $this->assertTrue($this->actions()->canUndoFirstRead($feed->fresh()));

        $this->assertSame(['removed' => 1, 'kept' => 2], $this->actions()->undoFirstRead($feed->fresh(), $this->owner));

        $this->assertEqualsCanonicalizing(['Made by hand', 'Has a fan', 'Our own title', 'Arrived an hour later'], Event::pluck('name')->all());
        $feed->refresh();
        $this->assertSame(EventFeed::PAUSED_UNDO, $feed->pause_reason);
        $this->assertNotNull($byHand->fresh());

        // Just undone, and not read since: there is nothing of a first read to take back.
        $this->assertFalse($this->actions()->canUndoFirstRead($feed->fresh()));

        // Resumed, it is a first read again, which is what undoing it was for: the address or
        // the clock is put right and the feed starts over. What was taken back returns as the
        // feed's setting says, published here, and with the first read's mark, which is what
        // keeps it out of the email to followers. (It used to return as a draft whatever the
        // setting, without the mark, so publishing each one announced it.)
        $this->actions()->resume($feed);
        $this->assertNull($feed->fresh()->baseline_done_at);
        $this->assertNotSame('abcdef012345', $feed->fresh()->baseline_batch);
        $this->read($feed);
        $plain = $this->named('Plain');
        $this->assertFalse((bool) $plain->is_draft);
        $this->assertSame($feed->fresh()->baseline_batch, $plain->import_batch);
        $this->assertNotNull($feed->fresh()->baseline_done_at);

        // And it can be taken back again. What the first undo kept is not this read's to remove.
        $this->assertTrue($this->actions()->canUndoFirstRead($feed->fresh()));
        $this->assertSame(['removed' => 1, 'kept' => 0], $this->actions()->undoFirstRead($feed->fresh(), $this->owner));
        $this->assertEqualsCanonicalizing(['Made by hand', 'Has a fan', 'Our own title', 'Arrived an hour later'], Event::pluck('name')->all());
        $this->actions()->resume($feed->fresh());
        $this->read($feed);

        // A day after the first read is over, there is no taking it back.
        $this->travel(25)->hours();
        $this->assertFalse($this->actions()->canUndoFirstRead($feed->fresh()));
        $before = Event::count();
        $this->assertSame(['removed' => 0, 'kept' => 0], $this->actions()->undoFirstRead($feed->fresh(), $this->owner));
        $this->assertSame($before, Event::count());
        $this->assertNull($feed->fresh()->paused_at);
    }

    /**
     * A feed of posts says when each event is on the post's own page, which is read once a day.
     * A new clock is a reason to read those pages again now, for events still to come and no others.
     */
    public function test_a_new_clock_reads_again_the_pages_of_events_still_to_come(): void
    {
        $feed = $this->feed(['kind' => EventFeed::KIND_ITEMS, 'can_see_leaving' => false, 'left_action' => EventFeed::LEFT_KEEP]);
        $item = fn (string $id, string $state, ?\Carbon\Carbon $starts, ?string $page = 'https://93.184.216.34/post') => EventFeedItem::create([
            'event_feed_id' => $feed->id, 'external_key' => EventFeedItem::keyFor($id), 'external_id' => $id, 'state' => $state,
            'starts_at' => $starts, 'detail_url' => $page ? $page.'/'.$id : null, 'detail_checked_at' => now()->subHour(),
            'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
        $item('coming', EventFeedItem::STATE_IMPORTED, now()->addWeek());
        $item('undated', EventFeedItem::STATE_IMPORTED, null);
        $item('deciding', EventFeedItem::STATE_DECIDE, now()->addWeek());
        $item('over', EventFeedItem::STATE_IMPORTED, now()->subWeek());
        $item('skipped', EventFeedItem::STATE_SKIPPED, now()->addWeek());
        $item('listed', EventFeedItem::STATE_IMPORTED, now()->addWeek(), null);
        $again = fn () => $feed->items()->whereNull('detail_checked_at')->pluck('external_id')->sort()->values()->all();
        $same = ['name' => 'Town calendar', 'publish_mode' => 'draft', 'left_action' => 'keep'];

        $this->assertFalse($this->actions()->edit($feed->fresh(), $same + ['source_timezone' => 'Europe/Vienna']));
        $this->assertSame([], $again());

        $this->assertTrue($this->actions()->edit($feed->fresh(), $same + ['source_timezone' => 'Europe/London']));
        $this->assertSame(['coming', 'deciding', 'undated'], $again());
        $this->assertSame('Europe/London', $feed->fresh()->source_timezone);
    }

    /** What the form cannot send, the action still does not take. */
    public function test_editing_keeps_what_it_is_not_given_and_refuses_what_the_feed_cannot_do(): void
    {
        $markets = $this->role->groups()->create(['name' => 'Markets', 'slug' => 'markets']);
        $feed = $this->feed(['kind' => EventFeed::KIND_ITEMS, 'can_see_leaving' => false, 'left_action' => EventFeed::LEFT_KEEP, 'publish_mode' => EventFeed::PUBLISH, 'group_id' => $markets->id, 'category_id' => 3]);

        $this->actions()->edit($feed, ['name' => '   ', 'left_action' => 'delete', 'source_timezone' => 'Not/AZone', 'publish_mode' => 'whatever']);

        $feed->refresh();
        $this->assertSame('Town calendar', $feed->name);
        // Not named, so not cleared.
        $this->assertSame([$markets->id, 3], [$feed->group_id, $feed->category_id]);
        // A feed of posts cannot tell that an event is gone, so it is never asked to act on it.
        $this->assertSame(EventFeed::LEFT_KEEP, $feed->left_action);
        $this->assertSame('Europe/Vienna', $feed->source_timezone);
        $this->assertSame(EventFeed::DRAFT, $feed->publish_mode);

        $this->actions()->edit($feed, ['name' => str_repeat('n', 200), 'group_id' => null, 'category_id' => null]);
        $this->assertSame(120, mb_strlen($feed->fresh()->name));
        $this->assertSame(EventFeed::DRAFT, $feed->fresh()->publish_mode);
        $this->assertSame([null, null], [$feed->fresh()->group_id, $feed->fresh()->category_id]);
    }
}
