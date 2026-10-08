<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One read of one feed, and the next, and the one after: what a schedule's events look like
 * after each. The address is an IP literal and every answer is faked, one closure for all of
 * them so that what the source says can change between reads.
 */
class FeedImporterTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FEED = 'https://93.184.216.34/calendar.ics';

    private User $owner;

    private Role $role;

    /** What the source answers: a calendar's entries, or a whole response. */
    private array $entries = [];

    private mixed $answer = null;

    /** Answers for particular addresses, by what the address ends in. */
    private array $pages = [];

    private int $asked = 0;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            $this->asked++;

            foreach ($this->pages as $ending => $response) {
                if (str_ends_with($request->url(), $ending)) {
                    return $response;
                }
            }

            return $this->answer ?? Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar']);
        });

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna']);
    }

    private function feed(array $attrs = [], ?Role $role = null, string $url = self::FEED): EventFeed
    {
        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'added_by' => $this->owner->id, 'name' => 'Town calendar',
            'url' => $url, 'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34',
            'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true,
            'publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    /** An entry $days from now at 19:30 in Vienna, written the way a calendar writes it. */
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

    private function read(EventFeed $feed): array
    {
        return app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 30);
    }

    /**
     * Read it again a while later. An entry has to have been out of sight for an hour and a
     * half before its absence means anything (FeedImporter::GONE_AFTER_MINUTES): two reads a
     * minute apart are one look, not two.
     */
    private function readLater(EventFeed $feed): array
    {
        \Illuminate\Support\Facades\DB::table('event_feed_items')->update(['last_seen_at' => \Illuminate\Support\Facades\DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);

        return $this->read($feed);
    }

    private function eventNamed(string $name): ?Event
    {
        return Event::where('name', $name)->first();
    }

    private function itemFor(EventFeed $feed, string $uid): EventFeedItem
    {
        return $feed->items()->where('external_key', EventFeedItem::keyFor($uid))->firstOrFail();
    }

    /** Ten events, all read and made. */
    private function tenEvents(EventFeed $feed): void
    {
        $this->entries = [];
        for ($i = 1; $i <= 10; $i++) {
            $this->entries["e{$i}"] = $this->entry("e{$i}", "Event {$i}", 10 + $i);
        }
        $this->read($feed);
    }

    public function test_the_first_read_makes_the_events_and_the_second_does_nothing(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Frühlingskonzert', 10, 'LOCATION:Stadtsaal\, Hauptplatz 1'), $this->entry('b', 'Lesung', 12)];

        $first = $this->read($feed);

        $this->assertSame(['status' => 'ok', 'created' => 2, 'updated' => 0, 'matched' => 0, 'held' => 0, 'left_out' => 0], $first);
        $concert = $this->eventNamed('Frühlingskonzert');
        $this->assertSame([Event::IMPORT_FEED, 'abcdef012345', false], [$concert->import_source, $concert->import_batch, (bool) $concert->is_draft]);
        $this->assertSame(now('Europe/Vienna')->addDays(10)->setTime(19, 30)->utc()->format('Y-m-d H:i:s'), $concert->starts_at);
        $this->assertSame('Stadtsaal', $concert->venue->name);
        $this->assertSame($this->owner->id, $concert->user_id);

        $feed->refresh();
        $this->assertSame('ok', $feed->last_status);
        $this->assertNotNull($feed->baseline_done_at);
        $this->assertSame([0, 0, 0], [$feed->failure_count, $feed->waiting_count, $feed->decide_count]);
        $this->assertEqualsWithDelta(60, now()->diffInMinutes($feed->next_check_at), 6);
        $this->assertSame(2, $feed->items()->where('state', EventFeedItem::STATE_IMPORTED)->count());

        $stamps = Event::pluck('updated_at', 'id')->all();
        $second = $this->read($feed);

        $this->assertSame([0, 0], [$second['created'], $second['updated']]);
        $this->assertSame(2, Event::count());
        $this->assertEquals($stamps, Event::pluck('updated_at', 'id')->all());
        // Events made after the first read are not part of it.
        $this->entries[] = $this->entry('c', 'Später dazu', 14);
        $this->read($feed);
        $this->assertNull($this->eventNamed('Später dazu')->import_batch);
    }

    public function test_a_feed_that_holds_new_events_makes_drafts_and_counts_them_as_waiting(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::DRAFT]);
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];

        $this->read($feed);

        $this->assertSame([true, true], Event::orderBy('id')->pluck('is_draft')->map(fn ($draft) => (bool) $draft)->all());
        $this->assertSame(2, $feed->fresh()->waiting_count);

        // The owner publishes one: the count follows on the next read.
        $this->eventNamed('One')->forceFill(['is_draft' => false])->save();
        $this->read($feed);
        $this->assertSame(1, $feed->fresh()->waiting_count);
    }

    public function test_a_change_at_the_source_reaches_the_event_and_an_owners_edit_stays(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Concert'), $this->entry('b', 'Reading', 12)];
        $this->read($feed);
        $this->eventNamed('Reading')->forceFill(['name' => 'Reading (our title)'])->save();

        $this->entries = [$this->entry('a', 'Concert, sold out'), $this->entry('b', 'Reading, renamed there', 12)];
        $result = $this->read($feed);

        $this->assertSame(1, $result['updated']);
        $this->assertNotNull($this->eventNamed('Concert, sold out'));
        $this->assertNotNull($this->eventNamed('Reading (our title)'));
        $this->assertSame(2, Event::count());
    }

    /**
     * The schedule already has this event, made by hand. The feed stands beside it: it is never
     * written, cancelled or deleted, whatever the source does. Otherwise two schedules that read
     * each other could delete each other's originals.
     */
    public function test_an_event_the_schedule_already_has_is_linked_and_never_touched(): void
    {
        $byHand = $this->createEvent($this->role, [
            'creator_role_id' => $this->role->id, 'name' => 'Concert',
            'starts_at' => now('Europe/Vienna')->addDays(10)->setTime(19, 30)->utc()->format('Y-m-d H:i:s'),
        ]);
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('a', 'concert')];

        $this->assertSame(1, $this->read($feed)['matched']);
        $this->assertSame(1, Event::count());
        $item = $this->itemFor($feed, 'a');
        $this->assertSame([EventFeedItem::STATE_MATCHED, $byHand->id], [$item->state, $item->event_id]);
        $this->assertNull($byHand->fresh()->import_source);

        // The source renames it, then drops it. Twice.
        $this->entries = [$this->entry('a', 'Concert (renamed at the source)')];
        $this->read($feed);
        $this->entries = [];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $byHand->refresh();
        $this->assertSame('Concert', $byHand->name);
        $this->assertFalse((bool) $byHand->is_cancelled);
        $this->assertSame(1, Event::count());

        // A link and nothing more: nothing is kept about it, compared with it, or put aside.
        $item->refresh();
        $this->assertSame(EventFeedItem::STATE_MATCHED, $item->state);
        $this->assertNull($item->imported);
        $this->assertNull($item->pending);
    }

    /** A source drops what is over. An event that has happened is not un-happened by that. */
    public function test_an_event_that_has_happened_is_left_alone_when_the_source_drops_it(): void
    {
        $feed = $this->feed(['left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('a', 'Last week\'s concert', 3), $this->entry('b', 'Still to come', 30)];
        $this->read($feed);

        $this->travel(5)->days();
        $this->entries = [$this->entry('b', 'Still to come', 25)];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $past = $this->eventNamed('Last week\'s concert');
        $this->assertNotNull($past);
        $this->assertFalse((bool) $past->is_cancelled);
    }

    /**
     * A feed of posts lists its newest few. An event that has scrolled off it has not gone
     * anywhere, and only its own page can say that it has: twice, like everything else.
     */
    public function test_a_feed_of_posts_is_followed_to_each_posts_page_and_absence_from_it_means_nothing(): void
    {
        $when = now('Europe/Vienna')->addDays(10)->setTime(19, 30);
        $page = fn (string $name, string $more = '') => Http::response(
            '<html><head><script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => $name, 'startDate' => $when->toIso8601String()] + ($more ? ['eventStatus' => $more] : [])).'</script></head><body></body></html>',
            200, ['Content-Type' => 'text/html']
        );
        $rss = fn (string ...$items) => Http::response(
            '<?xml version="1.0"?><rss version="2.0"><channel><title>News</title>'.implode('', $items).'</channel></rss>', 200, ['Content-Type' => 'application/rss+xml']
        );
        $item = fn (string $id, string $title) => "<item><guid>{$id}</guid><title>{$title}</title><link>https://93.184.216.34/posts/{$id}</link><description>From the list.</description></item>";

        $feed = $this->feed(['kind' => EventFeed::KIND_ITEMS, 'can_see_leaving' => false, 'left_action' => EventFeed::LEFT_CANCEL], null, 'https://93.184.216.34/feed.xml');
        $this->pages = [
            '/feed.xml' => $rss($item('concert', 'Concert'), $item('reading', 'Reading'), $item('news', 'We have a new roof')),
            '/posts/concert' => $page('Concert'),
            '/posts/reading' => $page('Reading'),
            '/posts/news' => Http::response('<html><body>A new roof.</body></html>', 200, ['Content-Type' => 'text/html']),
        ];

        $first = $this->read($feed);

        $this->assertSame([2, 1], [$first['created'], $first['left_out']]);
        $this->assertSame('From the list.', $this->eventNamed('Concert')->description);
        $this->assertSame('no_date', $this->itemFor($feed, 'news')->pending['left_out']);
        $this->assertNotNull($feed->fresh()->baseline_done_at, 'a post that is not an event does not keep the first read open');

        // The two events scroll off the feed. Read after read, they are not "missing".
        $this->pages['/feed.xml'] = $rss($item('news', 'We have a new roof'));
        foreach (range(1, 4) as $read) {
            $this->read($feed);
        }
        $this->assertSame(0, Event::where('is_cancelled', true)->count());
        $this->assertSame(0, $this->itemFor($feed, 'concert')->missing_reads);

        // A day later each event's own page is asked again. One has been taken down.
        $this->travel(25)->hours();
        $this->pages['/posts/concert'] = Http::response('gone', 410);
        $this->read($feed);
        $this->assertFalse((bool) $this->eventNamed('Concert')->is_cancelled, 'once is not gone');

        $this->travel(25)->hours();
        $this->read($feed);
        $this->assertTrue((bool) $this->eventNamed('Concert')->is_cancelled);
        $this->assertFalse((bool) $this->eventNamed('Reading')->is_cancelled);
    }

    public function test_an_event_is_gone_only_when_it_is_missing_twice_and_then_the_feeds_setting_decides(): void
    {
        foreach ([
            EventFeed::LEFT_KEEP => fn (?Event $event) => $event && ! $event->is_cancelled,
            EventFeed::LEFT_CANCEL => fn (?Event $event) => $event && $event->is_cancelled,
            EventFeed::LEFT_DELETE => fn (?Event $event) => $event === null,
        ] as $action => $afterwards) {
            $role = $this->createRole($this->owner, 'talent');
            $feed = $this->feed(['left_action' => $action], $role, self::FEED.'?'.$action);
            $this->entries = [$this->entry('stays', "Stays {$action}"), $this->entry('goes', "Goes {$action}", 12)];
            $this->read($feed);

            $this->entries = [$this->entry('stays', "Stays {$action}")];
            $this->read($feed);
            $once = $this->eventNamed("Goes {$action}");
            $this->assertTrue($once && ! $once->is_cancelled, "{$action}: missing once is not gone");

            // Twice, but in the same minute: one look, not two.
            $this->read($feed);
            $this->assertTrue($this->eventNamed("Goes {$action}") && ! $this->eventNamed("Goes {$action}")->is_cancelled, "{$action}: twice in a minute is not gone");

            $this->readLater($feed);
            $this->assertTrue($afterwards($this->eventNamed("Goes {$action}")), "{$action}: missing twice, an hour and a half apart");
            $this->assertFalse((bool) $this->eventNamed("Stays {$action}")->is_cancelled);
        }

        // What the feed deleted is "removed", not dismissed: listed again, it comes back, for the
        // owner to look at.
        $this->entries[] = $this->entry('goes', 'Goes delete', 12);
        $this->read($feed);
        $back = $this->eventNamed('Goes delete');
        $this->assertNotNull($back);
        $this->assertTrue((bool) $back->is_draft);
        $this->assertSame(EventFeedItem::STATE_IMPORTED, $this->itemFor($feed, 'goes')->state);
    }

    /** It came back in between: the count starts again. */
    public function test_an_event_missing_once_then_back_then_missing_once_is_not_gone(): void
    {
        $feed = $this->feed();
        $both = [$this->entry('a', 'Stays'), $this->entry('b', 'Flickers', 12)];
        $one = [$this->entry('a', 'Stays')];

        foreach ([$both, $one, $both, $one] as $entries) {
            $this->entries = $entries;
            $this->read($feed);
        }

        $this->assertFalse((bool) $this->eventNamed('Flickers')->is_cancelled);
    }

    /** A read that could not tell is not a read in which everything was missing. */
    public function test_an_answer_that_cannot_be_read_takes_nothing_away_and_counts_against_the_feed(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Concert')];
        $this->read($feed);

        foreach ([
            'not_readable' => Http::response('<html><body>Down for maintenance</body></html>', 200, ['Content-Type' => 'text/html']),
            'http_error' => Http::response('oops', 500),
            'unreachable' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timed out'),
        ] as $status => $answer) {
            $this->answer = is_callable($answer) ? null : $answer;
            if (is_callable($answer)) {
                Http::swap(new \Illuminate\Http\Client\Factory);
                Http::preventStrayRequests();
                Http::fake($answer);
            }

            $this->assertSame($status, $this->read($feed)['status']);
            $this->assertSame($status, $feed->fresh()->last_status);
        }

        $feed->refresh();
        $this->assertSame(3, $feed->failure_count);
        // An hour, two, six: by the third it is not asked again for six hours.
        $this->assertEqualsWithDelta(360, now()->diffInMinutes($feed->next_check_at), 2);
        $this->assertFalse((bool) $this->eventNamed('Concert')->is_cancelled);
        $this->assertSame(0, $this->itemFor($feed, 'a')->missing_reads);
        $this->assertNull($feed->paused_at);
    }

    public function test_a_feed_that_has_failed_for_a_fortnight_is_paused(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Concert')];
        $this->read($feed);
        $this->answer = Http::response('gone', 410);

        $feed->forceFill(['last_success_at' => now()->subDays(13)])->save();
        $this->read($feed);
        $this->assertNull($feed->fresh()->paused_at);

        // A fortnight without a good read is not enough on its own: a feed resumed after a long
        // pause has not had one for weeks either, and one timeout must not pause it again.
        $feed->forceFill(['last_success_at' => now()->subDays(15)])->save();
        $this->read($feed);
        $this->assertNull($feed->fresh()->paused_at, 'few failures, long silence: not yet');

        $feed->forceFill(['failure_count' => FeedImporter::PAUSE_AFTER_FAILURES - 1])->save();
        $this->read($feed);
        $this->assertNotNull($feed->fresh()->paused_at);
        $this->assertSame(EventFeed::PAUSED_FAILING, $feed->fresh()->pause_reason);

        // Paused, it is not asked at all.
        $before = $this->asked;
        $this->assertSame('idle', $this->read($feed)['status']);
        $this->assertSame($before, $this->asked);
    }

    /**
     * People are coming. The feed does not call it off or remove it over their heads, and it
     * does not delete what the owner added to it.
     */
    public function test_an_event_people_signed_up_for_or_the_owner_worked_on_waits_for_the_owner(): void
    {
        foreach ([EventFeed::LEFT_CANCEL => 'a sale', EventFeed::LEFT_DELETE => 'a promo code'] as $action => $what) {
            $role = $this->createRole($this->owner, 'talent');
            $feed = $this->feed(['left_action' => $action], $role, self::FEED.'?'.$action);
            $this->entries = [$this->entry('a', "Stays {$action}"), $this->entry('b', "Guarded {$action}", 12)];
            $this->read($feed);
            $event = $this->eventNamed("Guarded {$action}");

            if ($action === EventFeed::LEFT_CANCEL) {
                $event->forceFill(['tickets_enabled' => true])->save();
                $this->createSale($event, $role, ['status' => 'paid'], $this->createTicket($event));
            } else {
                PromoCode::create(['event_id' => $event->id, 'code' => 'EARLY', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
            }

            $this->entries = [$this->entry('a', "Stays {$action}")];
            $this->read($feed);
            $this->readLater($feed);

            $event = $event->fresh();
            $this->assertNotNull($event, "{$what}: deleted");
            $this->assertFalse((bool) $event->is_cancelled, "{$what}: cancelled");
            $item = $this->itemFor($feed, 'b');
            $this->assertSame(EventFeedItem::STATE_DECIDE, $item->state);
            $this->assertSame(['kind' => 'gone'], $item->pending['decide']);
            $this->assertSame(1, $feed->fresh()->decide_count);

            // Listed again, there is nothing left to decide.
            $this->entries[] = $this->entry('b', "Guarded {$action}", 12);
            $this->read($feed);
            $this->assertSame(EventFeedItem::STATE_IMPORTED, $this->itemFor($feed, 'b')->state);
            $this->assertSame(0, $feed->fresh()->decide_count);
        }
    }

    /**
     * Six of ten events gone in one read is a source having a bad day far more often than six
     * cancellations. None of it is done, and it lets go by itself when they return.
     */
    public function test_a_read_that_would_take_a_large_share_of_the_events_is_held_whole(): void
    {
        $feed = $this->feed();
        $this->tenEvents($feed);
        $all = $this->entries;

        $this->entries = array_slice($all, 0, 4);
        $this->read($feed);
        $held = $this->readLater($feed);

        $this->assertSame(6, $held['held']);
        $this->assertSame(0, Event::where('is_cancelled', true)->count());
        $this->assertCount(6, $feed->fresh()->held_leaving);

        // Still held on the next read, and the next.
        $this->read($feed);
        $this->assertSame(0, Event::where('is_cancelled', true)->count());

        $this->entries = $all;
        $this->read($feed);
        $this->assertNull($feed->fresh()->held_leaving);
        $this->assertSame(0, Event::where('is_cancelled', true)->count());

        // A few going is acted on: two of ten is not a bad day.
        $this->entries = array_slice($all, 0, 8);
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(2, Event::where('is_cancelled', true)->count());
        $this->assertNull($feed->fresh()->held_leaving);
    }

    public function test_an_event_called_off_at_the_source_is_called_off_here_and_comes_back_with_it(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Concert')];
        $this->read($feed);
        $event = $this->eventNamed('Concert');

        $this->entries = [$this->entry('a', 'Concert', 10, 'STATUS:CANCELLED')];
        $this->read($feed);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
        $this->assertTrue($this->itemFor($feed, 'a')->cancelled_by_feed);

        // On again at the source: on again here.
        $this->entries = [$this->entry('a', 'Concert')];
        $this->read($feed);
        $this->assertFalse((bool) $event->fresh()->is_cancelled);

        // Called off there, and the owner puts it back on by hand: it stays as they put it.
        $this->entries = [$this->entry('a', 'Concert', 10, 'STATUS:CANCELLED')];
        $this->read($feed);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
        $event->fresh()->forceFill(['is_cancelled' => false, 'cancelled_at' => null])->save();
        $this->read($feed);
        $this->readLater($feed);
        $this->assertFalse((bool) $event->fresh()->is_cancelled);
    }

    public function test_an_event_postponed_past_the_window_or_listed_by_another_feed_has_not_gone_anywhere(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Postponed', 12), $this->entry('c', 'Also elsewhere', 14)];
        $this->read($feed);

        // A second feed of the same schedule shows the third event too.
        $other = $this->feed([], null, 'https://93.184.216.34/other.ics');
        $this->read($other);
        $this->assertSame(3, Event::count(), 'the second feed matched the events the first made');

        // In the first feed: one is put off by thirteen months, one is dropped.
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Postponed', 400)];
        $this->read($feed);
        $this->readLater($feed);
        $this->readLater($feed);

        $this->assertFalse((bool) $this->eventNamed('Postponed')->is_cancelled);
        $this->assertFalse((bool) $this->eventNamed('Also elsewhere')->is_cancelled, 'the other feed still lists it');
    }

    /**
     * The owner deleting an event the feed made is a decision, and stays one. An event that
     * vanished any other way was not decided by anybody, and comes back for them to look at.
     */
    public function test_an_event_the_owner_deleted_stays_deleted_and_one_that_vanished_comes_back_as_a_draft(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Deleted on purpose'), $this->entry('b', 'Vanished', 12)];
        $this->read($feed);

        $onPurpose = $this->eventNamed('Deleted on purpose');
        $this->itemFor($feed, 'a')->forceFill(['state' => EventFeedItem::STATE_DISMISSED])->save();
        $onPurpose->delete();
        $this->eventNamed('Vanished')->delete();

        // The source changes both, so that both are looked at again.
        $this->entries = [$this->entry('a', 'Deleted on purpose (updated)'), $this->entry('b', 'Vanished (updated)', 12)];
        $this->read($feed);

        $this->assertSame(['Vanished (updated)'], Event::pluck('name')->all());
        $this->assertTrue((bool) Event::first()->is_draft);
        $this->assertSame(1, $feed->fresh()->waiting_count);
    }

    public function test_nothing_is_read_for_a_schedule_whose_plan_has_no_feeds_or_that_nobody_owns(): void
    {
        $this->entries = [$this->entry('a', 'Concert')];

        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);
        $ownerless = $this->createRole($this->owner, 'talent');
        $ownerless->forceFill(['user_id' => null])->save();
        $deleted = $this->createRole($this->owner, 'talent', ['is_deleted' => true]);

        foreach ([$pro, $ownerless, $deleted] as $index => $role) {
            $feed = $this->feed([], $role, self::FEED.'?'.$index);
            $this->assertSame('idle', $this->read($feed)['status']);
            // Not a failure: nothing about the feed is counted or moved.
            $this->assertSame([0, null, null], [$feed->fresh()->failure_count, $feed->fresh()->last_status, $feed->fresh()->last_checked_at]);
        }

        $this->assertSame(0, $this->asked);
        $this->assertSame(0, Event::count());

        // A selfhost install has feeds on every plan.
        config(['app.hosted' => false]);
        $this->assertSame(1, $this->read($this->feed([], $pro, self::FEED.'?selfhost'))['created']);
    }

    /** A feed stops while the owner can still add an event by hand, and says it will go on tomorrow. */
    public function test_a_first_read_larger_than_the_day_allows_leaves_room_and_continues(): void
    {
        config([
            'usage.event_create_daily_limit_trial' => 10, 'usage.event_create_daily_limit_pro' => 10, 'usage.event_create_daily_limit_enterprise' => 10,
        ]);
        $feed = $this->feed();
        $this->tenEvents($feed);

        // A fifth of the day's ten is kept free.
        $this->assertSame(8, Event::count());
        $this->assertTrue($feed->fresh()->stats['continues_tomorrow']);
        $this->assertNull($feed->fresh()->baseline_done_at, 'the first read is not over');
        $this->assertTrue($this->role->fresh()->canCreateEvent($this->owner));

        // Tomorrow the rest are made, still as part of the first read.
        $this->travel(1)->days();
        foreach ($this->entries as $uid => $entry) {
            $this->entries[$uid] = $this->entry($uid, 'Event '.substr($uid, 1), 9 + (int) substr($uid, 1));
        }
        $this->read($feed);

        $this->assertSame(10, Event::count());
        $this->assertSame(10, Event::where('import_batch', 'abcdef012345')->count());
        $this->assertNotNull($feed->fresh()->baseline_done_at);
        $this->assertArrayNotHasKey('continues_tomorrow', $feed->fresh()->stats);
    }
}
