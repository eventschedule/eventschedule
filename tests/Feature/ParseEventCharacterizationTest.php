<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\UsageDaily;
use App\Models\User;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What GeminiUtils::parseEvent() does with the model's answer, pinned as it stands.
 *
 * Everything after the provider call (defaults, category and custom-field matching, the
 * same-time merge, clamps, the registration link, venue and talent matching, the date window,
 * duplicate hints) is shared by the AI import page, guest submissions, the WhatsApp webhook and
 * the curator import cron, and none of it had a test: the provider is called over raw curl. The
 * fakeResponses() hook is what makes it reachable.
 *
 * These are characterizations, not endorsements. Two of them pin behaviour that reads as a
 * mistake - the upper date bound that never fires, and the similar-event check that converts
 * with the owner's timezone - so that moving this code cannot change either by accident.
 */
class ParseEventCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Every prompt sent to the provider, in order. */
    private array $prompts = [];

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Noon in New York and still the same date in UTC, so "today" cannot straddle midnight.
        $this->travelTo(Carbon::parse('2026-10-10 16:00:00', 'UTC'));
        Http::preventStrayRequests();

        $this->owner = $this->createOwner();
        $this->actingAs($this->owner);
    }

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);

        parent::tearDown();
    }

    /** The provider answers with these rows (null: it failed), and the prompt is recorded. */
    private function answer(?array $rows): void
    {
        GeminiUtils::fakeResponses(function ($prompt) use ($rows) {
            $this->prompts[] = $prompt;

            return $rows;
        });
    }

    /** A row as the model returns it: flat, every value a string. */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'event_name' => 'Jazz Night',
            'event_date_time' => '2026-10-20 20:00',
            'event_address' => '',
        ], $overrides);
    }

    private function parse(Role $role, array $rows, string $details = 'Jazz Night, Oct 20'): array
    {
        $this->answer($rows);

        return GeminiUtils::parseEvent($role, $details);
    }

    /** A venue nobody owns, the kind an import leaves behind. */
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

    public function test_the_prompt_lists_the_fields_the_categories_and_the_date_hint(): void
    {
        $role = $this->createRole($this->owner, 'curator', [
            'event_custom_fields' => ['f1' => ['name' => 'Stage', 'type' => 'dropdown', 'options' => 'Main, Garden']],
        ]);

        $this->parse($role, [$this->row()], 'Doors at eight.');

        $prompt = $this->prompts[0];
        $this->assertStringStartsWith('Act as a precise data extraction API.', $prompt);
        // No image, so the ":source" slot is empty.
        $this->assertStringContainsString('from this  message', $prompt);
        $this->assertStringContainsString("event_date_time (YYYY-MM-DD HH:MM),\n", $prompt);
        $this->assertStringContainsString("event_city,\n", $prompt);
        $this->assertStringContainsString('Choose the most appropriate category from: Art & Culture, Business Networking, Community, Concerts', $prompt);
        $this->assertStringContainsString("custom_field_f1 (Stage (Choose from: Main, Garden)),\n", $prompt);
        $this->assertStringContainsString('Doors at eight.', $prompt);
        $this->assertStringContainsString('The date today is Oct 10, 2026.', $prompt);
        $this->assertStringContainsString('The event date is either Oct 2026 or Nov 2026.', $prompt);
        $this->assertStringContainsString('default to 20:00 (8pm)', $prompt);
        $this->assertStringContainsString('separate them into multiple events', $prompt);
        // English schedule: no language-preservation paragraph.
        $this->assertStringNotContainsString('IMPORTANT: The input is in', $prompt);
    }

    public function test_a_non_english_schedule_is_told_to_keep_its_language(): void
    {
        $role = $this->createRole($this->owner, 'curator', ['language_code' => 'he']);

        $this->parse($role, [$this->row()]);

        $this->assertStringContainsString('IMPORTANT: The input is in Hebrew.', $this->prompts[0]);
    }

    public function test_missing_fields_default_and_shouting_is_recased(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        [$event] = $this->parse($role, [$this->row([
            'event_name' => '**JAZZ NIGHT**',
            'event_state' => 'CA',
            'event_city' => 'New Orleans',
        ])]);

        // Markdown emphasis stripped, then ALL CAPS becomes Title Case - for every field, so a
        // two-letter state is recased too.
        $this->assertSame('Jazz Night', $event['event_name']);
        $this->assertSame('Ca', $event['event_state']);
        $this->assertSame('New Orleans', $event['event_city']);
        // Fields the model left out exist and are empty.
        $this->assertSame('', $event['event_details']);
        $this->assertSame('', $event['venue_name']);
        $this->assertSame('', $event['registration_url']);
        $this->assertSame('', $event['category_name']);
        $this->assertArrayNotHasKey('category_id', $event);
        $this->assertSame([], $event['performers']);
    }

    public function test_a_category_is_matched_exactly_then_partially_then_fuzzily(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        $events = $this->parse($role, [
            $this->row(['event_date_time' => '2026-10-20 18:00', 'category_name' => 'concerts']),
            $this->row(['event_date_time' => '2026-10-20 19:00', 'category_name' => 'Food']),
            $this->row(['event_date_time' => '2026-10-20 20:00', 'category_name' => 'Sprituality']),
            $this->row(['event_date_time' => '2026-10-20 21:00', 'category_name' => 'Zzzzzz']),
        ]);

        $this->assertSame([4, 6, 11, null], array_column($events, 'category_id'));
        $this->assertArrayNotHasKey('category_name', $events[0]);
    }

    public function test_price_currency_and_performer_are_normalised(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        $events = $this->parse($role, [
            $this->row([
                'event_date_time' => '2026-10-20 18:00',
                'ticket_price' => '$1,250.50 per table',
                'ticket_currency' => 'usd',
                'performer_name' => 'The Band',
                'performer_email' => 'band@gmail.com',
            ]),
            $this->row([
                'event_date_time' => '2026-10-20 19:00',
                'ticket_price' => 'free',
                'ticket_currency' => 'dollars',
            ]),
        ]);

        $this->assertSame(1250.5, $events[0]['ticket_price']);
        $this->assertSame('USD', $events[0]['ticket_currency_code']);
        $this->assertArrayNotHasKey('ticket_currency', $events[0]);
        $this->assertSame([['name' => 'The Band', 'name_en' => '', 'email' => 'band@gmail.com', 'website' => '']], $events[0]['performers']);
        $this->assertArrayNotHasKey('performer_name', $events[0]);

        // No digits: the price goes. Not a three-letter code: no currency.
        $this->assertArrayNotHasKey('ticket_price', $events[1]);
        $this->assertArrayNotHasKey('ticket_currency_code', $events[1]);
        $this->assertArrayNotHasKey('ticket_currency', $events[1]);
    }

    public function test_custom_field_answers_are_matched_to_their_options(): void
    {
        $role = $this->createRole($this->owner, 'curator', [
            'event_custom_fields' => [
                'f1' => ['name' => 'Stage', 'type' => 'dropdown', 'options' => 'Main, Garden'],
                'f2' => ['name' => 'Indoor', 'type' => 'switch'],
                'f3' => ['name' => 'Tags', 'type' => 'multiselect', 'options' => 'Rock, Jazz, Folk'],
                'f4' => ['name' => 'Host', 'type' => 'string'],
            ],
        ]);

        [$event] = $this->parse($role, [$this->row([
            'custom_field_f1' => 'garden',
            'custom_field_f2' => 'Yes',
            'custom_field_f3' => 'jazz, rok, nothing-like-it',
            'custom_field_f4' => '',
        ])]);

        $this->assertSame(['f1' => 'Garden', 'f2' => '1', 'f3' => 'Jazz, Rock'], $event['custom_field_values']);
        $this->assertArrayNotHasKey('custom_field_f1', $event);
        $this->assertArrayNotHasKey('custom_field_f4', $event);
    }

    public function test_rows_at_the_same_time_and_address_become_one_event(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        $events = $this->parse($role, [
            $this->row(['event_name' => 'Double Bill', 'event_address' => '5 Elm St', 'performer_name' => 'First Act']),
            $this->row(['event_name' => 'Ignored Name', 'event_address' => '5 Elm St', 'performer_name' => 'Second Act']),
            $this->row(['event_name' => 'Ignored Too', 'event_address' => '5 Elm St', 'performer_name' => 'First Act']),
            $this->row(['event_name' => 'Elsewhere', 'event_address' => '9 Oak St', 'performer_name' => 'Third Act']),
        ]);

        $this->assertCount(2, $events);
        $this->assertSame('Double Bill', $events[0]['event_name']);
        $this->assertSame(['First Act', 'Second Act'], array_column($events[0]['performers'], 'name'));
        $this->assertSame('Elsewhere', $events[1]['event_name']);
    }

    public function test_values_longer_than_their_column_are_clamped(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        [$event] = $this->parse($role, [$this->row([
            'event_name' => str_repeat('a', 400),
            'event_details' => str_repeat('b', 400),
            'performer_name' => str_repeat('c', 400),
        ])]);

        $this->assertSame(255, mb_strlen($event['event_name']));
        $this->assertSame(255, mb_strlen($event['performers'][0]['name']));
        // A TEXT column: no ceiling.
        $this->assertSame(400, mb_strlen($event['event_details']));
    }

    public function test_a_registration_link_is_followed_to_where_it_lands(): void
    {
        // IP literals: the fetch guard resolves a hostname first, and that DNS lookup is real.
        Http::fake([
            '93.184.216.34/tickets' => Http::response('', 302, ['Location' => 'https://93.184.216.35/final']),
            '93.184.216.35/final' => Http::response('<html><head><title>Tickets</title></head></html>', 200),
        ]);
        $role = $this->createRole($this->owner, 'curator');

        [$event] = $this->parse($role, [$this->row(['registration_url' => 'https://93.184.216.34/tickets'])]);

        $this->assertSame('https://93.184.216.35/final', $event['registration_url']);
        // No og:image on the page, and no flyer was uploaded.
        $this->assertNull($event['social_image']);
        Http::assertSentCount(2);
    }

    public function test_a_venue_schedule_is_its_own_venue(): void
    {
        $role = $this->createRole($this->owner, 'venue', ['address1' => '1 Main St']);

        [$event] = $this->parse($role, [$this->row(['venue_name' => 'Somewhere Else', 'event_address' => '77 Other Rd'])]);

        $this->assertSame(UrlUtils::encodeId($role->id), $event['venue_id']);
        $this->assertSame('1 Main St', $event['event_address']);
        $this->assertArrayNotHasKey('matched_venue_name', $event);
    }

    public function test_a_venue_is_matched_by_ownership_then_by_city_then_by_connection(): void
    {
        $role = $this->createRole($this->owner, 'curator', ['country_code' => 'us']);
        $owned = $this->createRole($this->owner, 'venue', ['name' => 'The Blue Room']);
        $inCity = $this->placeholderVenue(['name' => 'Red Hall', 'city' => 'Austin', 'country_code' => 'us']);
        $connected = $this->placeholderVenue(['name' => 'Green Barn']);
        $this->createEvent($role)->roles()->attach($connected->id, ['is_accepted' => true]);

        $events = $this->parse($role, [
            // The article and the case are normalised away; ownership needs no city.
            $this->row(['event_date_time' => '2026-10-20 18:00', 'venue_name' => 'blue room']),
            $this->row(['event_date_time' => '2026-10-20 19:00', 'venue_name' => 'Red Hall', 'event_city' => 'Austin', 'event_country_code' => 'USA']),
            $this->row(['event_date_time' => '2026-10-20 20:00', 'venue_name' => 'Green Barn']),
            $this->row(['event_date_time' => '2026-10-20 21:00', 'venue_name' => 'Nowhere Known']),
        ]);

        $this->assertSame(UrlUtils::encodeId($owned->id), $events[0]['venue_id']);
        $this->assertSame('The Blue Room', $events[0]['matched_venue_name']);
        $this->assertSame($owned->subdomain, $events[0]['venue_subdomain']);
        $this->assertTrue($events[0]['venue_is_editable']);
        $this->assertFalse($events[0]['venue_is_claimable']);

        $this->assertSame(UrlUtils::encodeId($inCity->id), $events[1]['venue_id']);
        $this->assertTrue($events[1]['venue_is_claimable']);

        $this->assertSame(UrlUtils::encodeId($connected->id), $events[2]['venue_id']);

        $this->assertArrayNotHasKey('venue_id', $events[3]);
    }

    public function test_a_talent_schedule_is_its_own_talent_and_a_curator_matches_performers(): void
    {
        $talentRole = $this->createRole($this->owner, 'talent');
        [$own] = $this->parse($talentRole, [$this->row(['performer_name' => 'Someone Else'])]);
        $this->assertSame(UrlUtils::encodeId($talentRole->id), $own['talent_id']);
        $this->assertArrayNotHasKey('talent_id', $own['performers'][0]);

        $curator = $this->createRole($this->owner, 'curator', ['country_code' => 'us']);
        $band = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Band', 'country_code' => 'us']);
        $abroad = $this->createRole($this->createOwner(), 'talent', ['name' => 'Far Away', 'country_code' => 'fr']);

        $events = $this->parse($curator, [
            $this->row(['event_date_time' => '2026-10-20 18:00', 'performer_name' => 'The Band']),
            // Same name, another country, and no shared event: not matched.
            $this->row(['event_date_time' => '2026-10-20 19:00', 'performer_name' => 'Far Away']),
        ]);

        $this->assertSame(UrlUtils::encodeId($band->id), $events[0]['performers'][0]['talent_id']);
        $this->assertArrayNotHasKey('talent_id', $events[1]['performers'][0]);
        $this->assertNotNull($abroad->id);
    }

    public function test_country_and_address_default_from_the_schedule(): void
    {
        $role = $this->createRole($this->owner, 'curator', ['country_code' => 'il', 'address1' => '3 Herzl St']);

        $events = $this->parse($role, [
            $this->row(['event_date_time' => '2026-10-20 18:00']),
            // Alpha-3 comes back as the alpha-2 the pickers use.
            $this->row(['event_date_time' => '2026-10-20 19:00', 'event_country_code' => 'ISR', 'event_address' => '8 Dizengoff']),
        ]);

        $this->assertSame('il', $events[0]['event_country_code']);
        $this->assertSame('3 Herzl St', $events[0]['event_address']);
        $this->assertSame('il', $events[1]['event_country_code']);
        $this->assertSame('8 Dizengoff', $events[1]['event_address']);
    }

    public function test_the_date_window_drops_the_past_and_nothing_else(): void
    {
        $role = $this->createRole($this->owner, 'curator');

        $events = $this->parse($role, [
            $this->row(['event_date_time' => '2026-10-06 20:00']),  // four days ago
            $this->row(['event_date_time' => '2026-10-08 20:00']),  // two days ago
            $this->row(['event_date_time' => '2027-04-10 20:00']),  // six months out
            $this->row(['event_date_time' => 'sometime soon']),
        ]);

        $this->assertNull($events[0]['event_date_time']);
        $this->assertSame('2026-10-08 20:00', $events[1]['event_date_time']);
        // The "more than two months away" half of the check never fires for a future date: the
        // difference is signed, so it is negative. Pinned, not endorsed.
        $this->assertSame('2027-04-10 20:00', $events[2]['event_date_time']);
        $this->assertNull($events[3]['event_date_time']);
    }

    public function test_an_event_already_on_the_schedule_is_pointed_out(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<html></html>', 200)]);
        $role = $this->createRole($this->owner, 'curator');
        $venue = $this->placeholderVenue(['name' => 'Corner Hall', 'address1' => '5 Elm St']);
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Band']);

        // The owner is in New York, so 20:00 on the page is midnight UTC.
        $byLink = $this->createEvent($role, ['creator_role_id' => $role->id, 'registration_url' => 'https://93.184.216.34/show', 'starts_at' => '2026-10-25 00:00:00']);
        $byAddress = $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-10-21 00:00:00']);
        $byAddress->roles()->attach($venue->id, ['is_accepted' => true]);
        $byPerformer = $this->createEvent($role, ['creator_role_id' => $role->id, 'starts_at' => '2026-10-22 00:00:00']);
        $byPerformer->roles()->attach($talent->id, ['is_accepted' => true]);

        $events = $this->parse($role, [
            $this->row(['event_date_time' => '2026-10-28 20:00', 'registration_url' => 'https://93.184.216.34/show']),
            $this->row(['event_date_time' => '2026-10-20 20:00', 'event_address' => '5 Elm St']),
            $this->row(['event_date_time' => '2026-10-21 20:00', 'performer_name' => 'The Band']),
            $this->row(['event_date_time' => '2026-10-23 20:00', 'event_address' => '5 Elm St']),
        ]);

        // Same link, whatever the date.
        $this->assertSame(UrlUtils::encodeId($byLink->id), $events[0]['event_id']);
        $this->assertTrue($events[0]['is_curated']);
        // Same start and the same street address.
        $this->assertSame(UrlUtils::encodeId($byAddress->id), $events[1]['event_id']);
        $this->assertSame($byAddress->getGuestUrl(), $events[1]['event_url']);
        // Same start and a performer of that name.
        $this->assertSame(UrlUtils::encodeId($byPerformer->id), $events[2]['event_id']);
        // Same address on another night: nothing.
        $this->assertArrayNotHasKey('event_id', $events[3]);
    }

    public function test_one_usage_row_per_answer_and_none_when_the_provider_fails(): void
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->owner, 'curator');
        $count = fn () => (int) UsageDaily::where('operation', UsageTrackingService::GEMINI_PARSE_EVENT)
            ->where('role_id', $role->id)->sum('count');

        $this->assertSame([], $this->parse($role, []) ?: []);
        $this->answer(null);
        $this->assertSame([], GeminiUtils::parseEvent($role, 'nothing'));
        $this->assertSame(0, $count());

        // Two events in one answer are one request.
        $this->parse($role, [
            $this->row(['event_date_time' => '2026-10-20 18:00']),
            $this->row(['event_date_time' => '2026-10-20 19:00']),
        ]);
        $this->assertSame(1, $count());
    }
}
