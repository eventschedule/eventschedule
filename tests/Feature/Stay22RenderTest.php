<?php

namespace Tests\Feature;

use App\Services\Stay22Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What actually reaches a guest event page.
 *
 * The widget is client-rendered from a JSON props blob, so these assert on the host element
 * and the decoded payload rather than on visible strings. Note that json_encode is called
 * with JSON_HEX_AMP, so the embed URL's separators arrive as & - assertSee('&lat=') would
 * fail misleadingly. Parse the payload instead.
 */
class Stay22RenderTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'stay22.enabled' => true,
            'stay22.aid' => 'operator-aid',
            // See Stay22ServiceTest: leaving the geocoder live would overwrite the fixture
            // coordinates locally and behave differently in CI, which has no key.
            'services.google.backend' => null,
        ]);
    }

    public function test_the_map_renders_when_every_gate_passes(): void
    {
        [$role, $event] = $this->venueWithEvent();

        $payload = $this->payloadFor($role, $event);

        $this->assertNotNull($payload, 'The accommodation map should render.');
        $this->assertStringStartsWith('https://www.stay22.com/embed/gm?aid=', $payload['url']);
        $this->assertNotSame('', $payload['disclosure']);
        $this->assertNotSame('', $payload['frameTitle']);
    }

    /**
     * The click-to-load contract. Stay22 must not be contacted on page load, so the URL may
     * live in the props blob (which Vue only uses once the visitor consents) but no iframe or
     * script may reference the host in the served HTML.
     */
    public function test_no_iframe_or_script_targets_stay22_in_the_served_html(): void
    {
        [$role, $event] = $this->venueWithEvent();

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // The props blob is expected to carry the URL; everything else must be clean. An
        // iframe src, a preconnect hint or a script tag would all defeat the consent gate.
        // Our own identifiers (#es-stay22-host, .es-stay22-frame) contain the vendor name and
        // are fine - what must be absent is any reference to the vendor's HOST.
        $this->assertNotNull($this->payloadFromHtml($html), 'Sanity: the payload should be present.');

        $outside = strtolower($this->stripStay22Json($html));

        // Only the vendor host matters. The page legitimately carries a Google Maps iframe a
        // few elements earlier, so asserting on '<iframe' would be testing the wrong thing.
        $this->assertStringNotContainsString('stay22.com', $outside);
    }

    public function test_absent_when_the_operator_switch_is_off(): void
    {
        config(['stay22.enabled' => false]);
        [$role, $event] = $this->venueWithEvent();

        $this->assertNull($this->payloadFor($role, $event));
    }

    public function test_absent_when_the_schedule_has_not_opted_in(): void
    {
        [$role, $event] = $this->venueWithEvent(['stay22_enabled' => false]);

        $this->assertNull($this->payloadFor($role, $event));
    }

    public function test_absent_when_no_affiliate_id_resolves_anywhere(): void
    {
        // Guards against emitting a broken embed on a half-configured install.
        config(['stay22.aid' => null]);
        [$role, $event] = $this->venueWithEvent(['stay22_aid' => null]);

        $this->assertNull($this->payloadFor($role, $event));
    }

    public function test_absent_when_the_venue_has_no_coordinates(): void
    {
        [$role, $event] = $this->venueWithEvent(['geo_lat' => null, 'geo_lon' => null]);

        $this->assertNull($this->payloadFor($role, $event));
    }

    public function test_absent_for_a_past_event(): void
    {
        [$role, $event] = $this->venueWithEvent([], [
            'starts_at' => now()->subDays(30)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        $this->assertNull(
            $this->payloadFor($role, $event),
            'Offering hotels for an event that already happened is dead weight.'
        );
    }

    public function test_absent_once_last_nights_event_is_over(): void
    {
        // Checkout is this morning, so there is no night left to book.
        [$role, $event] = $this->venueWithEvent([], [
            'starts_at' => now()->subDay()->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 3,
        ]);

        $this->assertNull($this->payloadFor($role, $event));
    }

    public function test_present_for_an_event_happening_today(): void
    {
        [$role, $event] = $this->venueWithEvent([], [
            'starts_at' => now()->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 3,
        ]);

        $payload = $this->payloadFor($role, $event);

        $this->assertNotNull($payload, 'Tonight\'s event still needs a room for tonight.');
        parse_str(parse_url($payload['url'], PHP_URL_QUERY), $params);
        $this->assertSame(now()->format('Y-m-d'), $params['checkin']);
    }

    /**
     * The regression this test exists for: gating on check-in alone hid the map on day three of
     * a week-long festival, which is exactly the case the feature is most useful for.
     */
    public function test_an_in_progress_multi_day_event_still_offers_tonight(): void
    {
        [$role, $event] = $this->venueWithEvent([], [
            // Started 2 days ago, runs 7 days total.
            'starts_at' => now()->subDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'duration' => 24 * 7,
        ]);

        $payload = $this->payloadFor($role, $event);

        $this->assertNotNull($payload, 'A festival already under way must still show accommodation.');

        parse_str(parse_url($payload['url'], PHP_URL_QUERY), $params);

        $this->assertSame(
            now()->format('Y-m-d'),
            $params['checkin'],
            'Check-in must be clamped forward to today, not the day the festival opened.'
        );
        $this->assertTrue($params['checkout'] > now()->format('Y-m-d'));
    }

    public function test_the_language_of_the_schedule_is_not_forced_on_the_visitor(): void
    {
        // supportedlang is a list of languages the widget MAY offer, so a single value locks it.
        [$role, $event] = $this->venueWithEvent(['language_code' => 'he']);

        parse_str(parse_url($this->payloadFor($role, $event)['url'], PHP_URL_QUERY), $params);

        $this->assertArrayNotHasKey('supportedlang', $params);
    }

    public function test_absent_in_embeds_and_generated_graphics(): void
    {
        [$role, $event] = $this->venueWithEvent();
        $url = $this->guestEventUrl($role, $event);

        $this->assertNull($this->payloadFromHtml($this->get($url.'?embed=true')->getContent()));
        $this->assertNull($this->payloadFromHtml($this->get($url.'?graphic=1')->getContent()));
    }

    public function test_absent_on_the_demo_schedule(): void
    {
        // The demo schedule is a sales surface, matching Role::showAds(). Identified by its
        // subdomain constant rather than a config value.
        config(['app.hosted' => true]);

        [$role, $event] = $this->venueWithEvent([
            'subdomain' => \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN,
        ]);

        $this->assertNull($this->payloadFor($role, $event));
    }

    /**
     * The explicit inverse of the ads tier expectations. Unlike AdSense this is NOT
     * free-tier-only, so pin it before someone "fixes" it into matching Role::showAds().
     */
    public function test_present_for_a_paid_schedule(): void
    {
        [$role, $event] = $this->venueWithEvent([
            'plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);

        $this->assertNotNull($this->payloadFor($role, $event));
    }

    public function test_present_for_a_free_schedule_too(): void
    {
        [$role, $event] = $this->venueWithEvent([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertNotNull($this->payloadFor($role, $event));
    }

    public function test_a_recurring_occurrence_uses_the_date_from_the_url(): void
    {
        $owner = $this->createOwner();
        $role = $this->geocodedVenue($owner);
        $event = $this->createRecurringEvent($role, [
            'starts_at' => now()->addDays(2)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'creator_role_id' => $role->id,
        ]);

        $date = now()->addDays(30)->format('Y-m-d');

        $payload = $this->payloadFromHtml(
            $this->get($this->guestEventUrl($role, $event, $date))->getContent()
        );

        $this->assertNotNull($payload);
        parse_str(parse_url($payload['url'], PHP_URL_QUERY), $params);
        $this->assertSame($date, $params['checkin']);
    }

    // ---------------------------------------------------------------- custom domains

    public function test_the_operator_fallback_is_suppressed_on_a_custom_domain(): void
    {
        // A customer paying for a white-label domain should not have commission taken from it.
        [$role, $event] = $this->venueWithEvent(['stay22_aid' => null]);

        $this->assertNull($this->embedOnCustomDomain($role, $event));
    }

    public function test_a_schedules_own_affiliate_id_still_works_on_a_custom_domain(): void
    {
        [$role, $event] = $this->venueWithEvent(['stay22_aid' => 'owner-aid']);

        $url = $this->embedOnCustomDomain($role, $event);

        $this->assertNotNull($url);
        $this->assertStringContainsString('aid=owner-aid', $url);
    }

    // ---------------------------------------------------------------- helpers

    private function embedOnCustomDomain($role, $event): ?string
    {
        $request = \Illuminate\Http\Request::create('https://tickets.example.org/', 'GET');
        $request->attributes->set('custom_domain_host', 'tickets.example.org');

        return Stay22Service::embedFor($role->fresh(), $event->fresh(), null, $request);
    }

    /** @return array{0: \App\Models\Role, 1: \App\Models\Event} */
    private function venueWithEvent(array $roleAttrs = [], array $eventAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->geocodedVenue($owner, $roleAttrs);
        $event = $this->createEvent($role, array_merge(['creator_role_id' => $role->id], $eventAttrs));

        return [$role->fresh(), $event->fresh()];
    }

    /**
     * The host schedule must itself be the venue: Event::getVenueAttribute() returns the first
     * attached venue-type role, and createEvent() attaches the host first.
     */
    private function geocodedVenue($owner, array $attrs = [])
    {
        return $this->createVenueWithAddress($owner, array_merge([
            'timezone' => 'UTC',
            'stay22_enabled' => true,
            'geo_lat' => '39.7817213',
            'geo_lon' => '-89.6501481',
            'formatted_address' => '123 Main St, Springfield, IL 62701, USA',
        ], $attrs));
    }

    private function payloadFor($role, $event): ?array
    {
        return $this->payloadFromHtml(
            $this->get($this->guestEventUrl($role, $event))->getContent()
        );
    }

    private function payloadFromHtml(string $html): ?array
    {
        if (! preg_match('/id="es-stay22-json"[^>]*>(.*?)<\/script>/s', $html, $m)) {
            return null;
        }

        return json_decode($m[1], true);
    }

    /** The served HTML with the props blob removed, so the rest can be checked for leaks. */
    private function stripStay22Json(string $html): string
    {
        return preg_replace('/<script type="application\/json" id="es-stay22-json".*?<\/script>/s', '', $html);
    }
}
