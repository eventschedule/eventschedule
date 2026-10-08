<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Notifications\FeedNotification;
use App\Services\Feeds\FeedActions;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Who hears what a feed needs somebody for, and how often (FeedNotifier).
 *
 * A feed runs with nobody watching, so the three things that do need a person have to reach one:
 * drafts to look over, a decision, and a feed that stopped being read. The rules held here are
 * the ones that keep those from being either silence or a flood.
 */
class FeedMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    private array $entries = [];

    private int $status = 200;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Notification::fake();
        Http::preventStrayRequests();
        Http::fake(fn () => $this->status === 200
            ? Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar'])
            : Http::response('gone', $this->status));

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna', 'show_event_interest' => true]);
    }

    private function entry(string $uid, string $name, int $days = 10, int $hour = 19): string
    {
        $start = now('Europe/Vienna')->addDays($days)->setTime($hour, 30);

        return "UID:{$uid}\nSUMMARY:{$name}\nDTSTART;TZID=Europe/Vienna:".$start->format('Ymd\THis')."\nDTEND;TZID=Europe/Vienna:".$start->copy()->addHours(2)->format('Ymd\THis');
    }

    private function calendar(): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($entry) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($entry))."\r\nEND:VEVENT\r\n", $this->entries))
            ."END:VCALENDAR\r\n";
    }

    private function feed(array $attrs = [], ?Role $role = null): EventFeed
    {
        $url = 'https://93.184.216.34/'.($attrs['file'] ?? 'calendar').'.ics';
        unset($attrs['file']);

        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'added_by' => $this->owner->id, 'name' => 'Town calendar', 'url' => $url,
            'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34', 'kind' => EventFeed::KIND_CALENDAR,
            'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true, 'publish_mode' => EventFeed::DRAFT,
            'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    private function read(EventFeed $feed, float $seconds = 30): array
    {
        return app(FeedImporter::class)->read($feed->fresh(), microtime(true) + $seconds);
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

    private function signUp(string $name): void
    {
        $event = Event::where('name', $name)->firstOrFail();
        $event->forceFill(['is_draft' => false])->save();
        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test', 'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->fresh()->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();
    }

    /** The kinds sent to this person, in order. */
    private function sentTo(object $who): array
    {
        return Notification::sent($who, FeedNotification::class)->map(fn (FeedNotification $notification) => $notification->kind())->values()->all();
    }

    private function factsSentTo(object $who, string $kind): array
    {
        return Notification::sent($who, FeedNotification::class, fn (FeedNotification $notification) => $notification->kind() === $kind)->first()->facts();
    }

    public function test_drafts_are_said_once_they_have_arrived_and_once_a_day(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];

        $this->read($feed);

        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $facts = $this->factsSentTo($this->owner, FeedNotification::REVIEW);
        $this->assertSame(2, $facts['count']);
        $this->assertSame('Town calendar', $facts['feed']);
        // A path to the feed's own page, at its drafts: the mail makes it absolute.
        $this->assertSame(route('role.feeds.show', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)], false).'#waiting', $facts['url']);

        // A read that adds nothing says nothing; and more drafts on the same day are not
        // another email, they are owed one.
        $this->read($feed);
        $this->entries[] = $this->entry('c', 'Three', 12);
        $this->read($feed);
        $this->assertCount(1, $this->sentTo($this->owner));
        $this->assertSame(1, $feed->fresh()->stats['to_tell']);

        // Tomorrow's first read pays it, with what is waiting then.
        $this->travel(21)->hours();
        $this->read($feed);
        $this->assertSame([FeedNotification::REVIEW, FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertSame(3, Notification::sent($this->owner, FeedNotification::class)->last()->facts()['count']);
        $this->assertArrayNotHasKey('to_tell', $feed->fresh()->stats);
    }

    public function test_drafts_already_looked_over_and_events_that_were_published_are_not_mailed_about(): void
    {
        // A feed that publishes has nothing to review.
        $publishing = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'publishing']);
        $this->entries = [$this->entry('a', 'One')];
        $this->read($publishing);
        $this->assertSame([], $this->sentTo($this->owner));

        // Owed an email, and dealt with before the day's email could go: nothing is owed.
        $drafts = $this->feed(['file' => 'drafts']);
        $this->entries = [$this->entry('b', 'Two', 11)];
        cache()->put('feeds.mailed.review.'.$this->role->id, 1, now()->addHours(5));
        $this->read($drafts);
        $this->assertSame(1, $drafts->fresh()->stats['to_tell']);
        app(FeedActions::class)->publish($drafts->fresh(), $this->role, $this->owner, $drafts->items()->pluck('id')->all());
        $this->travel(21)->hours();
        $this->read($drafts);

        $this->assertSame([], $this->sentTo($this->owner));
        $this->assertArrayNotHasKey('to_tell', $drafts->fresh()->stats);
    }

    /**
     * A feed switched to publishing, with drafts from before still waiting: what it publishes
     * from then on is not a reason to say "waiting for your review" again.
     */
    public function test_a_feed_that_now_publishes_does_not_mail_about_the_drafts_it_left(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $this->assertCount(1, $this->sentTo($this->owner));

        $this->travel(21)->hours();
        $feed->forceFill(['publish_mode' => EventFeed::PUBLISH])->save();
        $this->entries[] = $this->entry('c', 'Three', 12);
        $this->read($feed);

        $this->assertFalse((bool) Event::where('name', 'Three')->value('is_draft'));
        $this->assertSame(2, $feed->fresh()->waiting_count);
        $this->assertCount(1, $this->sentTo($this->owner));
    }

    /**
     * Drafts are said when a read ends with nothing left over. A read that made some and has
     * more to make says nothing yet and remembers what it owes; one that stopped because the
     * day's allowance is used up has said all it will today.
     */
    public function test_a_read_with_more_to_make_owes_the_email_and_one_out_of_allowance_sends_it(): void
    {
        $notifier = app(\App\Services\Feeds\FeedNotifier::class);
        $feed = $this->feed(['waiting_count' => 5]);

        $notifier->afterRead($feed, $this->role, ['created' => 5], true, []);
        $this->assertSame([], $this->sentTo($this->owner));
        $this->assertSame(5, $feed->fresh()->stats['to_tell']);

        // More arrive, and still more to come.
        $feed = $feed->fresh();
        $feed->forceFill(['waiting_count' => 8])->save();
        $notifier->afterRead($feed, $this->role, ['created' => 3], true, []);
        $this->assertSame(8, $feed->fresh()->stats['to_tell']);

        // Out of today's allowance: this is all there will be today.
        $feed = $feed->fresh();
        $feed->forceFill(['stats' => ['continues_tomorrow' => true] + $feed->stats])->save();
        $notifier->afterRead($feed, $this->role, ['created' => 0], true, []);
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertSame(8, $this->factsSentTo($this->owner, FeedNotification::REVIEW)['count']);
        $this->assertArrayNotHasKey('to_tell', $feed->fresh()->stats);
        $this->assertTrue($feed->fresh()->stats['continues_tomorrow']);
    }

    /** A first read of many events takes several runs: one email, when the last of them is in. */
    public function test_a_read_that_ran_out_of_time_waits_to_say_how_many(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11), $this->entry('c', 'Three', 12)];

        // No time at all: the list is taken in and nothing is made.
        $this->read($feed, -1);
        $this->assertSame([], $this->sentTo($this->owner));

        $this->read($feed);
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertSame(3, $this->factsSentTo($this->owner, FeedNotification::REVIEW)['count']);
    }

    public function test_a_decision_is_said_once_and_an_urgent_one_does_not_wait_for_tomorrow(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('far', 'Far off', 20), $this->entry('later', 'Later still', 30), $this->entry('soon', 'This weekend', 2)];
        $this->read($feed);
        foreach (['Far off', 'Later still', 'This weekend'] as $name) {
            $this->signUp($name);
        }

        // Two leave the feed. Gone on the second read that misses them.
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('soon', 'This weekend', 2)];
        $this->read($feed);
        $this->assertSame([], $this->sentTo($this->owner));
        $this->readLater($feed);

        $this->assertSame([FeedNotification::DECIDE], $this->sentTo($this->owner));
        $facts = $this->factsSentTo($this->owner, FeedNotification::DECIDE);
        // About the one that happens soonest, with how many signed up and how many more wait.
        $this->assertSame(['Far off', 'gone', 1, 1], [$facts['event'], $facts['says'], $facts['people'], $facts['more']]);
        $this->assertStringEndsWith('#decide', $facts['url']);

        // Still waiting on the next reads: nothing new to say.
        $this->read($feed);
        $this->readLater($feed);
        $this->assertCount(1, $this->sentTo($this->owner));

        // The same day, the one this weekend moves. That does not wait.
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('soon', 'This weekend', 2, 21)];
        $this->read($feed);
        $this->assertSame([FeedNotification::DECIDE, FeedNotification::DECIDE], $this->sentTo($this->owner));
        $urgent = Notification::sent($this->owner, FeedNotification::class)->last()->facts();
        $this->assertSame(['This weekend', 'moved', 0], [$urgent['event'], $urgent['says'], $urgent['more']]);

        // Urgent is not "on every read until somebody answers": still moved, still waiting,
        // nothing new to say.
        $this->read($feed);
        $this->readLater($feed);
        $this->assertCount(2, $this->sentTo($this->owner));
    }

    /** Called off at the source, this weekend, with people signed up: said at once, and once. */
    public function test_an_urgent_cancellation_at_the_source_is_said_once(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'This weekend', 2)];
        $this->read($feed);
        $this->signUp('This weekend');

        $this->entries[1] = $this->entry('b', 'This weekend', 2)."\nSTATUS:CANCELLED";
        $this->read($feed);

        $this->assertSame([FeedNotification::DECIDE], $this->sentTo($this->owner));
        $this->assertSame('cancelled', $this->factsSentTo($this->owner, FeedNotification::DECIDE)['says']);
        $this->assertFalse((bool) Event::where('name', 'This weekend')->value('is_cancelled'));

        $this->read($feed);
        $this->readLater($feed);
        $this->assertCount(1, $this->sentTo($this->owner));
    }

    public function test_a_second_decision_on_a_day_that_is_not_urgent_waits(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'First', 20), $this->entry('c', 'Second', 25)];
        $this->read($feed);
        $this->signUp('First');
        $this->signUp('Second');

        $this->entries = [$this->entry('a', 'Stays'), $this->entry('c', 'Second', 25)];
        $this->read($feed);
        $this->readLater($feed);
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);

        $this->assertSame([FeedNotification::DECIDE], $this->sentTo($this->owner));
        $this->assertSame('First', $this->factsSentTo($this->owner, FeedNotification::DECIDE)['event']);
        $this->assertSame(2, $feed->fresh()->decide_count);

        // It waits for tomorrow's email, and tomorrow it is written. Only what a read had just
        // raised was ever looked at, so this one was mailed by nobody, on that day or any other.
        $this->assertCount(1, $feed->fresh()->stats['decide_owed']);
        $this->travel(21)->hours();
        $this->read($feed);

        $this->assertSame([FeedNotification::DECIDE, FeedNotification::DECIDE], $this->sentTo($this->owner));
        $second = Notification::sent($this->owner, FeedNotification::class)->last()->facts();
        $this->assertSame(['Second', 0], [$second['event'], $second['more']]);
        $this->assertArrayNotHasKey('items', $second, 'which ones are owed is the feed\'s own note, not part of the email');
        $this->assertArrayNotHasKey('decide_owed', $feed->fresh()->stats);

        // Once.
        $this->travel(21)->hours();
        $this->read($feed);
        $this->assertCount(2, $this->sentTo($this->owner));
    }

    /** A decision answered before its email was due is not written about. */
    public function test_a_decision_answered_before_its_email_was_due_is_not_mailed(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'First', 20), $this->entry('c', 'Second', 25)];
        $this->read($feed);
        $this->signUp('First');
        $this->signUp('Second');
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('c', 'Second', 25)];
        $this->read($feed);
        $this->readLater($feed);
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertCount(1, $feed->fresh()->stats['decide_owed']);

        $second = $feed->items()->where('external_key', \App\Models\EventFeedItem::keyFor('c'))->firstOrFail();
        app(\App\Services\Feeds\FeedActions::class)->keep($feed->fresh(), $second);
        $this->travel(21)->hours();
        $this->read($feed);

        $this->assertCount(1, $this->sentTo($this->owner));
        $this->assertArrayNotHasKey('decide_owed', $feed->fresh()->stats);
    }

    /**
     * A mail server that is down for an hour was the one email about forty drafts, gone: the
     * day's allowance was used and the feed no longer owed anything. An email that reached
     * nobody is owed again, and the next read sends it.
     */
    public function test_an_email_that_reached_nobody_is_owed_again(): void
    {
        $mail = new class extends \Illuminate\Support\Testing\Fakes\NotificationFake
        {
            public bool $down = true;

            public function send($notifiables, $notification)
            {
                if ($this->down) {
                    throw new \RuntimeException('Connection could not be established with host "smtp.example.test"');
                }

                parent::send($notifiables, $notification);
            }
        };
        Notification::swap($mail);

        // Drafts.
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $this->assertSame([], $this->sentTo($this->owner));
        $this->assertGreaterThan(0, $feed->fresh()->stats['to_tell'] ?? 0);

        $mail->down = false;
        $this->read($feed);
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertArrayNotHasKey('to_tell', $feed->fresh()->stats);

        // A decision.
        $publishing = $this->feed(['file' => 'second', 'publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('x', 'Stays'), $this->entry('y', 'Leaves', 20)];
        $this->read($publishing);
        $this->signUp('Leaves');
        $this->entries = [$this->entry('x', 'Stays')];
        $this->read($publishing);
        $mail->down = true;
        $this->readLater($publishing);
        $this->assertSame(1, $publishing->fresh()->decide_count);
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertCount(1, $publishing->fresh()->stats['decide_owed']);

        $mail->down = false;
        $this->read($publishing);
        $this->assertSame([FeedNotification::REVIEW, FeedNotification::DECIDE], $this->sentTo($this->owner));
        $this->assertArrayNotHasKey('decide_owed', $publishing->fresh()->stats);
    }

    /**
     * One run reads every feed that is due under one lock. A mail server that takes ten seconds
     * to answer was ten seconds in which no feed on the install was read, so what a read has to
     * say waits until the run lets go.
     */
    public function test_mail_waits_while_a_run_holds_the_lock(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One')];
        $importer = app(FeedImporter::class);

        $importer->holdMail();
        $importer->read($feed->fresh(), microtime(true) + 30);
        $this->assertSame([], $this->sentTo($this->owner));

        $importer->sendHeldMail();
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));

        // And nothing is sent twice by letting go again.
        $importer->sendHeldMail();
        $this->assertCount(1, $this->sentTo($this->owner));
    }

    public function test_a_feed_that_stops_being_read_is_said_after_three_days_and_again_when_it_is_paused(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'One')];
        $this->read($feed);
        $this->status = 500;

        // The first days of trouble are a source having a bad week.
        foreach ([1, 1] as $days) {
            $this->travel($days)->days();
            $this->read($feed);
        }
        $this->assertSame([], $this->sentTo($this->owner));

        $this->travel(2)->days();
        $this->read($feed);
        $this->assertSame([FeedNotification::FAILING], $this->sentTo($this->owner));
        $this->assertNotEmpty($this->factsSentTo($this->owner, FeedNotification::FAILING)['since']);

        // Not every day after that.
        foreach (range(1, 6) as $day) {
            $this->travel(1)->days();
            $this->read($feed);
        }
        $this->assertCount(1, $this->sentTo($this->owner));

        // Two weeks without one good read: paused, and said.
        $this->travel(6)->days();
        $this->read($feed);
        $this->assertTrue($feed->fresh()->isPaused());
        $this->assertSame([FeedNotification::FAILING, FeedNotification::PAUSED], $this->sentTo($this->owner));

        // It comes back, and stops again months later: that is told again.
        app(FeedActions::class)->resume($feed->fresh());
        $this->status = 200;
        $this->read($feed);
        $this->assertArrayNotHasKey('failing_told', $feed->fresh()->stats ?? []);
        $this->status = 404;
        $this->travel(4)->days();
        $this->read($feed);
        $this->assertSame([FeedNotification::FAILING, FeedNotification::PAUSED, FeedNotification::FAILING], $this->sentTo($this->owner));
    }

    /** When half of all sources fail together the fault is ours: nobody is told theirs is broken, and none is paused. */
    public function test_many_sources_failing_at_once_is_not_each_owners_news(): void
    {
        // Ten sites being read, five of them failing since this morning, as they would after an
        // outage of ours. This one has gone long enough to be mailed about, and that one to be paused.
        foreach (range(1, 8) as $n) {
            $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'other'.$n, 'host' => "site{$n}.example", 'last_success_at' => now()->subHours(2), 'last_checked_at' => now()->subHour()])
                ->forceFill($n <= 5 ? ['failure_count' => 2] : [])->save();
        }
        $days = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'days', 'host' => 'days.example', 'last_success_at' => now()->subDays(4)]);
        $weeks = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'weeks', 'host' => 'weeks.example', 'last_success_at' => now()->subDays(20), 'failure_count' => FeedImporter::PAUSE_AFTER_FAILURES]);
        $this->status = 500;
        $this->assertTrue(EventFeed::manyFailing());

        $this->read($days);
        $this->read($weeks);

        $this->assertSame([], $this->sentTo($this->owner));
        $this->assertFalse($weeks->fresh()->isPaused());
        $this->assertSame(FeedImporter::PAUSE_AFTER_FAILURES + 1, $weeks->fresh()->failure_count);

        // The others recover. Now these two are their own schedules' news.
        EventFeed::where('url_hash', '!=', $days->url_hash)->where('url_hash', '!=', $weeks->url_hash)->update(['failure_count' => 0]);
        $this->assertFalse(EventFeed::manyFailing());
        $this->read($days);
        $this->read($weeks);

        $this->assertSame([FeedNotification::FAILING, FeedNotification::PAUSED], $this->sentTo($this->owner));
        $this->assertTrue($weeks->fresh()->isPaused());
    }

    /** What counts as many: at least five SITES, half of those being read, failing within the day. */
    public function test_what_counts_as_many_sources_failing(): void
    {
        $feeds = fn (int $n, array $more = [], ?string $host = null) => collect(range(1, $n))->each(fn ($i) => $this->feed(['file' => uniqid('f', true), 'host' => $host ?? uniqid('site').'.example'])
            ->forceFill($more + ['last_checked_at' => now()->subHour()])->save());
        $failing = fn (int $n, array $more = [], ?string $host = null) => $feeds($n, $more + ['failure_count' => 1], $host);

        $failing(4);
        $this->assertFalse(EventFeed::manyFailing(), 'four is a few');
        $failing(1);
        $this->assertTrue(EventFeed::manyFailing());
        $feeds(5);
        $this->assertTrue(EventFeed::manyFailing(), 'five of ten is half');
        $feeds(1);
        $this->assertFalse(EventFeed::manyFailing(), 'five of eleven is not');

        // A feed that is never asked (its schedule deleted, its plan lapsed) is not one of "all".
        EventFeed::query()->delete();
        $failing(5);
        $feeds(20, ['last_checked_at' => now()->subDays(5)]);
        $this->assertTrue(EventFeed::manyFailing(), 'feeds nobody reads do not make half harder to reach');

        // Six feeds on ONE site that went down are one source's trouble, however many feeds.
        EventFeed::query()->delete();
        $failing(6, [], 'one-site.example');
        $this->assertFalse(EventFeed::manyFailing());

        // Paused feeds are not being read, and a failure from last week is not this outage.
        EventFeed::query()->delete();
        $failing(5, ['paused_at' => now()]);
        $failing(5, ['last_checked_at' => now()->subDays(2)->subHour()]);
        $this->assertFalse(EventFeed::manyFailing());
    }

    public function test_every_member_who_runs_the_schedule_and_wants_it_is_told_and_the_shared_address(): void
    {
        $admin = User::factory()->create(['language_code' => 'de']);
        $quiet = User::factory()->create();
        $viewer = User::factory()->create();
        $this->role->users()->attach($admin->id, ['level' => 'admin']);
        $this->role->users()->attach($quiet->id, ['level' => 'admin', 'notification_settings' => json_encode(['feed' => false])]);
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->role->forceFill(['notification_email' => 'team@schedule.test', 'notification_email_verified_at' => now()])->save();
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One')];

        $this->read($feed);

        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($admin));
        $this->assertSame([], $this->sentTo($quiet));
        $this->assertSame([], $this->sentTo($viewer));
        // In the reader's own language.
        $this->assertSame('de', Notification::sent($admin, FeedNotification::class)->first()->locale);
        Notification::assertSentTo(new AnonymousNotifiable, FeedNotification::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'team@schedule.test');

        // The shared address can turn it off for itself.
        Notification::fake();
        $this->role->forceFill(['notification_email_settings' => ['feed' => false]])->save();
        $this->entries[] = $this->entry('b', 'Two', 11);
        $this->travel(21)->hours();
        $this->read($feed);
        $this->assertSame([FeedNotification::REVIEW], $this->sentTo($this->owner));
        Notification::assertNotSentTo(new AnonymousNotifiable, FeedNotification::class);
    }

    public function test_the_mail_says_what_happened_and_opens_the_place_to_deal_with_it(): void
    {
        $url = route('role.feeds.show', ['subdomain' => $this->role->subdomain, 'hash' => 'abc'], false);
        $render = fn (string $kind, array $facts) => (new FeedNotification($this->role, $kind, ['feed' => 'Town <b>calendar</b>', 'url' => $url] + $facts))->toMail($this->owner);

        $review = $render(FeedNotification::REVIEW, ['count' => 12]);
        $this->assertSame('12 events from Town <b>calendar</b> are waiting for your review', $review->subject);
        $html = (string) $review->render();
        $this->assertStringContainsString('12 events from Town &lt;b&gt;calendar&lt;/b&gt; are waiting for your review.', $html);
        $this->assertStringContainsString('href="'.e(app_url($url)).'"', $html);
        $this->assertStringContainsString(__('messages.feeds_mail_button_review'), $html);
        $this->assertStringContainsString(__('messages.unsubscribe'), $html);

        $this->assertSame('1 event from Town <b>calendar</b> is waiting for your review', $render(FeedNotification::REVIEW, ['count' => 1])->subject);

        $decide = $render(FeedNotification::DECIDE, ['event' => 'Open mic', 'says' => 'gone', 'people' => 14, 'more' => 2]);
        $this->assertSame('Open mic is no longer in the feed', $decide->subject);
        $html = (string) $decide->render();
        $this->assertStringContainsString('14 people signed up for it', $html);
        // Which feed, since the sentence is about the event: a schedule may read several.
        $this->assertStringContainsString(__('messages.feeds_col_feed').': Town &lt;b&gt;calendar&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>calendar</b>', $html);
        $this->assertStringContainsString('2 more events need your decision too.', $html);
        $this->assertSame('Open mic has moved at the source', $render(FeedNotification::DECIDE, ['event' => 'Open mic', 'says' => 'moved', 'people' => 1, 'more' => 0])->subject);
        $this->assertSame('Open mic is cancelled at the source', $render(FeedNotification::DECIDE, ['event' => 'Open mic', 'says' => 'cancelled', 'people' => 1, 'more' => 0])->subject);
        $this->assertStringNotContainsString('need your decision too', (string) $render(FeedNotification::DECIDE, ['event' => 'Open mic', 'says' => 'moved', 'people' => 1, 'more' => 0])->render());
        // Held because of the owner's own work on it, with nobody signed up: the mail does not
        // say that anybody did.
        $nobody = (string) $render(FeedNotification::DECIDE, ['event' => 'Open mic', 'says' => 'gone', 'people' => 0, 'more' => 0])->render();
        $this->assertStringNotContainsString('signed up', $nobody);
        $this->assertStringContainsString('Open mic is no longer in the feed.', $nobody);

        // The day is the schedule's: 23:30 UTC on the 4th is already the 5th in Vienna.
        $failing = $render(FeedNotification::FAILING, ['since' => '2026-11-04T23:30:00+00:00']);
        $this->assertSame('Town <b>calendar</b> has not been read since 5 November', $failing->subject);
        $this->assertStringContainsString(__('messages.feeds_mail_failing_text'), (string) $failing->render());

        $paused = $render(FeedNotification::PAUSED, []);
        $this->assertSame('Town <b>calendar</b> is paused: it could not be read for two weeks', $paused->subject);
        $this->assertStringContainsString(__('messages.feeds_mail_paused_text'), (string) $paused->render());
    }

    public function test_the_preference_is_on_until_somebody_turns_it_off_and_is_shown_where_feeds_are(): void
    {
        $form = fn (Role $role) => $this->actingAs($this->owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $html = $form($this->role);
        $this->assertMatchesRegularExpression('/id="notification_feed" name="notification_feed" value="1"\s+checked/', $html);
        $this->assertStringContainsString(__('messages.notify_feed_help'), $html);

        // Not offered on a plan that cannot have a feed.
        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);
        $this->assertStringNotContainsString('name="notification_feed"', $form($pro));

        $this->assertTrue($this->role->getEditorsWantingNotification('feed')->contains('id', $this->owner->id));
        $this->role->users()->updateExistingPivot($this->owner->id, ['notification_settings' => json_encode(['feed' => false])]);
        $this->assertFalse($this->role->getEditorsWantingNotification('feed')->contains('id', $this->owner->id));
        $this->assertMatchesRegularExpression('/id="notification_feed" name="notification_feed" value="1"\s+class/', $form($this->role));
        $this->assertTrue($this->role->notificationEmailSettings()['feed']);
        $this->assertContains('feed', Role::NOTIFICATION_EMAIL_TYPES);
    }

    /** The two toggles are saved by the schedule form: the member's own, and the shared address's. */
    public function test_the_schedule_form_saves_both_toggles(): void
    {
        $this->role->forceFill(['notification_email' => 'team@schedule.test', 'notification_email_verified_at' => now()])->save();
        $save = fn (array $fields) => $this->actingAs($this->owner)
            ->put(route('role.update', ['subdomain' => $this->role->subdomain]), $fields + [
                'name' => $this->role->name, 'email' => $this->role->email, 'timezone' => $this->role->timezone,
                'language_code' => 'en', 'new_subdomain' => $this->role->subdomain, 'notification_email' => 'team@schedule.test',
            ])->assertSessionHasNoErrors();
        $mine = fn () => json_decode($this->role->users()->where('user_id', $this->owner->id)->first()->pivot->notification_settings ?? '{}', true);

        $save(['notification_feed' => '0', 'notification_email_feed' => '0']);
        $this->assertFalse($mine()['feed']);
        $this->assertFalse($this->role->fresh()->notificationEmailSettings()['feed']);
        $this->assertFalse($this->role->fresh()->getEditorsWantingNotification('feed')->contains('id', $this->owner->id));
        $this->assertNull($this->role->fresh()->getNotificationEmailWanting('feed'));

        $save(['notification_feed' => '1', 'notification_email_feed' => '1']);
        $this->assertTrue($mine()['feed']);
        $this->assertSame('team@schedule.test', $this->role->fresh()->getNotificationEmailWanting('feed'));

        // The shared one is a switch, and is refused as anything else.
        $this->actingAs($this->owner)->put(route('role.update', ['subdomain' => $this->role->subdomain]), [
            'name' => $this->role->name, 'email' => $this->role->email, 'timezone' => $this->role->timezone,
            'language_code' => 'en', 'new_subdomain' => $this->role->subdomain, 'notification_email_feed' => 'banana',
        ])->assertSessionHasErrors('notification_email_feed');

        // A save that does not carry them leaves them as they are.
        $save(['notification_feed' => '0']);
        $save([]);
        $this->assertFalse($mine()['feed']);
        $this->assertTrue($this->role->fresh()->notificationEmailSettings()['feed']);
    }
}
