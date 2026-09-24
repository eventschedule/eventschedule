<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\EventTextGenerator;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event's external registration link (events.registration_url) opens from the event page's
 * button, from a calendar card under direct registration, and from the event's address with a
 * trailing slash. Nothing validated it on the web form, the public guest import, the AI import,
 * WhatsApp or the curator scraper, so a stored javascript: value ran on a visitor's click, and
 * the trailing-slash redirect ran before the password gate, handing a password event's link to
 * anybody who added the slash.
 *
 * The saving hook now stores what Event::registrationHref() reads - an http(s) link, a scheme-less
 * one with https:// in front, or null - and every guest surface reads registrationHref(), which
 * covers a row written around the hook.
 */
class RegistrationLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const LINK = 'https://tickets.example.org/e/42?utm_source=flyer';

    private Role $venue;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
        $this->travelTo(Carbon::parse('2026-09-24 09:00:00', 'UTC'));

        $this->venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Blue Room', 'direct_registration' => true]);
    }

    private function externalEvent(array $attrs = []): Event
    {
        return $this->createEvent($this->venue, array_merge([
            'name' => 'Jazz Night',
            'creator_role_id' => $this->venue->id,
            'registration_url' => self::LINK,
            'ticket_price' => 15,
            'tickets_enabled' => false,
        ], $attrs));
    }

    /** A row as a restore or an older release left it: written around the saving hook. */
    private function legacyValue(Event $event, string $value): Event
    {
        DB::table('events')->where('id', $event->id)->update(['registration_url' => $value]);

        return $event->fresh();
    }

    private function assertNoScriptHref(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/href\s*=\s*["\']\s*javascript:/i', $html);
    }

    /** The event's address with a trailing slash, as a flyer prints it for direct registration. */
    private function slashUrl(Event $event): string
    {
        // The query keeps the slash: the test client trims a trailing one off the whole URL. And
        // selfhost, as CI runs: a hosted install sends the app host's slash to the clean URL first.
        config(['app.hosted' => false]);

        return $this->guestEventUrl($this->venue, $event).'/?ref=flyer';
    }

    public function test_the_saving_hook_stores_a_link_a_browser_can_open(): void
    {
        $this->assertSame(self::LINK, $this->externalEvent()->registration_url, 'a web link is kept byte for byte');
        $this->assertSame('https://tickets.example.org/e/44', $this->externalEvent(['registration_url' => 'tickets.example.org/e/44'])->registration_url);
        $this->assertSame('https://tickets.example.org/e/1', $this->externalEvent(['registration_url' => "  https://tickets.example.org/e/1\n"])->registration_url);
        $this->assertNull($this->externalEvent(['registration_url' => 'javascript:alert(document.cookie)'])->registration_url);
        $this->assertNull($this->externalEvent(['registration_url' => 'Tickets at the door'])->registration_url);

        $event = $this->externalEvent();
        $event->registration_url = ' JavaScript:alert(1)';
        $event->save();
        $this->assertNull($event->fresh()->registration_url, 'an edit goes through the hook as well');
    }

    /** Guarded on dirty, like the clamp beside it: an unrelated save never rewrites a stored value. */
    public function test_an_untouched_legacy_value_survives_an_unrelated_save(): void
    {
        $event = $this->legacyValue($this->externalEvent(), 'Call the box office');

        $event->name = 'Jazz Night, second set';
        $event->save();

        $this->assertSame('Call the box office', $event->fresh()->registration_url);
    }

    /** The public guest-add form: an anonymous submitter's value goes through the hook too. */
    public function test_guest_import_stores_a_javascript_link_as_null(): void
    {
        $curator = $this->createCurator($this->createOwner(), ['accept_requests' => true, 'require_account' => false]);

        foreach (['Scripted Show' => 'javascript:alert(document.cookie)', 'Bare Show' => 'tickets.example.org/e/9'] as $name => $link) {
            $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => $name,
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration' => 2,
                'registration_url' => $link,
            ])->assertOk();
        }

        $this->assertNull(Event::where('name', 'Scripted Show')->firstOrFail()->registration_url);
        $this->assertSame('https://tickets.example.org/e/9', Event::where('name', 'Bare Show')->firstOrFail()->registration_url);
    }

    /**
     * A row written around the hook: no href, no price badge and no offer on the event page, and a
     * null field in the calendar JSON, whose window.open() would have run it.
     */
    public function test_a_legacy_javascript_link_is_never_offered(): void
    {
        $event = $this->legacyValue($this->externalEvent(), 'javascript:alert(document.cookie)');
        $this->assertNull($event->registrationHref());

        $html = $this->get($this->guestEventUrl($this->venue, $event))->assertOk()->getContent();
        $this->assertNoScriptHref($html);
        $this->assertStringNotContainsString('id="gp-event-price"', $html, 'no price badge without a link to buy at');
        $this->assertStringNotContainsString(__('messages.view_event'), $html, 'and no registration button');
        $this->assertStringContainsString('id="calendar-mobile-sheet"', $html, 'the add-to-calendar sheet stands in for it on mobile');

        $payload = $this->getJson(route('role.calendar_events', [
            'subdomain' => $this->venue->subdomain,
            'month' => 10,
            'year' => 2026,
        ]))->assertOk()->json();
        $row = collect($payload['events'])->firstWhere('id', UrlUtils::encodeId($event->id));
        $this->assertNotNull($row, 'the event is in the month payload');
        $this->assertNull($row['registration_url']);

        // ?graphic=1 builds its payload in the calendar view instead of the trait.
        $graphic = $this->get('/'.$this->venue->subdomain.'?graphic=1&month=10&year=2026')->assertOk()->getContent();
        $this->assertStringNotContainsString('javascript:alert', $graphic);
    }

    /** The control: a web link is still the button, the badge and the JSON field. */
    public function test_a_web_link_is_still_offered_everywhere(): void
    {
        $event = $this->externalEvent();

        $html = $this->get($this->guestEventUrl($this->venue, $event))->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'href="'.e(self::LINK).'"'), 'the desktop and the mobile button');
        $this->assertStringContainsString(__('messages.view_event'), $html);
        $this->assertStringContainsString('id="gp-event-price"', $html);
        $this->assertStringNotContainsString('id="calendar-mobile-sheet"', $html, 'the button is the mobile call to action');

        $payload = $this->getJson(route('role.calendar_events', [
            'subdomain' => $this->venue->subdomain,
            'month' => 10,
            'year' => 2026,
        ]))->assertOk()->json();
        $this->assertSame(self::LINK, collect($payload['events'])->firstWhere('id', UrlUtils::encodeId($event->id))['registration_url']);
    }

    /** A legacy link without a scheme is offered with https:// in front, not as a relative path. */
    public function test_a_legacy_scheme_less_link_is_offered_as_https(): void
    {
        $event = $this->legacyValue($this->externalEvent(), 'tickets.example.org/e/8');

        $html = $this->get($this->guestEventUrl($this->venue, $event))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'href="https://tickets.example.org/e/8"'), 'the desktop and the mobile button');
        $this->assertStringNotContainsString('href="tickets.example.org', $html);
    }

    public function test_a_trailing_slash_redirects_only_to_a_web_link(): void
    {
        $this->get($this->slashUrl($this->externalEvent()))->assertRedirect(self::LINK);

        // A legacy value with no scheme: to its https:// form, not to a path on this host.
        $bare = $this->legacyValue($this->externalEvent(), 'tickets.example.org/e/8');
        $this->get($this->slashUrl($bare))->assertRedirect('https://tickets.example.org/e/8');

        // No web link: the page itself.
        $scripted = $this->legacyValue($this->externalEvent(), 'javascript:alert(document.cookie)');
        $response = $this->get($this->slashUrl($scripted))->assertOk();
        $this->assertNoScriptHref($response->getContent());
    }

    /**
     * The redirect runs before the password gate, so it has to ask the same question: a visitor
     * who would see the password prompt gets the prompt, not the link.
     */
    public function test_a_password_event_redirects_only_past_its_password(): void
    {
        $event = $this->externalEvent(['is_private' => true, 'event_password' => 'letmein']);
        $url = $this->slashUrl($event);

        $prompt = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('name="password"', $prompt);
        $this->assertStringNotContainsString('tickets.example.org', $prompt);

        $this->withSession(['event_password_'.$event->id => true])->get($url)->assertRedirect(self::LINK);

        $this->flushSession();
        $this->actingAs($this->venue->user)->get($url)->assertRedirect(self::LINK);
    }

    /**
     * Graphics and the event text print the address with a trailing slash under direct
     * registration. Only when it will redirect: for a link that is no web page it would only fall
     * through to the event page.
     */
    public function test_the_event_text_adds_the_slash_only_for_a_web_link(): void
    {
        $settings = ['url_include_https' => true, 'url_include_id' => true];

        $linked = EventTextGenerator::parseTemplate('{url}', $this->externalEvent(), $this->venue, true, $settings);
        $this->assertStringEndsWith('/', $linked);

        $scripted = $this->legacyValue($this->externalEvent(), 'javascript:alert(document.cookie)');
        $unlinked = EventTextGenerator::parseTemplate('{url}', $scripted, $this->venue, true, $settings);
        $this->assertStringEndsNotWith('/', $unlinked);
        $this->assertSame($scripted->getGuestUrl($this->venue->subdomain, null, true, true), $unlinked);
    }
}
