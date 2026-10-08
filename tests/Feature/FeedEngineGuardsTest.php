<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\EventLifecycleService;
use App\Services\Feeds\FeedActions;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a review of the feed engine found that tidy fixtures had hidden.
 *
 * Every test in FeedImporterTest reads a small, whole, well-behaved source whose ids never
 * change and whose owner never pushes back. Each test here is one of the sources and owners
 * that do: a counter left running for weeks, pictures that never load, a page that shows only
 * its next few, an id that changes, an owner who restores what the feed cancelled.
 */
class FeedEngineGuardsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FEED = 'https://93.184.216.34/private-0123456789abcdef/basic.ics';

    private User $owner;

    private Role $role;

    private array $entries = [];

    private mixed $answer = null;

    /** Answers for particular addresses, by what the address contains. */
    private array $pages = [];

    /** @var list<string> Every address asked for, in order. */
    private array $asked = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            $this->asked[] = $request->url();

            foreach ($this->pages as $part => $response) {
                if (str_contains($request->url(), $part)) {
                    return is_callable($response) ? $response($request) : $response;
                }
            }

            return $this->answer ?? Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar']);
        });

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna', 'show_event_interest' => true]);
    }

    private function feed(array $attrs = [], string $url = self::FEED): EventFeed
    {
        return EventFeed::create($attrs + [
            'role_id' => $this->role->id, 'added_by' => $this->owner->id, 'name' => 'Town calendar',
            'url' => $url, 'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34',
            'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true,
            'publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    private function entry(string $uid, string $name, int $days = 10, string $more = '', int $hour = 19): string
    {
        $start = now('Europe/Vienna')->addDays($days)->setTime($hour, 30);

        return "UID:{$uid}\nSUMMARY:{$name}\nDTSTART;TZID=Europe/Vienna:".$start->format('Ymd\THis')."\nDTEND;TZID=Europe/Vienna:".$start->copy()->addHours(2)->format('Ymd\THis').($more ? "\n".$more : '');
    }

    private function calendar(): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($entry) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($entry))."\r\nEND:VEVENT\r\n", $this->entries))
            ."END:VCALENDAR\r\n";
    }

    /** A page that marks up its events, as most venue sites do. */
    private function page(array $events): \GuzzleHttp\Promise\PromiseInterface
    {
        $nodes = [];
        foreach ($events as $slug => [$name, $days]) {
            $nodes[] = ['@context' => 'https://schema.org', '@type' => 'Event', 'name' => $name, 'url' => 'https://93.184.216.34/e/'.$slug,
                'startDate' => now('Europe/Vienna')->addDays($days)->setTime(19, 30)->format('Y-m-d\TH:i:sP')];
        }

        return Http::response('<html><head><title>What is on</title><script type="application/ld+json">'.json_encode($nodes).'</script></head><body></body></html>', 200, ['Content-Type' => 'text/html']);
    }

    private function read(EventFeed $feed): array
    {
        return app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 30);
    }

    /** Read it again a while later: an entry must be out of sight for an hour and a half before its absence counts. */
    private function readLater(EventFeed $feed): array
    {
        DB::table('event_feed_items')->update(['last_seen_at' => DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);

        return $this->read($feed);
    }

    private function named(string $name): ?Event
    {
        return Event::where('name', $name)->first();
    }

    private function itemFor(EventFeed $feed, string $id): ?EventFeedItem
    {
        return $feed->items()->where('external_key', EventFeedItem::keyFor($id))->first();
    }

    private function signUp(Event $event): void
    {
        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test', 'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->fresh()->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();
    }

    /**
     * In "Leave it", the default, an event that left the source is missed on every read for as
     * long as it is still to come. The counter was a tinyint: the 256th miss was an error thrown
     * before anything was written, on that read and every one after.
     */
    public function test_the_count_of_misses_stops_and_the_feed_goes_on_reading(): void
    {
        $feed = $this->feed(['left_action' => EventFeed::LEFT_KEEP]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Left weeks ago', 40)];
        $this->read($feed);

        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->itemFor($feed, 'b')->forceFill(['missing_reads' => FeedImporter::MISSING_CAP])->save();

        $this->entries[] = $this->entry('c', 'Arrives later', 12);
        $this->assertSame('ok', $this->readLater($feed)['status']);
        $this->assertSame('ok', $this->readLater($feed)['status']);

        $this->assertSame(FeedImporter::MISSING_CAP, $this->itemFor($feed, 'b')->missing_reads);
        $this->assertNotNull($this->named('Arrives later'));
        $this->assertNotNull($this->named('Left weeks ago'), 'and in "Leave it" the event stays');
    }

    /**
     * A source that will not hand its pictures to anybody but its own pages (hotlink
     * protection) is enough: every picture fails, for ever. Each used to stay pending, ten
     * pending meant "come back in a minute", and the whole list was fetched again every minute.
     */
    public function test_pictures_that_cannot_be_had_are_asked_for_three_times_and_do_not_bring_the_run_back(): void
    {
        $feed = $this->feed();
        $this->pages['/img/'] = Http::response('no', 403);
        $this->entries = [];
        for ($i = 1; $i <= 11; $i++) {
            $this->entries[] = $this->entry("e{$i}", "Event {$i}", 10 + $i, "ATTACH;FMTTYPE=image/jpeg:https://93.184.216.34/img/{$i}.jpg");
        }
        $pictures = fn () => count(array_filter($this->asked, fn ($url) => str_contains($url, '/img/')));
        $soon = fn () => $feed->fresh()->next_check_at->lt(now()->addMinutes(5));

        // The first run made eleven events: it got somewhere, and comes back for the pictures.
        $this->read($feed);
        $this->assertSame(11, Event::count());
        $this->assertTrue($soon());

        // The next asks for pictures and gets none. That is not getting anywhere.
        $this->read($feed);
        $this->assertFalse($soon(), 'a run that settled nothing waits its hour');

        for ($i = 0; $i < 6; $i++) {
            $this->read($feed);
        }

        $this->assertSame(33, $pictures(), 'three tries each, and no more');
        $this->assertSame(0, $feed->items()->where('image_pending', true)->count());
        $before = $pictures();
        $this->read($feed);
        $this->assertSame($before, $pictures(), 'an address that was tried enough is not armed again');

        // A new address for one of them is a new picture.
        $this->entries[0] = $this->entry('e1', 'Event 1', 11, 'ATTACH;FMTTYPE=image/jpeg:https://93.184.216.34/img/new.jpg');
        $this->read($feed);
        $this->assertSame($before + 1, $pictures());
    }

    /** A post's own page that never answers is asked a few times, an hour apart, and then left out. */
    public function test_a_page_that_never_answers_is_tried_a_few_times_and_the_first_read_still_ends(): void
    {
        $feed = $this->feed(['kind' => EventFeed::KIND_ITEMS, 'can_see_leaving' => false, 'left_action' => EventFeed::LEFT_KEEP], 'https://93.184.216.34/feed.xml');
        $this->answer = Http::response('<?xml version="1.0"?><rss version="2.0"><channel><title>News</title>'
            .'<item><guid>1</guid><title>Concert</title><link>https://93.184.216.34/posts/1</link></item>'
            .'<item><guid>2</guid><title>Dead link</title><link>https://93.184.216.34/posts/2</link></item>'
            .'</channel></rss>', 200, ['Content-Type' => 'application/rss+xml']);
        $this->pages['/posts/1'] = Http::response('<html><head><script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Concert',
            'startDate' => now('Europe/Vienna')->addDays(5)->setTime(20, 0)->format('Y-m-d\TH:i:sP')]).'</script></head></html>', 200, ['Content-Type' => 'text/html']);
        $this->pages['/posts/2'] = Http::response('down', 500);
        $dead = fn () => count(array_filter($this->asked, fn ($url) => str_contains($url, '/posts/2')));
        $anHourOn = fn () => DB::table('event_feed_items')->whereNotNull('detail_checked_at')->update(['detail_checked_at' => DB::raw('DATE_SUB(detail_checked_at, INTERVAL 1 HOUR)')]);

        $this->read($feed);
        $this->assertNotNull($this->named('Concert'));
        $this->assertSame(1, $dead());
        $this->assertNull($feed->fresh()->baseline_done_at, 'one entry is still waiting');

        // The same minute: not asked again.
        $this->read($feed);
        $this->assertSame(1, $dead());

        for ($i = 0; $i < 8; $i++) {
            $anHourOn();
            $this->read($feed);
        }

        $this->assertSame(FeedImporter::PAGE_TRIES, $dead());
        $this->assertSame('unreadable', $this->itemFor($feed, '2')->pending['left_out']);
        $this->assertNotNull($feed->fresh()->baseline_done_at, 'and the first read is over');
        $this->assertFalse(app(FeedActions::class)->canUndoFirstRead($feed->fresh()->forceFill(['baseline_done_at' => now()->subDays(2)])));
    }

    /**
     * Under "Mark it cancelled": what the owner did is never undone. Their restore was
     * cancelled again by the next read, every hour; and an event they cancelled themselves was
     * put back when the source listed it again.
     */
    public function test_the_owners_restore_stays_and_so_does_their_own_cancellation(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Leaves', 11)];
        $this->read($feed);
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertTrue((bool) $this->named('Leaves')->is_cancelled);
        $this->assertNotNull($this->itemFor($feed, 'b')->feed_cancelled_at);

        // The owner puts it back on. It stays on, however many times the feed looks.
        app(EventLifecycleService::class)->restore($this->named('Leaves'), $this->owner->id);
        $this->readLater($feed);
        $this->readLater($feed);
        $this->assertFalse((bool) $this->named('Leaves')->is_cancelled, 'the restore is the owner\'s answer');

        // Later they call it off themselves, and then the source lists it again.
        $this->travel(3)->seconds();
        app(EventLifecycleService::class)->cancel($this->named('Leaves'), $this->owner->id);
        $this->entries[] = $this->entry('b', 'Leaves', 11);
        $this->read($feed);
        $this->assertTrue((bool) $this->named('Leaves')->is_cancelled, 'their cancellation is not the feed\'s to take back');

        // The plain case still works: the feed's own cancellation is taken back by the feed.
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Leaves', 11), $this->entry('c', 'Comes and goes', 12)];
        $this->read($feed);
        unset($this->entries[2]);
        $this->read($feed);
        $this->readLater($feed);
        $this->assertTrue((bool) $this->named('Comes and goes')->is_cancelled);
        $this->entries[2] = $this->entry('c', 'Comes and goes', 12);
        $this->read($feed);
        $this->assertFalse((bool) $this->named('Comes and goes')->is_cancelled);
    }

    /** The owner's decision to cancel, with people told, is theirs too. */
    public function test_an_event_the_owner_decided_to_cancel_is_not_put_back_by_the_feed(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Has a fan', 11)];
        $this->read($feed);
        $this->signUp($this->named('Has a fan'));
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $item = $this->itemFor($feed, 'b');
        $this->assertSame(EventFeedItem::STATE_DECIDE, $item->state);

        $this->assertSame('cancelled', app(FeedActions::class)->apply($feed->fresh(), $this->role, $item, $this->owner, false, null));
        $this->assertFalse((bool) $this->itemFor($feed, 'b')->cancelled_by_feed);

        $this->entries[] = $this->entry('b', 'Has a fan', 11);
        $this->read($feed);
        $this->readLater($feed);
        $this->assertTrue((bool) $this->named('Has a fan')->is_cancelled);
    }

    /**
     * A source gives an event a new id (a CMS migration, a re-export). The entry it was made
     * from is gone and a new one is the same event. As two items, the new one only stood beside
     * the event while the old one went missing, and the event was removed with the source still
     * listing it.
     */
    public function test_the_same_event_under_a_new_id_carries_on(): void
    {
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('old-1', 'Jazz night'), $this->entry('z', 'Other', 12)];
        $this->read($feed);
        $event = $this->named('Jazz night');

        $this->entries = [$this->entry('new-1', 'Jazz night'), $this->entry('z', 'Other', 12)];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $this->assertNotNull($this->named('Jazz night'), 'still listed, so still here');
        $this->assertSame($event->id, $this->named('Jazz night')->id);
        $this->assertNull($this->itemFor($feed, 'old-1'));
        $this->assertSame([EventFeedItem::STATE_IMPORTED, $event->id, 0], [$this->itemFor($feed, 'new-1')->state, $this->itemFor($feed, 'new-1')->event_id, $this->itemFor($feed, 'new-1')->missing_reads]);
        $this->assertSame(2, $feed->items()->count(), 'no twin');

        // And it goes on following the source under its new id.
        $this->entries[0] = $this->entry('new-1', 'Jazz night, second set');
        $this->read($feed);
        $this->assertSame('Jazz night, second set', $event->fresh()->name);

        // An event made by hand is still only stood beside.
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'By hand', 'starts_at' => now('Europe/Vienna')->addDays(14)->setTime(19, 30)->utc()->format('Y-m-d H:i:s')]);
        $this->entries[] = $this->entry('h', 'By hand', 14);
        $this->read($feed);
        $this->assertSame([EventFeedItem::STATE_MATCHED, $byHand->id], [$this->itemFor($feed, 'h')->state, $this->itemFor($feed, 'h')->event_id]);
    }

    /**
     * A source lists one event twice, under two ids. When one of the two entries goes, the event
     * is still listed: by the other entry of this very feed.
     */
    public function test_an_event_listed_twice_does_not_leave_when_one_of_its_entries_does(): void
    {
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('x1', 'Listed twice'), $this->entry('z', 'Other', 12)];
        $this->read($feed);
        // A second entry for the same event turns up while the first is still listed: it
        // stands beside the event, and does not take it over.
        array_splice($this->entries, 1, 0, [$this->entry('x2', 'Listed twice')]);
        $this->read($feed);
        $this->assertSame(1, Event::where('name', 'Listed twice')->count());
        $this->assertSame(EventFeedItem::STATE_MATCHED, $this->itemFor($feed, 'x2')->state);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $this->itemFor($feed, 'x1')->state);

        unset($this->entries[0]);
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $this->assertNotNull($this->named('Listed twice'));
    }

    /**
     * "Keep the event" answers for one absence. If the entry comes back and leaves again, that
     * is a new question, and it is asked.
     */
    public function test_keeping_an_event_answers_for_that_absence_and_not_the_next(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Has a fan', 11)];
        $this->read($feed);
        $this->signUp($this->named('Has a fan'));
        $away = [$this->entry('a', 'Stays')];
        $back = [$this->entry('a', 'Stays'), $this->entry('b', 'Has a fan', 11)];

        $this->entries = $away;
        $this->read($feed);
        $this->readLater($feed);
        app(FeedActions::class)->keep($feed->fresh(), $this->itemFor($feed, 'b'));
        $this->readLater($feed);
        $this->readLater($feed);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $this->itemFor($feed, 'b')->state, 'kept: not asked again while it stays away');

        $this->entries = $back;
        $this->read($feed);
        $this->entries = $away;
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(EventFeedItem::STATE_DECIDE, $this->itemFor($feed, 'b')->state, 'it came back and left again: a new question');
    }

    /** A weekly class moved from half past seven to half past eight is the same dates, an hour later. */
    public function test_a_repeating_entry_moved_by_an_hour_is_the_same_dates(): void
    {
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $weekly = fn (int $hour) => 'UID:class'."\nSUMMARY:Yoga\nDTSTART;TZID=Europe/Vienna:".now('Europe/Vienna')->addDays(3)->setTime($hour, 30)->format('Ymd\THis')."\nRRULE:FREQ=WEEKLY";
        $this->entries = [$weekly(19)];
        $this->read($feed);
        $ids = Event::where('name', 'Yoga')->orderBy('starts_at')->pluck('id')->all();
        $starts = Event::where('name', 'Yoga')->orderBy('starts_at')->pluck('starts_at')->all();
        $this->assertCount(12, $ids);

        $this->entries = [$weekly(20)];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $this->assertSame($ids, Event::where('name', 'Yoga')->orderBy('starts_at')->pluck('id')->all(), 'the same twelve events');
        $this->assertSame(
            array_map(fn ($start) => \Carbon\Carbon::parse($start)->addHour()->format('Y-m-d H:i:s'), $starts),
            Event::where('name', 'Yoga')->orderBy('starts_at')->pluck('starts_at')->all()
        );
        $this->assertSame(12, $feed->items()->count());
    }

    /**
     * A page shows its next three. An earlier event is added and the third is pushed to page
     * two. It has not left: it has not been reached.
     */
    public function test_a_page_that_shows_its_next_few_does_not_lose_what_it_did_not_reach(): void
    {
        $feed = $this->feed(['kind' => EventFeed::KIND_PAGE, 'left_action' => EventFeed::LEFT_DELETE], 'https://93.184.216.34/whats-on');
        $this->answer = $this->page(['a' => ['First', 3], 'b' => ['Second', 5], 'c' => ['Third', 7]]);
        $this->read($feed);
        $this->assertSame(3, Event::count());

        $this->answer = $this->page(['z' => ['Added before', 1], 'a' => ['First', 3], 'b' => ['Second', 5]]);
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);
        $this->assertNotNull($this->named('Third'), 'later than the last one shown: not reached, not gone');

        // One that IS inside what the page shows, and is no longer there, has left.
        $this->answer = $this->page(['z' => ['Added before', 1], 'b' => ['Second', 5], 'c' => ['Third', 7]]);
        $this->read($feed);
        $this->readLater($feed);
        $this->assertNull($this->named('First'));
        $this->assertNotNull($this->named('Third'));
    }

    /** A calendar that is suddenly empty is a source having a bad moment, not every event called off. */
    public function test_an_empty_calendar_takes_nothing_away(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11), $this->entry('c', 'Three', 12)];
        $this->read($feed);

        $this->entries = [];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $this->assertSame(0, Event::where('is_cancelled', true)->count());
        $this->assertSame(0, $feed->items()->where('missing_reads', '>', 0)->count());
    }

    /** One entry that cannot be saved is left out. It does not stop the read for all the others. */
    public function test_one_entry_that_cannot_be_saved_does_not_stop_the_rest(): void
    {
        $feed = $this->feed();
        // The real writer, with one entry it cannot save: text the database refuses, say.
        $real = app(\App\Services\Feeds\FeedEventWriter::class);
        $writer = \Mockery::mock(\App\Services\Feeds\FeedEventWriter::class.'[create]', [app(\App\Repos\EventRepo::class)]);
        $writer->shouldReceive('create')->andReturnUsing(function (...$args) use ($real) {
            if ($args[4]['name'] === 'Cannot be saved') {
                throw new \RuntimeException('SQLSTATE[HY000]: 1366 Incorrect string value');
            }

            return $real->create(...$args);
        });
        $this->app->instance(\App\Services\Feeds\FeedEventWriter::class, $writer);
        $this->entries = [$this->entry('a', 'Before'), $this->entry('b', 'Cannot be saved', 11), $this->entry('c', 'After', 12)];

        $result = $this->read($feed);

        $this->assertSame('ok', $result['status']);
        $this->assertSame(['After', 'Before'], Event::orderBy('name')->pluck('name')->all());
        $this->assertSame('failed', $this->itemFor($feed, 'b')->pending['left_out']);
        $this->assertSame(1, $result['left_out']);

        // And it is not tried again on every read.
        $this->read($feed);
        $this->assertSame(2, Event::count());
        $this->assertSame(0, $feed->fresh()->failure_count);
    }

    /** "Publish all" is the owner's own drafts: it does not wait on a source that is down. */
    public function test_what_was_asked_to_be_published_does_not_wait_on_the_source(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::DRAFT]);
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $this->assertSame(['asked' => 2, 'published' => 0], app(FeedActions::class)->publishAll($feed->fresh(), $this->role, $this->owner));

        $this->answer = Http::response('down', 500);
        $this->assertSame('http_error', $this->read($feed)['status']);

        $this->assertSame(0, Event::where('is_draft', true)->count());
        $this->assertSame(0, $feed->fresh()->waiting_count);
    }

    /** "Has it changed since?" is asked only while a whole read is recent. */
    public function test_a_list_that_never_changes_is_still_read_whole_once_a_day(): void
    {
        $feed = $this->feed();
        $this->answer = Http::response("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\nUID:a\r\nSUMMARY:One\r\nDTSTART:".now()->addDays(5)->format('Ymd\THis\Z')."\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", 200, ['Content-Type' => 'text/calendar', 'ETag' => '"v1"']);
        $this->read($feed);
        $this->assertSame('"v1"', $feed->fresh()->etag);

        $sawValidator = false;
        $this->pages['basic.ics'] = function ($request) use (&$sawValidator) {
            $sawValidator = $request->hasHeader('If-None-Match');

            return Http::response('', 304);
        };

        $this->read($feed);
        $this->assertTrue($sawValidator, 'within the day it is asked whether it changed');
        $seenAt = $this->itemFor($feed, 'a')->last_seen_at;

        $this->travel(25)->hours();
        unset($this->pages['basic.ics']);
        Http::fake();
        $asked = null;
        Http::fake(function ($request) use (&$asked) {
            $asked = $request->hasHeader('If-None-Match');

            return $this->answer;
        });
        $this->read($feed);

        $this->assertFalse($asked, 'a day on, the whole list is asked for');
        $this->assertTrue($this->itemFor($feed, 'a')->last_seen_at->gt($seenAt), 'and the ledger knows the entry is still shown');
    }

    /** A calendar entry's LOCATION that is a link is where a meeting is held. A feed never makes it the event's public link. */
    public function test_a_link_that_was_a_location_is_not_made_public(): void
    {
        $feed = $this->feed();
        $this->entries = [
            $this->entry('a', 'Online meeting', 10, 'LOCATION:https://meet.example.org/room/secret-123'),
            $this->entry('b', 'Has its own page', 11, "URL:https://example.org/events/b\nLOCATION:https://meet.example.org/room/other"),
        ];

        $this->read($feed);

        $this->assertNull($this->named('Online meeting')->registration_url);
        $this->assertSame('https://example.org/events/b', $this->named('Has its own page')->registration_url);
        $this->assertStringNotContainsString('secret-123', json_encode(Event::all()->toArray()));
    }

    /**
     * A source whose text is different on every read (a counter, a rotating line) would have
     * every event rewritten every hour: translations bought again, calendars pushed, a webhook.
     */
    public function test_a_source_that_changes_its_text_on_every_read_is_written_a_few_times_a_day(): void
    {
        $feed = $this->feed();
        $text = fn (int $n) => $this->entry('a', 'Concert', 10, "DESCRIPTION:{$n} people are looking at this");
        $this->entries = [$text(0)];
        $this->read($feed);

        $written = 0;
        for ($n = 1; $n <= 10; $n++) {
            $this->entries = [$text($n)];
            $written += $this->read($feed)['updated'];
        }

        $this->assertSame(\App\Services\Feeds\FeedEventWriter::WRITES_PER_DAY, $written);
        $this->assertStringContainsString('6 people', $this->named('Concert')->description);

        // Tomorrow it follows again, and nothing was lost: it takes what the source says then.
        $this->travel(1)->days();
        $this->assertSame(1, $this->read($feed)['updated']);
        $this->assertStringContainsString('10 people', $this->named('Concert')->description);
    }

    /**
     * An event the feed removed, listed again, comes back for the owner to look at: made as a
     * draft, not published and then hidden. A publish is a push to calendars and a webhook.
     */
    public function test_an_event_that_comes_back_is_made_as_a_draft_and_never_published_on_the_way(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\EventPublished::class]);
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Goes and returns', 11)];
        $this->read($feed);
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertNull($this->named('Goes and returns'));

        $published = [];
        Event::saved(function (Event $event) use (&$published) {
            if ($event->name === 'Goes and returns' && ! $event->is_draft) {
                $published[] = $event->id;
            }
        });
        $this->entries[] = $this->entry('b', 'Goes and returns', 11);
        $this->read($feed);

        $this->assertTrue((bool) $this->named('Goes and returns')->is_draft);
        $this->assertSame([], $published, 'it was never saved as public, not even for a moment');
    }

    /**
     * Rows read from posts' own pages are only in hand for that run. A run that reads pages and
     * runs out of time before writing must ask for them again next time, not tomorrow.
     */
    public function test_a_post_that_was_read_and_not_written_is_asked_for_again_on_the_next_run(): void
    {
        $feed = $this->feed(['kind' => EventFeed::KIND_ITEMS, 'can_see_leaving' => false, 'left_action' => EventFeed::LEFT_KEEP], 'https://93.184.216.34/feed.xml');
        $this->answer = Http::response('<?xml version="1.0"?><rss version="2.0"><channel><title>News</title>'
            .'<item><guid>1</guid><title>Concert</title><link>https://93.184.216.34/posts/1</link></item></channel></rss>', 200, ['Content-Type' => 'application/rss+xml']);
        // The page answers, slowly enough that the run's time is up when it has.
        $this->pages['/posts/1'] = function () {
            usleep(700000);

            return Http::response('<html><head><script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Concert',
                'startDate' => now('Europe/Vienna')->addDays(5)->setTime(20, 0)->format('Y-m-d\TH:i:sP')]).'</script></head></html>', 200, ['Content-Type' => 'text/html']);
        };

        app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 0.5);
        $this->assertNull($this->named('Concert'), 'read, and out of time before it was written');
        $this->assertNull($this->itemFor($feed, '1')->detail_checked_at, 'so it is due again at once');

        $this->read($feed);
        $this->assertNotNull($this->named('Concert'));
    }

    /** A feed makes at most 500 events a day whatever the plan allows: the guide says so. */
    public function test_a_feed_makes_at_most_five_hundred_a_day_on_any_plan(): void
    {
        config(['usage.event_create_daily_limit_enterprise' => 2000]);
        $room = new \ReflectionMethod(FeedImporter::class, 'room');

        $this->assertSame(FeedImporter::PER_DAY, $room->invoke(app(FeedImporter::class), $this->role->fresh(), $this->owner));

        // And never more than the plan leaves, less the share kept for events added by hand.
        config(['usage.event_create_daily_limit_enterprise' => 100]);
        $this->assertSame(80, $room->invoke(app(FeedImporter::class), $this->role->fresh(), $this->owner));
    }
}
