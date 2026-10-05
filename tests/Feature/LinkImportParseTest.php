<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\UsageDaily;
use App\Models\User;
use App\Services\DemoService;
use App\Services\LinkImportService;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A link pasted on the import page: POST /{subdomain}/parse with source_url.
 *
 * The readers have their own tests. This one is about the request around them: what is fetched
 * and what must never be, which paths cost an AI request, who may ask, and that what comes back
 * can be saved through the ordinary import endpoint as the event it describes.
 *
 * Every address is an IP literal. The fetch guard resolves a hostname before it connects, and
 * that lookup is real; a literal skips it, so Http::fake() sees every request there is.
 */
class LinkImportParseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FEED = 'https://93.184.216.34/calendar.ics';

    private const PAGE = 'https://93.184.216.34/whats-on';

    private User $owner;

    private Role $role;

    private array $prompts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-10 16:00:00', 'UTC'));
        Http::preventStrayRequests();
        // No AI at all unless a test turns it on: most of this must work without it.
        config(['services.google.gemini_key' => null, 'services.openai.api_key' => null, 'app.hosted' => true]);

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'curator', ['timezone' => 'America/New_York']);
        $this->actingAs($this->owner);
    }

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);

        parent::tearDown();
    }

    private function feed(string ...$events): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($event) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($event))."\r\nEND:VEVENT\r\n", $events))
            ."END:VCALENDAR\r\n";
    }

    private function serveFeed(string $body, array $headers = ['Content-Type' => 'text/calendar; charset=utf-8']): void
    {
        Http::fake(['93.184.216.34/calendar.ics' => Http::response($body, 200, $headers)]);
    }

    private function servePage(string $html): void
    {
        Http::fake(['93.184.216.34/whats-on' => Http::response($html, 200, ['Content-Type' => 'text/html; charset=utf-8'])]);
    }

    private function pageWith(array ...$events): string
    {
        $blocks = implode('', array_map(fn ($event) => '<script type="application/ld+json">'.json_encode($event).'</script>', $events));

        return '<html><head><title>What\'s on at the Hall</title>'.$blocks.'</head><body><nav>Home About Contact</nav>'
            .'<main><h1>Events</h1><p>Jazz Night on Tuesday 20 October at 8pm. Tickets at the door.</p></main>'
            .'<script>var tracking = "do not read me";</script></body></html>';
    }

    private function ldEvent(array $overrides = []): array
    {
        return array_merge(['@context' => 'https://schema.org', '@type' => 'MusicEvent', 'name' => 'Jazz Night',
            'startDate' => '2026-10-20T20:00:00-04:00'], $overrides);
    }

    private function parse(string $link, array $extra = [], ?Role $role = null)
    {
        return $this->postJson(
            route('event.parse', ['subdomain' => ($role ?? $this->role)->subdomain]),
            ['source_url' => $link] + $extra
        );
    }

    private function withAi(array $rows = [['event_name' => 'Jazz Night', 'event_date_time' => '2026-10-20 20:00']]): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        GeminiUtils::fakeResponses(function ($prompt) use ($rows) {
            $this->prompts[] = $prompt;

            return $rows;
        });
    }

    private function aiRequests(): int
    {
        return (int) UsageDaily::where('operation', UsageTrackingService::GEMINI_PARSE_EVENT)
            ->where('role_id', $this->role->id)->sum('count');
    }

    private function useUpTheAllowance(): void
    {
        UsageDaily::create(['date' => now()->toDateString(), 'operation' => UsageTrackingService::GEMINI_PARSE_EVENT,
            'role_id' => $this->role->id, 'count' => 5000]);
    }

    public function test_a_calendar_feed_is_read_with_no_ai_key_and_no_allowance_left(): void
    {
        $this->serveFeed($this->feed(
            "UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z\nDTEND:20261021T020000Z\nLOCATION:The Blue Room\\, 12 Main St",
            "UID:b\nSUMMARY:Quiz\nDTSTART:20261022T000000Z",
        ));
        $this->useUpTheAllowance();

        $response = $this->parse(self::FEED)->assertOk();

        $this->assertSame(['Open Mic', 'Quiz'], array_column($response->json('parsed'), 'event_name'));
        $this->assertSame('2026-10-20 20:00', $response->json('parsed.0.event_date_time'));
        $this->assertSame('The Blue Room', $response->json('parsed.0.venue_name'));
        $this->assertSame('ics', $response->json('meta.source'));
        $this->assertSame('93.184.216.34', $response->json('meta.host'));
        $this->assertSame(2, $response->json('meta.found'));
        $this->assertSame('America/New_York', $response->json('meta.timezone'));
        // The allowance row is the one this test wrote: reading a feed spent nothing.
        $this->assertSame(5000, $this->aiRequests());
    }

    public function test_a_feed_is_recognised_by_what_it_is_not_by_its_label(): void
    {
        $this->serveFeed($this->feed("UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z"), ['Content-Type' => 'text/plain']);

        $this->parse(self::FEED)->assertOk()->assertJsonPath('meta.source', 'ics');
    }

    public function test_a_subscription_address_is_read_over_https(): void
    {
        $this->serveFeed($this->feed("UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z"));

        $this->parse('webcal://93.184.216.34/calendar.ics')->assertOk()->assertJsonPath('meta.source', 'ics');

        Http::assertSent(fn ($request) => $request->url() === self::FEED);
    }

    public function test_a_pages_own_event_data_is_read_without_ai(): void
    {
        $this->servePage($this->pageWith(
            $this->ldEvent(['location' => ['@type' => 'Place', 'name' => 'The Hall']]),
            $this->ldEvent(['name' => 'Second Night', 'startDate' => '2026-10-21T20:00:00-04:00']),
        ));

        $response = $this->parse(self::PAGE)->assertOk();

        $this->assertSame(['Jazz Night', 'Second Night'], array_column($response->json('parsed'), 'event_name'));
        $this->assertSame('page', $response->json('meta.source'));
        $this->assertSame('The Hall', $response->json('parsed.0.venue_name'));
        // With no AI key there is no "read the whole page" to offer.
        $this->assertFalse($response->json('meta.can_read_whole_page'));
        $this->assertSame(0, $this->aiRequests());
        Http::assertSentCount(1);
    }

    public function test_a_page_with_nothing_marked_up_is_read_by_the_model_once(): void
    {
        $this->servePage($this->pageWith());
        $this->withAi();

        $response = $this->parse(self::PAGE)->assertOk();

        $this->assertSame('page_ai', $response->json('meta.source'));
        $this->assertSame(['Jazz Night'], array_column($response->json('parsed'), 'event_name'));
        // A page that describes one event is that event's link.
        $this->assertSame(self::PAGE, $response->json('parsed.0.registration_url'));
        $this->assertSame(1, $this->aiRequests());

        $this->assertCount(1, $this->prompts);
        $prompt = $this->prompts[0];
        $this->assertStringContainsString('This is the text of a web page.', $prompt);
        $this->assertStringContainsString("What's on at the Hall", $prompt);
        $this->assertStringContainsString('Jazz Night on Tuesday 20 October at 8pm.', $prompt);
        // Not the page's scripts, and not its navigation.
        $this->assertStringNotContainsString('do not read me', $prompt);
        $this->assertStringNotContainsString('Home About Contact', $prompt);
    }

    public function test_a_long_page_is_cut_to_what_the_model_takes_and_says_so(): void
    {
        $paragraphs = '';
        foreach (range(1, 400) as $n) {
            $paragraphs .= "<p>Event number {$n}: a long evening of music and conversation at the Hall.</p>";
        }
        $this->servePage('<html><body><main>'.$paragraphs.'</main></body></html>');
        $this->withAi([
            ['event_name' => 'One', 'event_date_time' => '2026-10-20 20:00'],
            ['event_name' => 'Two', 'event_date_time' => '2026-10-21 20:00'],
        ]);

        $response = $this->parse(self::PAGE)->assertOk();

        $this->assertTrue($response->json('meta.text_truncated'));
        $pageText = strstr(strstr($this->prompts[0], 'Event number 1:'), "\nThe date today is", true);
        $this->assertLessThanOrEqual(10000, mb_strlen($pageText));
        // Cut at the end of a line, not through the middle of one.
        $this->assertStringEndsWith('at the Hall.', rtrim($pageText));
        // A listing is nobody's link in particular.
        $this->assertSame(['', ''], array_column($response->json('parsed'), 'registration_url'));
    }

    public function test_asking_for_the_whole_page_reads_past_its_event_data(): void
    {
        $this->servePage($this->pageWith($this->ldEvent()));
        $this->withAi([
            ['event_name' => 'Jazz Night', 'event_date_time' => '2026-10-20 20:00'],
            ['event_name' => 'Unmarked Extra', 'event_date_time' => '2026-10-27 20:00'],
        ]);

        // On its own the page's data wins, and the whole-page read is on offer.
        $this->parse(self::PAGE)->assertOk()
            ->assertJsonPath('meta.source', 'page')
            ->assertJsonPath('meta.can_read_whole_page', true);
        $this->assertSame(0, $this->aiRequests());

        $response = $this->parse(self::PAGE, ['source_mode' => 'page'])->assertOk();

        $this->assertSame('page_ai', $response->json('meta.source'));
        $this->assertSame(['Jazz Night', 'Unmarked Extra'], array_column($response->json('parsed'), 'event_name'));
        $this->assertSame(1, $this->aiRequests());
    }

    public function test_a_page_that_needs_the_model_says_why_it_cannot_be_read(): void
    {
        $this->servePage($this->pageWith());

        // No AI configured on this install.
        $this->parse(self::PAGE)->assertStatus(422)->assertJsonPath('reason', 'needs_ai');

        // Configured, and today's allowance is gone.
        $this->withAi();
        $this->useUpTheAllowance();
        $this->parse(self::PAGE)->assertStatus(422)->assertJsonPath('reason', 'ai_limit');
        $this->assertSame([], $this->prompts);
    }

    public function test_a_page_the_model_finds_nothing_on_has_no_events(): void
    {
        $this->servePage($this->pageWith());
        // What "[]" from the model arrives as.
        $this->withAi([[]]);

        $this->parse(self::PAGE)->assertStatus(422)->assertJsonPath('reason', 'no_events');
        // The request was made, and counts.
        $this->assertSame(1, $this->aiRequests());
    }

    public function test_links_that_cannot_work_are_turned_away_before_anything_is_fetched(): void
    {
        Http::fake();

        foreach ([
            'https://www.facebook.com/thehall/events' => 'needs_screenshot',
            'https://m.facebook.com/events/123' => 'needs_screenshot',
            'https://instagram.com/thehall' => 'needs_screenshot',
            'not a link' => 'invalid_url',
            'file:///etc/passwd' => 'invalid_url',
            'ftp://93.184.216.34/calendar.ics' => 'invalid_url',
            // Addresses on the inside read exactly like addresses that are down.
            'http://127.0.0.1/admin' => 'unreachable',
            'http://169.254.169.254/latest/meta-data/' => 'unreachable',
            'http://10.0.0.5/calendar.ics' => 'unreachable',
        ] as $link => $reason) {
            $this->parse($link)->assertStatus(422)->assertJsonPath('reason', $reason);
            // Stay under the per-minute limit: this loop is one person's nine tries.
            $this->travel(10)->seconds();
        }

        Http::assertNothingSent();

        $this->assertStringContainsString('Facebook', $this->parse('https://facebook.com/thehall')->json('error'));
    }

    public function test_a_redirect_to_the_inside_stops_at_the_redirect(): void
    {
        Http::fake(['93.184.216.34/calendar.ics' => Http::response('', 302, ['Location' => 'http://10.0.0.5/calendar.ics'])]);

        $this->parse(self::FEED)->assertStatus(422)->assertJsonPath('reason', 'unreachable');

        Http::assertSentCount(1);
    }

    public function test_what_is_not_a_page_or_a_calendar_is_refused(): void
    {
        Http::fake([
            '93.184.216.34/poster.pdf' => Http::response('%PDF-1.7 ...', 200, ['Content-Type' => 'application/pdf']),
            '93.184.216.34/huge' => Http::response(str_repeat('x', 3 * 1024 * 1024 + 1), 200, ['Content-Type' => 'text/html']),
            '93.184.216.34/gone' => Http::response('Not found', 404),
            '93.184.216.34/nothing.ics' => Http::response($this->feed("UID:old\nSUMMARY:Last year\nDTSTART:20251021T000000Z"), 200, ['Content-Type' => 'text/calendar']),
        ]);

        $this->parse('https://93.184.216.34/poster.pdf')->assertStatus(422)->assertJsonPath('reason', 'unsupported');
        $this->parse('https://93.184.216.34/huge')->assertStatus(422)->assertJsonPath('reason', 'too_large');
        $gone = $this->parse('https://93.184.216.34/gone')->assertStatus(422)->assertJsonPath('reason', 'http_error');
        $this->assertStringContainsString('404', $gone->json('error'));
        // A feed that holds only the past.
        $this->parse('https://93.184.216.34/nothing.ics')->assertStatus(422)->assertJsonPath('reason', 'no_events');
    }

    public function test_a_body_stopped_at_the_size_cap_says_it_is_too_large(): void
    {
        // The fetch guard stops a transfer at its cap: curl error 63 for a stated length over
        // it, 23 for a body that ran past it. Both reached the person as a general failure.
        $stopped = fn (int $errno) => fn ($request) => throw new \GuzzleHttp\Exception\RequestException(
            "cURL error {$errno}", $request->toPsrRequest(), null, null, ['errno' => $errno]
        );

        foreach ([CURLE_WRITE_ERROR, CURLE_FILESIZE_EXCEEDED] as $errno) {
            Http::fake(['93.184.216.34/endless' => $stopped($errno)]);
            $this->parse('https://93.184.216.34/endless')->assertStatus(422)
                ->assertJsonPath('reason', 'too_large')
                ->assertJsonPath('error', __('messages.link_import_too_large'));
        }
    }

    public function test_an_international_name_is_fetched_by_its_ascii_form(): void
    {
        // The guard vets the name that is looked up, letter for letter, and refuses one with
        // letters outside ASCII. So such a link is turned into the form a browser would send.
        $normalise = fn (string $link) => (fn () => $this->normalise($link))->call(app(LinkImportService::class));

        $this->assertSame('https://xn--mnchen-3ya.example/whats-on?tag=jazz#top', $normalise('https://münchen.example/whats-on?tag=jazz#top'));
        $this->assertSame('https://xn--mnchen-3ya.example:8443/feed.ics', $normalise('webcal://münchen.example:8443/feed.ics'));
        // What comes after the name is not the name: left exactly as written.
        $this->assertSame('https://93.184.216.34/café/événements', $normalise('https://93.184.216.34/café/événements'));
        $this->assertSame(self::FEED, $normalise(self::FEED));

        // By the rules browsers use now. Under the older ones "ß" is "ss", and straße.de is
        // strasse.de: a different address, which may be somebody else's.
        $this->assertSame('https://xn--strae-oqa.de/kalender.ics', $normalise('https://straße.de/kalender.ics'));
        // An underscore in a name is unusual and real, and is kept.
        $this->assertSame('https://my_site.xn--mnchen-3ya.example/x', $normalise('https://my_site.münchen.example/x'));
        // Only the name: a user name in front of it is not part of it.
        $this->assertSame('https://user:geheim@xn--mnchen-3ya.example/feed.ics', $normalise('https://user:geheim@münchen.example/feed.ics'));

        // A full-width "@" or "/" becomes the real one when converted, which would make another
        // address of it. Refused, as a browser refuses it.
        foreach (["https://good.example\u{FF20}other.example/feed.ics", "https://good.example\u{FF0F}path.example/feed.ics"] as $link) {
            try {
                $normalise($link);
                $this->fail("Accepted: {$link}");
            } catch (\App\Exceptions\LinkImportException $e) {
                $this->assertSame('invalid_url', $e->reason());
            }
        }
    }

    public function test_a_google_calendar_share_link_is_read_from_the_calendars_public_feed(): void
    {
        $service = new class extends LinkImportService
        {
            public function feedFor(string $url): array
            {
                return $this->knownFeedFor($url);
            }
        };
        $feed = 'https://calendar.google.com/calendar/ical/thehall%40group.calendar.google.com/public/basic.ics';

        $this->assertSame([$feed, 'google'], $service->feedFor('https://calendar.google.com/calendar/embed?src=thehall%40group.calendar.google.com&ctz=America%2FNew_York'));
        $this->assertSame([$feed, 'google'], $service->feedFor('https://calendar.google.com/calendar/u/0?cid='.rtrim(base64_encode('thehall@group.calendar.google.com'), '=')));
        // Already the feed, or nothing that names a calendar: left alone.
        $this->assertSame([$feed, null], $service->feedFor($feed));
        $this->assertSame(['https://calendar.google.com/calendar/u/0/r', null], $service->feedFor('https://calendar.google.com/calendar/u/0/r'));
        // A published Outlook calendar's page has its feed beside it.
        $this->assertSame(
            ['https://outlook.office365.com/owa/calendar/abc/def/calendar.ics', 'outlook'],
            $service->feedFor('https://outlook.office365.com/owa/calendar/abc/def/calendar.html')
        );
    }

    public function test_a_private_google_calendar_says_so(): void
    {
        // The rewrite, pointed at an address the test can serve.
        $this->app->bind(LinkImportService::class, fn () => new class extends LinkImportService
        {
            protected function knownFeedFor(string $url): array
            {
                return ['https://93.184.216.34/private.ics', 'google'];
            }
        });
        Http::fake(['93.184.216.34/private.ics' => Http::response('Not found', 404)]);

        $this->parse('https://93.184.216.34/embed?src=x')->assertStatus(422)->assertJsonPath('reason', 'google_private');
    }

    public function test_only_an_editor_of_the_schedule_gets_a_link_read(): void
    {
        Http::fake();
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);

        $this->actingAs($follower)->postJson(
            route('event.parse', ['subdomain' => $this->role->subdomain]), ['source_url' => self::FEED]
        )->assertStatus(403);

        Http::assertNothingSent();
    }

    public function test_the_guest_submission_form_never_fetches(): void
    {
        Http::fake();
        $role = $this->createRole($this->owner, 'curator', ['accept_requests' => true]);
        auth()->logout();

        // The field by name, and a bare link as the text: neither is fetched.
        $this->postJson(route('event.guest_parse', ['subdomain' => $role->subdomain]), [
            'source_url' => self::FEED,
            'event_details' => self::FEED,
        ])->assertOk()->assertExactJson([]);

        Http::assertNothingSent();
    }

    public function test_the_demo_account_cannot_have_the_server_fetch(): void
    {
        Http::fake();
        $demo = User::factory()->create(['email' => DemoService::DEMO_EMAIL, 'email_verified_at' => now()]);
        $role = $this->createRole($demo, 'curator');

        $this->actingAs($demo)->postJson(route('event.parse', ['subdomain' => $role->subdomain]), ['source_url' => self::FEED])
            ->assertStatus(422)->assertJsonPath('reason', 'demo');

        Http::assertNothingSent();
    }

    public function test_links_are_rate_limited_per_person(): void
    {
        $this->serveFeed($this->feed("UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z"));

        foreach (range(1, 10) as $try) {
            $this->parse(self::FEED)->assertOk();
        }

        $this->parse(self::FEED)->assertStatus(429)->assertJsonPath('reason', 'too_many');

        $this->travel(61)->seconds();
        $this->parse(self::FEED)->assertOk();
    }

    public function test_a_long_feed_is_capped_and_says_how_many_there_are(): void
    {
        $events = [];
        foreach (range(1, 150) as $n) {
            $events[] = "UID:e{$n}\nSUMMARY:Event {$n}\nDTSTART:".Carbon::parse('2026-10-12 23:00', 'UTC')->addDays($n)->format('Ymd\THis\Z');
        }
        $this->serveFeed($this->feed(...$events));

        $response = $this->parse(self::FEED)->assertOk();

        $this->assertCount(LinkImportService::MAX_EVENTS, $response->json('parsed'));
        $this->assertSame(150, $response->json('meta.found'));
        $this->assertSame(100, $response->json('meta.shown'));
        // The soonest hundred.
        $this->assertSame('Event 1', $response->json('parsed.0.event_name'));
        $this->assertSame('Event 100', $response->json('parsed.99.event_name'));
    }

    public function test_what_the_schedule_already_has_is_left_out_and_counted(): void
    {
        $this->serveFeed($this->feed(
            "UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z",
            "UID:b\nSUMMARY:Quiz\nDTSTART:20261022T000000Z",
        ));
        $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'open mic', 'starts_at' => '2026-10-21 00:00:00']);

        $response = $this->parse(self::FEED)->assertOk();

        $this->assertSame(['Quiz'], array_column($response->json('parsed'), 'event_name'));
        $this->assertSame(2, $response->json('meta.found'));
        $this->assertSame(1, $response->json('meta.shown'));
        $this->assertSame(1, $response->json('meta.already_on_schedule'));

        // With everything already there, the answer is an empty preview, not an error.
        $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Quiz', 'starts_at' => '2026-10-22 00:00:00']);
        $this->parse(self::FEED)->assertOk()->assertJsonPath('parsed', [])->assertJsonPath('meta.already_on_schedule', 2);
    }

    public function test_a_previewed_row_saves_as_the_import_it_came_from(): void
    {
        $this->serveFeed($this->feed("UID:a\nSUMMARY:Open Mic\nDTSTART:20261021T000000Z\nDTEND:20261021T020000Z"));
        $token = $this->parse(self::FEED)->assertOk()->json('meta.import_token');

        $save = fn (array $extra, ?Role $role = null) => $this->postJson(
            route('event.import', ['subdomain' => ($role ?? $this->role)->subdomain]),
            ['name' => 'Open Mic', 'starts_at' => '2026-10-20 20:00:00', 'duration' => 2, 'schedule_type' => 'one_time'] + $extra
        )->assertOk();
        $source = fn () => Event::query()->latest('id')->firstOrFail()->import_source;

        $save(['import_token' => $token]);
        $this->assertSame('ics', $source());

        // No token, a made-up one, or one minted for another schedule: a row off the import page.
        $save([]);
        $this->assertSame('ai', $source());
        $save(['import_token' => 'made-up']);
        $this->assertSame('ai', $source());
        $other = $this->createRole($this->owner, 'curator');
        $save(['import_token' => $token], $other);
        $this->assertSame('ai', $source());

        // And a day later it has expired.
        $this->travel(25)->hours();
        $save(['import_token' => $token]);
        $this->assertSame('ai', $source());
    }

    public function test_a_weekly_entry_saves_as_one_repeating_event(): void
    {
        $this->serveFeed($this->feed(
            "UID:yoga\nSUMMARY:Yoga Flow\nDTSTART;TZID=America/New_York:20260907T180000\nDTEND;TZID=America/New_York:20260907T190000\n"
            ."RRULE:FREQ=WEEKLY;BYDAY=MO,WE\nEXDATE;TZID=America/New_York:20261019T180000",
        ));
        $response = $this->parse(self::FEED)->assertOk();
        $row = $response->json('parsed.0');

        $this->assertCount(1, $response->json('parsed'));
        $this->assertSame('weekly', $row['recurrence']['frequency']);

        // What the page sends for the row: the preview's fields, and the repeat as given.
        $this->postJson(route('event.import', ['subdomain' => $this->role->subdomain]), [
            'name' => $row['event_name'],
            'starts_at' => $row['event_date_time'].':00',
            'duration' => $row['event_duration'],
            'import_token' => $response->json('meta.import_token'),
        ] + $row['recurrence']['fields'])->assertOk();

        $event = Event::query()->latest('id')->firstOrFail();
        $this->assertSame('weekly', $event->recurring_frequency);
        $this->assertSame('0101000', $event->days_of_week);
        $this->assertSame('ics', $event->import_source);
        // 18:00 in New York, and it repeats: a Wednesday in November is on, the Monday that was
        // called off is not, and a Tuesday never was.
        $this->assertSame('2026-09-07 22:00:00', Carbon::parse($event->starts_at)->format('Y-m-d H:i:s'));
        $this->assertTrue($event->matchesDate('2026-11-04', 'America/New_York'));
        $this->assertFalse($event->matchesDate('2026-10-19', 'America/New_York'));
        $this->assertTrue($event->matchesDate('2026-10-26', 'America/New_York'));
        $this->assertFalse($event->matchesDate('2026-11-03', 'America/New_York'));
    }

    public function test_a_rows_picture_is_fetched_for_the_preview(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        Http::fake([
            '93.184.216.34/calendar.ics' => Http::response($this->feed(
                "UID:a\nSUMMARY:With a poster\nDTSTART:20261021T000000Z\nATTACH;FMTTYPE=image/png:https://93.184.216.34/poster.png",
                "UID:b\nSUMMARY:With a broken one\nDTSTART:20261022T000000Z\nATTACH;FMTTYPE=image/png:https://93.184.216.34/not-an-image.png",
            ), 200, ['Content-Type' => 'text/calendar']),
            '93.184.216.34/poster.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
            '93.184.216.34/not-an-image.png' => Http::response('<html>nope</html>', 200),
        ]);

        $response = $this->parse(self::FEED)->assertOk();
        $path = $response->json('parsed.0.social_image');

        try {
            // Where the preview shows it from and the save attaches it from.
            $this->assertMatchesRegularExpression('#/app/temp/event_[a-z0-9]{32}\.png$#', $path);
            $this->assertFileExists($path);
            $this->assertArrayNotHasKey('social_image', $response->json('parsed.1'));
            $this->assertArrayNotHasKey('image_url', $response->json('parsed.0'));
        } finally {
            @unlink((string) $path);
        }
    }

    private function saveRow(array $row, array $meta): Event
    {
        // What the page sends: the preview's fields, and the repeat as the row gives it.
        $this->postJson(route('event.import', ['subdomain' => $this->role->subdomain]), ($row['recurrence']['fields'] ?? ['schedule_type' => 'one_time']) + [
            'name' => $row['event_name'],
            'starts_at' => $row['event_date_time'].':00',
            'duration' => $row['event_duration'] ?: 2,
            'import_token' => $meta['import_token'],
        ])->assertOk();

        return Event::query()->latest('id')->firstOrFail();
    }

    public function test_reading_a_link_again_shows_the_dates_that_are_left_of_a_listed_series(): void
    {
        // The last Friday of the month is a rule the app cannot repeat, so it comes as dates.
        $this->serveFeed($this->feed(
            "UID:jam\nSUMMARY:Last Friday Jam\nDTSTART;TZID=America/New_York:20260130T200000\nDTEND;TZID=America/New_York:20260130T220000\nRRULE:FREQ=MONTHLY;BYDAY=-1FR",
        ));
        $first = $this->parse(self::FEED)->assertOk();
        $this->assertSame(range(1, 12), array_column(array_column($first->json('parsed'), 'series'), 'position'));
        $this->assertSame('2026-10-30 20:00', $first->json('parsed.0.event_date_time'));

        // The first date is added, and the link is read again a little later.
        $this->saveRow($first->json('parsed.0'), $first->json('meta'));
        $second = $this->parse(self::FEED)->assertOk();

        // The preview shows a series under its first row. With the first date gone, the rest
        // used to keep positions 2 to 12: no row showed them, and they were added all the same.
        $this->assertSame('2026-11-27 20:00', $second->json('parsed.0.event_date_time'));
        $this->assertSame(range(1, 11), array_column(array_column($second->json('parsed'), 'series'), 'position'));
        $this->assertSame(array_fill(0, 11, 11), array_column(array_column($second->json('parsed'), 'series'), 'count'));
    }

    public function test_a_series_that_comes_back_date_by_date_is_the_repeating_event_already_there(): void
    {
        $weekly = "UID:class\nSUMMARY:Monday Class\nDTSTART;TZID=America/New_York:20260907T180000\nDTEND;TZID=America/New_York:20260907T190000\nRRULE:FREQ=WEEKLY;BYDAY=MO";

        // Two addresses, faked together: a second Http::fake() for the same address is not
        // consulted, the first one still answers.
        Http::fake([
            '93.184.216.34/calendar.ics' => Http::response($this->feed($weekly), 200, ['Content-Type' => 'text/calendar']),
            // Somebody moved one Monday to a Tuesday. The source now lists the series by date.
            '93.184.216.34/later.ics' => Http::response($this->feed(
                $weekly,
                "UID:class\nSUMMARY:Monday Class\nRECURRENCE-ID;TZID=America/New_York:20261019T180000\nDTSTART;TZID=America/New_York:20261020T183000\nDTEND;TZID=America/New_York:20261020T193000",
            ), 200, ['Content-Type' => 'text/calendar']),
        ]);

        $first = $this->parse(self::FEED)->assertOk();
        $this->assertSame('weekly', $first->json('parsed.0.recurrence.frequency'));
        $saved = $this->saveRow($first->json('parsed.0'), $first->json('meta'));
        $this->assertSame('0100000', $saved->days_of_week);

        $second = $this->parse('https://93.184.216.34/later.ics')->assertOk();

        // Every date the repeating event already covers is left out. Only the moved one is new.
        // (It used to offer all twelve, ticked: twelve single events on top of the repeating one.)
        $this->assertSame(['2026-10-20 18:30'], array_column($second->json('parsed'), 'event_date_time'));
        $this->assertNull($second->json('parsed.0.series'));
    }

    public function test_a_series_saved_date_by_date_is_not_offered_again_as_a_repeating_event(): void
    {
        // The reverse: its next date is already a single event of that name on the schedule.
        $this->createEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'name' => 'Monday Class',
            'starts_at' => Carbon::parse('2026-10-12 18:00', 'America/New_York')->utc()->format('Y-m-d H:i:s'),
        ]);
        $this->serveFeed($this->feed(
            "UID:class\nSUMMARY:Monday Class\nDTSTART;TZID=America/New_York:20260907T180000\nRRULE:FREQ=WEEKLY;BYDAY=MO",
            "UID:other\nSUMMARY:Something else\nDTSTART;TZID=America/New_York:20261015T180000",
        ));

        $response = $this->parse(self::FEED)->assertOk();

        $this->assertSame(['Something else'], array_column($response->json('parsed'), 'event_name'));
        $this->assertSame(1, $response->json('meta.already_on_schedule'));
    }
}
