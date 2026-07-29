<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\Stay22Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Affiliate-ID resolution, stay-date derivation and the embed URL contract.
 *
 * A Feature test rather than a Unit one: stayDates() calls Event::scheduleTimezone(), which
 * loads the creatorRole relation, and the aid resolver reads the settings table. tests/Unit
 * gets no migrated database.
 */
class Stay22ServiceTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setting::get memoizes the whole site_settings map with rememberForever.
        Cache::flush();

        config([
            'stay22.enabled' => true,
            'stay22.aid' => null,
            // Role::saving() geocodes through the Google API whenever this key is set and the
            // address changed, which would overwrite the fixture coordinates with whatever
            // Google returns - and make these tests pass locally (where a key exists) while
            // behaving differently in CI, where it does not. Pinned off so the coordinates
            // under test are the ones written, and no real HTTP request is made.
            'services.google.backend' => null,
        ]);
    }

    /** Nights between the two derived dates. Carbon's diffInDays returns a float. */
    private function nights(array $dates): int
    {
        return (int) \Carbon\Carbon::parse($dates['checkin'])
            ->diffInDays(\Carbon\Carbon::parse($dates['checkout']));
    }

    private function request(array $query = [], array $attributes = []): Request
    {
        $request = Request::create('https://sub.eventschedule.test/', 'GET', $query);

        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }

        return $request;
    }

    // ---------------------------------------------------------------- aid resolution

    public function test_no_aid_resolves_when_the_master_switch_is_off(): void
    {
        config(['stay22.enabled' => false, 'stay22.aid' => 'operator-aid']);
        $role = $this->createRole($this->createOwner(), 'venue', ['stay22_aid' => 'owner-aid']);

        // The owner's own ID is still readable, but the operator fallback is not, and the
        // component never renders anyway - isEnabled() is the first gate in embedFor().
        $this->assertNull(Stay22Service::operatorAid());
        $this->assertFalse(Stay22Service::isEnabled());
    }

    public function test_the_owners_own_aid_beats_the_operator_fallback(): void
    {
        config(['stay22.aid' => 'operator-aid']);
        $role = $this->createRole($this->createOwner(), 'venue', ['stay22_aid' => 'owner-aid']);

        $this->assertSame('owner-aid', Stay22Service::resolveAid($role));
        $this->assertTrue(Stay22Service::hasOwnAid($role));
    }

    public function test_the_operator_fallback_is_used_when_the_owner_has_none(): void
    {
        config(['stay22.aid' => 'operator-aid']);
        $role = $this->createRole($this->createOwner());

        $this->assertSame('operator-aid', Stay22Service::resolveAid($role));
        $this->assertFalse(Stay22Service::hasOwnAid($role));
    }

    public function test_the_settings_table_beats_the_config_default(): void
    {
        config(['stay22.aid' => 'from-env']);
        Setting::set('stay22_aid', 'from-settings');
        Cache::flush();

        $this->assertSame('from-settings', Stay22Service::operatorAid());
    }

    public function test_a_whitespace_only_owner_aid_falls_back_to_the_operator(): void
    {
        config(['stay22.aid' => 'operator-aid']);
        $role = $this->createRole($this->createOwner(), 'venue', ['stay22_aid' => '   ']);

        $this->assertFalse(Stay22Service::hasOwnAid($role));
        $this->assertSame('operator-aid', Stay22Service::resolveAid($role));
    }

    /**
     * The highest-value case in this file. The aid is interpolated as the FIRST query
     * parameter, so a value carrying '&' or '#' could append arbitrary Stay22 parameters or
     * truncate the query. Rejecting beats encoding: a silently wrong map is worse than none.
     */
    public function test_an_aid_containing_url_metacharacters_is_rejected_not_encoded(): void
    {
        config(['stay22.aid' => 'operator-aid']);

        foreach (['abc&lat=0', 'abc#x', 'abc?y=1', 'abc/../x', 'a b'] as $malicious) {
            $role = $this->createRole($this->createOwner(), 'venue', ['stay22_aid' => $malicious]);

            $this->assertFalse(
                Stay22Service::hasOwnAid($role),
                "[{$malicious}] must not be accepted as an affiliate ID."
            );
            $this->assertSame('operator-aid', Stay22Service::resolveAid($role));
        }
    }

    public function test_a_malicious_operator_aid_yields_no_embed_at_all(): void
    {
        Setting::set('stay22_aid', 'evil&lat=0');
        Cache::flush();

        $this->assertNull(Stay22Service::operatorAid());

        $owner = $this->createOwner();
        $role = $this->createGeocodedVenue($owner, ['stay22_enabled' => true]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->assertNull(Stay22Service::embedFor($role->fresh(), $event->fresh(), null, $this->request()));
    }

    // ---------------------------------------------------------------- stay dates

    public function test_a_single_evening_event_is_one_night(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(10)->setTime(23, 0)->format('Y-m-d H:i:s'),
            'duration' => 3,
            'creator_role_id' => $role->id,
        ]);

        $dates = Stay22Service::stayDates($event->fresh());

        // You sleep the night OF the gig, so checkout is the next morning - never the same day.
        $this->assertSame(
            1,
            $this->nights($dates)
        );
    }

    public function test_a_null_duration_is_one_night_rather_than_a_zero_night_stay(): void
    {
        // getEndDateTime() would silently default to +2 hours here, landing checkout on the
        // same calendar day. That is why stayDates() does its own arithmetic.
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['duration' => null, 'creator_role_id' => $role->id]);

        $dates = Stay22Service::stayDates($event->fresh());

        $this->assertNotSame($dates['checkin'], $dates['checkout']);
        $this->assertSame(
            1,
            $this->nights($dates)
        );
    }

    public function test_a_multi_day_event_covers_every_night_of_its_run(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        // Friday 18:00 + 54h runs to Sunday midnight: two calendar boundaries, three nights,
        // so a traveller checks out on Monday.
        $event = $this->createEvent($role, [
            'starts_at' => '2026-09-04 18:00:00',
            'duration' => 54,
            'creator_role_id' => $role->id,
        ]);

        $dates = Stay22Service::stayDates($event->fresh());

        $this->assertSame('2026-09-04', $dates['checkin']);
        $this->assertSame('2026-09-07', $dates['checkout']);
    }

    public function test_an_absurd_duration_is_clamped(): void
    {
        config(['stay22.max_nights' => 30]);
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'starts_at' => '2026-09-04 18:00:00',
            'duration' => 100000,
            'creator_role_id' => $role->id,
        ]);

        $dates = Stay22Service::stayDates($event->fresh());

        $this->assertSame(
            30,
            $this->nights($dates)
        );
    }

    /**
     * The check-in date is a property of where the event happens, not of who is looking.
     * getStartDateTime() resolves against the authenticated viewer's timezone unless one is
     * passed explicitly, so without the override a signed-in owner abroad would be shown a
     * different date from an anonymous visitor at the identical URL.
     */
    public function test_the_checkin_date_is_venue_local_and_ignores_the_viewers_timezone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
        // 01:00 UTC on the 6th is 21:00 on the 5th in New York.
        $event = $this->createEvent($role, [
            'starts_at' => '2026-08-06 01:00:00',
            'duration' => 3,
            'creator_role_id' => $role->id,
        ]);

        $this->assertSame('2026-08-05', Stay22Service::stayDates($event->fresh())['checkin']);

        $viewer = $this->createOwner();
        $viewer->timezone = 'Asia/Tokyo';
        $viewer->save();

        $this->actingAs($viewer);

        $this->assertSame(
            '2026-08-05',
            Stay22Service::stayDates($event->fresh())['checkin'],
            'A signed-in viewer in another timezone must not change the check-in date.'
        );
    }

    public function test_a_recurring_occurrence_uses_the_date_from_the_url(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $event = $this->createRecurringEvent($role, [
            'starts_at' => now()->addDays(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'creator_role_id' => $role->id,
        ]);

        $occurrence = now()->addDays(31)->format('Y-m-d');

        $this->assertSame($occurrence, Stay22Service::stayDates($event->fresh(), $occurrence)['checkin']);
    }

    public function test_no_dates_without_a_start(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->starts_at = null;

        $this->assertNull(Stay22Service::stayDates($event));
    }

    // ---------------------------------------------------------------- URL contract

    public function test_the_affiliate_id_is_the_first_query_parameter(): void
    {
        // Stay22 keys their error detection and reporting off its position, and nothing else
        // in the URL would break visibly if a refactor reordered it.
        $url = $this->embedUrl();

        $this->assertStringStartsWith('https://www.stay22.com/embed/gm?', $url);
        $this->assertStringStartsWith('aid=', parse_url($url, PHP_URL_QUERY));
    }

    public function test_the_url_carries_the_derived_dates_and_coordinates(): void
    {
        parse_str(parse_url($this->embedUrl(), PHP_URL_QUERY), $params);

        $this->assertSame('owner-aid', $params['aid']);
        $this->assertSame('39.781721', $params['lat']);
        $this->assertSame('-89.650148', $params['lng']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $params['checkin']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $params['checkout']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $params['campaign']);
    }

    public function test_a_valid_accent_colour_becomes_maincolor_and_an_invalid_one_is_dropped(): void
    {
        parse_str(parse_url($this->embedUrl(accent: '#4E81FA'), PHP_URL_QUERY), $params);
        $this->assertSame('4E81FA', $params['maincolor']);

        parse_str(parse_url($this->embedUrl(accent: 'rebeccapurple'), PHP_URL_QUERY), $params);
        $this->assertArrayNotHasKey('maincolor', $params);
    }

    /**
     * geo_lat / geo_lon are varchar and frequently unset, so '0' has to stay valid while ''
     * and non-numeric junk must not produce a map centred on nowhere.
     */
    public function test_unusable_coordinates_yield_no_embed_but_zero_is_accepted(): void
    {
        foreach ([null, '', '   ', 'abc', '999', '-181'] as $bad) {
            $this->assertNull(
                $this->embedUrl(venue: ['geo_lat' => $bad, 'geo_lon' => $bad]),
                'Coordinates ['.var_export($bad, true).'] must not produce an embed.'
            );
        }

        $this->assertNotNull($this->embedUrl(venue: ['geo_lat' => '0', 'geo_lon' => '0']));
    }

    // ---------------------------------------------------------------- helpers

    /** Builds a fully-configured venue schedule + event and returns the embed URL (or null). */
    private function embedUrl(?string $accent = null, array $venue = [], array $query = [], array $attributes = [], array $role = []): ?string
    {
        $owner = $this->createOwner();

        $schedule = $this->createGeocodedVenue($owner, array_merge([
            'stay22_enabled' => true,
            'stay22_aid' => 'owner-aid',
        ], $role, $venue));

        $event = $this->createEvent($schedule, ['creator_role_id' => $schedule->id]);

        return Stay22Service::embedFor(
            $schedule->fresh(),
            $event->fresh(),
            null,
            $this->request($query, $attributes),
            $accent
        );
    }

    /**
     * A venue schedule carrying its own coordinates, so it is also the event's venue.
     *
     * Coordinates are set explicitly rather than left to the Role::saving() geocoder, which
     * only runs when BACKEND_GOOGLE_KEY is configured and so never fires in tests.
     *
     * Note the host schedule must BE the venue here: Event::getVenueAttribute() returns the
     * first attached role of type venue, and createEvent() attaches the host schedule first,
     * so a venue-type host would shadow any separately attached venue.
     */
    private function createGeocodedVenue($owner, array $attrs = [])
    {
        return $this->createVenueWithAddress($owner, array_merge([
            'timezone' => 'UTC',
            'geo_lat' => '39.7817213',
            'geo_lon' => '-89.6501481',
            'formatted_address' => '123 Main St, Springfield, IL 62701, USA',
        ], $attrs));
    }
}
