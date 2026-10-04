<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * GeminiUtils::enrichParsedEvents() for rows that did NOT come from the model: a calendar feed,
 * a page's own event data, a connected calendar.
 *
 * Those rows get the same matching, clamps and already-on-the-schedule hints as a parsed flyer,
 * and none of the clean-up that exists because of how a model answers. Each test here states one
 * thing a feed row must be spared, next to the same rows going through as the model's so the
 * difference is the option and nothing else. ParseEventCharacterizationTest holds the other side.
 */
class EnrichParsedEventsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-10 16:00:00', 'UTC'));
        // Any request at all fails the test: a feed row is never worth a page fetch.
        Http::preventStrayRequests();

        $this->owner = $this->createOwner();
        $this->actingAs($this->owner);
    }

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);

        parent::tearDown();
    }

    private function row(array $overrides = []): array
    {
        return array_merge([
            'event_name' => 'Open Mic',
            'event_date_time' => '2026-10-20 20:00',
        ], $overrides);
    }

    private function feed(Role $role, array $rows, array $options = []): array
    {
        return GeminiUtils::enrichParsedEvents($role, $rows, ['source' => 'ics'] + $options);
    }

    private function placeholderVenue(array $attrs): Role
    {
        $venue = new Role;
        $venue->type = 'venue';
        $venue->subdomain = 'venue'.strtolower(Str::random(10));
        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }
        $venue->save();

        return $venue->fresh();
    }

    public function test_two_feed_entries_at_the_same_time_stay_two_events(): void
    {
        $role = $this->createRole($this->owner, 'curator');
        $rows = [
            $this->row(['event_name' => 'Yoga, Studio A']),
            $this->row(['event_name' => 'Pilates, Studio B']),
        ];

        $fromFeed = $this->feed($role, $rows);
        $this->assertSame(['Yoga, Studio A', 'Pilates, Studio B'], array_column($fromFeed, 'event_name'));
        $this->assertSame([], $fromFeed[0]['performers']);

        // The same rows as the model's are one event: it splits a bill into a row per performer.
        $this->assertCount(1, GeminiUtils::enrichParsedEvents($role, $rows));
    }

    public function test_feed_text_is_kept_as_written(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        [$event] = $this->feed($role, [$this->row([
            'event_name' => 'DJ SET',
            'event_state' => 'CA',
            'event_details' => '**Doors at 7**',
        ])]);

        $this->assertSame('DJ SET', $event['event_name']);
        $this->assertSame('CA', $event['event_state']);
        $this->assertSame('**Doors at 7**', $event['event_details']);
        // Missing fields are still filled in.
        $this->assertSame('', $event['venue_name']);
    }

    public function test_a_feed_event_that_already_started_keeps_its_date(): void
    {
        $role = $this->createRole($this->owner, 'curator');
        $festival = $this->row(['event_date_time' => '2026-10-05 10:00', 'event_duration' => 168]);

        [$fromFeed] = $this->feed($role, [$festival]);
        $this->assertSame('2026-10-05 10:00', $fromFeed['event_date_time']);

        // From the model, a date more than three days back is blanked.
        [$fromModel] = GeminiUtils::enrichParsedEvents($role, [$festival]);
        $this->assertNull($fromModel['event_date_time']);
    }

    public function test_a_feed_link_is_kept_without_fetching_it(): void
    {
        Http::fake();
        $role = $this->createRole($this->owner, 'curator');

        [$event] = $this->feed($role, [$this->row(['registration_url' => 'https://93.184.216.34/tickets?utm_source=feed'])]);

        $this->assertSame('https://93.184.216.34/tickets?utm_source=feed', $event['registration_url']);
        $this->assertArrayNotHasKey('social_image', $event);
        Http::assertNothingSent();
    }

    public function test_a_feed_row_is_still_clamped_and_matched(): void
    {
        $role = $this->createRole($this->owner, 'curator', ['country_code' => 'us']);
        $venue = $this->createRole($this->owner, 'venue', ['name' => 'The Blue Room']);

        [$event] = $this->feed($role, [$this->row([
            'event_name' => str_repeat('a', 400),
            'venue_name' => 'Blue Room',
            'category_name' => 'Concerts',
            'ticket_price' => '12.50',
            'ticket_currency' => 'usd',
        ])]);

        $this->assertSame(255, mb_strlen($event['event_name']));
        $this->assertSame(UrlUtils::encodeId($venue->id), $event['venue_id']);
        $this->assertSame(4, $event['category_id']);
        $this->assertSame(12.5, $event['ticket_price']);
        $this->assertSame('USD', $event['ticket_currency_code']);
        $this->assertSame('us', $event['event_country_code']);
    }

    public function test_a_shared_link_only_marks_the_date_already_on_the_schedule(): void
    {
        $role = $this->createRole($this->owner, 'curator');
        // 20:00 in New York on the 20th.
        $existing = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'registration_url' => 'https://93.184.216.34/series',
            'starts_at' => '2026-10-21 00:00:00',
        ]);

        $events = $this->feed($role, [
            $this->row(['event_name' => 'Week 1', 'event_date_time' => '2026-10-20 20:00', 'registration_url' => 'https://93.184.216.34/series']),
            $this->row(['event_name' => 'Week 2', 'event_date_time' => '2026-10-27 20:00', 'registration_url' => 'https://93.184.216.34/series']),
        ], ['timezone' => 'America/New_York']);

        $this->assertSame(UrlUtils::encodeId($existing->id), $events[0]['event_id']);
        // Same link, another night: a new event, not a duplicate of the first.
        $this->assertArrayNotHasKey('event_id', $events[1]);
    }

    public function test_hints_compare_in_the_zone_the_rows_are_in(): void
    {
        // The owner is in New York; the schedule, and so the feed's times, are in Los Angeles.
        $role = $this->createRole($this->owner, 'curator', ['timezone' => 'America/Los_Angeles']);
        $venue = $this->placeholderVenue(['name' => 'Corner Hall', 'address1' => '5 Elm St']);
        // 20:00 in Los Angeles on the 20th.
        $existing = $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-10-21 03:00:00']);
        $existing->roles()->attach($venue->id, ['is_accepted' => true]);
        $row = $this->row(['event_address' => '5 Elm St']);

        [$inScheduleZone] = $this->feed($role, [$row], ['timezone' => 'America/Los_Angeles']);
        $this->assertSame(UrlUtils::encodeId($existing->id), $inScheduleZone['event_id']);

        // Without the option it converts with the owner's zone, as it always has, and misses.
        [$inOwnerZone] = $this->feed($role, [$row]);
        $this->assertArrayNotHasKey('event_id', $inOwnerZone);
    }

    public function test_parse_event_can_be_given_another_footer(): void
    {
        $role = $this->createRole($this->owner, 'curator');
        $prompts = [];
        GeminiUtils::fakeResponses(function ($prompt) use (&$prompts) {
            $prompts[] = $prompt;

            return [['event_name' => 'Tour Stop', 'event_date_time' => '2027-03-01 20:00']];
        });

        GeminiUtils::parseEvent($role, 'tour dates');
        GeminiUtils::parseEvent($role, 'page text', null, ['prompt_footer' => 'footer_page']);
        GeminiUtils::parseEvent($role, 'tour dates', null, ['prompt_footer' => 'no_such_footer']);

        $this->assertStringContainsString('The event date is either Oct 2026 or Nov 2026.', $prompts[0]);
        // A page lists a season, so it is not told the dates are this month or next.
        $this->assertStringNotContainsString('The event date is either', $prompts[1]);
        $this->assertStringContainsString('This is the text of a web page.', $prompts[1]);
        $this->assertStringContainsString('use the nearest date on or after today that fits', $prompts[1]);
        $this->assertStringContainsString('The date today is Oct 10, 2026.', $prompts[1]);
        // An unknown key falls back rather than sending a prompt with no ending.
        $this->assertStringContainsString('The event date is either Oct 2026 or Nov 2026.', $prompts[2]);
    }

    public function test_a_single_event_page_becomes_its_own_link(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<html></html>', 200)]);
        $role = $this->createRole($this->owner, 'curator');
        $options = ['default_registration_url' => 'https://93.184.216.34/show'];

        GeminiUtils::fakeResponses(fn () => [['event_name' => 'One Show', 'event_date_time' => '2026-10-20 20:00']]);
        [$single] = GeminiUtils::parseEvent($role, 'page text', null, $options);
        $this->assertSame('https://93.184.216.34/show', $single['registration_url']);

        // A listing page is not any one event's link.
        GeminiUtils::fakeResponses(fn () => [
            ['event_name' => 'First', 'event_date_time' => '2026-10-20 20:00'],
            ['event_name' => 'Second', 'event_date_time' => '2026-10-21 20:00'],
        ]);
        $listing = GeminiUtils::parseEvent($role, 'page text', null, $options);
        $this->assertSame(['', ''], array_column($listing, 'registration_url'));

        // A link the event names itself wins.
        GeminiUtils::fakeResponses(fn () => [['event_name' => 'One Show', 'event_date_time' => '2026-10-20 20:00', 'registration_url' => 'https://93.184.216.34/own']]);
        [$own] = GeminiUtils::parseEvent($role, 'page text', null, $options);
        $this->assertSame('https://93.184.216.34/own', $own['registration_url']);
    }

    public function test_a_page_with_no_events_is_an_empty_answer(): void
    {
        $role = $this->createRole($this->owner, 'curator');
        // What "[]" from the model arrives as: one empty row.
        GeminiUtils::fakeResponses(fn () => [[]]);

        $this->assertSame([], GeminiUtils::parseEvent($role, 'page text', null, ['drop_empty_rows' => true]));
        // Without the option the caller gets a blank event to fill in, as before.
        $this->assertCount(1, GeminiUtils::parseEvent($role, 'page text'));
    }

    public function test_a_ticket_link_is_opened_once_however_many_rows_share_it_and_only_so_many(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<html><head><title>Tickets</title></head></html>', 200)]);
        $role = $this->createRole($this->owner, 'curator', ['timezone' => 'America/New_York']);

        // Three of the model's rows, one link: a festival page with one "Tickets" address.
        $rows = [];
        foreach (['One', 'Two', 'Three'] as $index => $name) {
            $rows[] = $this->row(['event_name' => $name, 'event_date_time' => '2026-10-2'.$index.' 20:00', 'registration_url' => 'https://93.184.216.34/tickets']);
        }
        $out = GeminiUtils::enrichParsedEvents($role, $rows);

        $this->assertSame(array_fill(0, 3, 'https://93.184.216.34/tickets'), array_column($out, 'registration_url'));
        Http::assertSentCount(1);

        // Twenty-five rows, each with its own link: the person is waiting on every fetch, so the
        // first ten are opened and the rest keep their link as it was written.
        $rows = [];
        for ($n = 0; $n < 25; $n++) {
            $rows[] = $this->row(['event_name' => 'Show '.$n, 'event_date_time' => '2026-11-'.str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT).' 20:00', 'registration_url' => 'https://93.184.216.34/show/'.$n]);
        }
        $out = GeminiUtils::enrichParsedEvents($role, $rows);

        $this->assertCount(25, $out);
        $this->assertSame('https://93.184.216.34/show/24', $out[24]['registration_url']);
        Http::assertSentCount(1 + 10);
    }
}
