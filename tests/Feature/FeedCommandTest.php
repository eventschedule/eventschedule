<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * app:import-feeds, the run both cron rails start every minute: which feeds it reads, that only
 * one run reads at a time, and that one feed's trouble is not the others'. And the other end of
 * a feed's life with an event: the owner deleting it.
 */
class FeedCommandTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    private array $asked = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            $this->asked[] = basename(parse_url($request->url(), PHP_URL_PATH));

            return Http::response($this->calendar(basename(parse_url($request->url(), PHP_URL_PATH))), 200, ['Content-Type' => 'text/calendar']);
        });

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna']);
    }

    /** A calendar with one event, named after the file it is served as. */
    private function calendar(string $file, string $suffix = ''): string
    {
        $start = now('Europe/Vienna')->addDays(10)->setTime(19, 30)->format('Ymd\THis');

        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nBEGIN:VEVENT\r\nUID:{$file}\r\nSUMMARY:From {$file}{$this->suffix}\r\nDTSTART;TZID=Europe/Vienna:{$start}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
    }

    private string $suffix = '';

    private function feed(string $file, array $attrs = [], ?Role $role = null): EventFeed
    {
        $url = 'https://93.184.216.34/'.$file;

        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'name' => $file, 'url' => $url, 'url_hash' => EventFeed::hashOf($url),
            'host' => '93.184.216.34', 'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna',
            'publish_mode' => EventFeed::PUBLISH, 'can_see_leaving' => true,
        ]);
    }

    public function test_it_reads_what_is_due_and_nothing_else_and_a_second_run_finds_nothing_to_do(): void
    {
        $this->feed('never-read.ics');
        $this->feed('due.ics', ['next_check_at' => now()->subMinute()]);
        $this->feed('later.ics', ['next_check_at' => now()->addMinutes(30)]);
        $due = now()->subMinute()->startOfSecond();
        $paused = $this->feed('paused.ics', ['next_check_at' => $due, 'paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_BY_OWNER]);

        $this->artisan('app:import-feeds')->assertSuccessful();
        // A paused feed is not so much as looked at: when it is resumed, it is due.
        $this->assertTrue($due->equalTo($paused->fresh()->next_check_at));

        // One that has never been read goes first.
        $this->assertSame(['never-read.ics', 'due.ics'], $this->asked);
        $this->assertEqualsCanonicalizing(['From never-read.ics', 'From due.ics'], Event::pluck('name')->all());

        $this->artisan('app:import-feeds')->assertSuccessful();
        $this->assertCount(2, $this->asked, 'a feed just read is not due again');
        $this->assertSame(2, Event::count());

        // Asked for by name, a feed is read now whether or not it is due.
        $this->artisan('app:import-feeds', ['--feed' => EventFeed::where('name', 'later.ics')->value('id')])->assertSuccessful();
        $this->assertSame('later.ics', end($this->asked));
        $this->assertCount(3, $this->asked);
    }

    /** Both rails start it every minute, and an HTTP cron may call more than once a minute. */
    public function test_a_run_that_finds_another_reading_does_nothing(): void
    {
        $this->feed('due.ics');
        $held = Cache::lock('feeds.import', 120);
        $this->assertTrue($held->get());

        $this->artisan('app:import-feeds')->assertSuccessful();
        $this->assertSame([], $this->asked);

        $held->release();
        $this->artisan('app:import-feeds')->assertSuccessful();
        $this->assertSame(['due.ics'], $this->asked);

        // And it lets go when it is done.
        $this->assertTrue(Cache::lock('feeds.import', 120)->get());
    }

    /** Once an hour, not once a minute: the ledger is thousands of rows on a busy install. */
    public function test_the_ledger_is_pruned_once_an_hour(): void
    {
        $this->mock(FeedImporter::class, function ($mock) {
            $mock->shouldReceive('prune')->once()->andReturn(0);
        });

        $this->artisan('app:import-feeds')->assertSuccessful();
        $this->artisan('app:import-feeds')->assertSuccessful();

        $this->travel(61)->minutes();
        $this->mock(FeedImporter::class, function ($mock) {
            $mock->shouldReceive('prune')->once()->andReturn(0);
        });
        $this->artisan('app:import-feeds')->assertSuccessful();
    }

    public function test_a_feed_that_is_not_read_waits_an_hour_with_nothing_counted_against_it(): void
    {
        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);
        $offPlan = $this->feed('off-plan.ics', [], $pro);
        $this->feed('fine.ics');

        $this->artisan('app:import-feeds')->assertSuccessful();

        $this->assertSame(['fine.ics'], $this->asked);
        $offPlan->refresh();
        $this->assertEqualsWithDelta(60, now()->diffInMinutes($offPlan->next_check_at), 1);
        $this->assertSame([0, null, null], [$offPlan->failure_count, $offPlan->last_status, $offPlan->last_checked_at]);
    }

    public function test_one_feeds_trouble_is_counted_against_it_and_is_not_the_others(): void
    {
        $broken = $this->feed('broken.ics');
        $this->feed('fine.ics');

        $this->mock(FeedImporter::class, function ($mock) use ($broken) {
            $mock->shouldReceive('prune')->andReturn(0);
            $mock->shouldReceive('read')->andReturnUsing(function (EventFeed $feed) use ($broken) {
                if ($feed->id === $broken->id) {
                    throw new \RuntimeException('Something nobody expected');
                }

                return ['status' => 'ok'];
            });
        });

        $this->artisan('app:import-feeds')->assertSuccessful();

        $broken->refresh();
        $this->assertSame(['failed', 1], [$broken->last_status, $broken->failure_count]);
        $this->assertEqualsWithDelta(60, now()->diffInMinutes($broken->next_check_at), 1);
        // The lock was let go even so.
        $this->assertTrue(Cache::lock('feeds.import', 120)->get());
    }

    /**
     * The owner deletes an event a feed made. That is a decision about the feed too: the next
     * read, with the event changed at the source so that it is looked at again, does not bring
     * it back.
     */
    public function test_an_event_its_owner_deletes_is_not_brought_back(): void
    {
        $raw = 'testapikey_'.Str::random(24);
        $this->owner->forceFill(['api_key' => substr(hash('sha256', $raw), 0, 8), 'api_key_hash' => Hash::make($raw)])->save();

        foreach ([
            'portal.ics' => fn (Event $event) => $this->actingAs($this->owner)->delete(route('event.delete', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)])),
            'api.ics' => fn (Event $event) => $this->deleteJson('/api/events/'.UrlUtils::encodeId($event->id), [], ['X-API-Key' => $raw])->assertOk(),
        ] as $file => $delete) {
            $feed = $this->feed($file);
            $this->artisan('app:import-feeds')->assertSuccessful();
            $event = Event::where('name', 'From '.$file)->firstOrFail();

            $delete($event);

            $this->assertNull(Event::find($event->id), $file);
            $item = $feed->items()->firstOrFail();
            $this->assertSame([EventFeedItem::STATE_DISMISSED, null], [$item->state, $item->event_id]);

            $this->suffix = ' (changed at the source)';
            $this->artisan('app:import-feeds', ['--feed' => $feed->id])->assertSuccessful();
            $this->assertSame(0, Event::where('name', 'like', 'From '.$file.'%')->count(), $file.' came back');
            $this->suffix = '';
        }

        // An event made by hand has no feed to tell, and deleting it is what it always was.
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
        $this->actingAs($this->owner)->delete(route('event.delete', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($byHand->id)]));
        $this->assertNull(Event::find($byHand->id));
    }
}
