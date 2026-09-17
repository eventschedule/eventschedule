<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest booking request form (issue #124).
 *
 * The form posts over fetch, and before this issue its hidden "create an account" section still
 * carried `required` controls, so the browser refused every submit from a guest who left the box
 * alone - invisible to any test that posts straight to the endpoint. The page tests below pin the
 * markup contract that prevents that (a real-browser journey lives in tests/Browser); the rest pin
 * the owner's booking form options and the account rules on the server.
 */
class BookingRequestFormTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();
    }

    /**
     * A claimed schedule taking booking-form requests from guests. require_account is always set:
     * createRole() leaves it at the column default, which is TRUE.
     */
    private function bookingSchedule(string $type = 'venue', array $attributes = []): Role
    {
        return $this->createRole($this->createOwner(), $type, array_merge([
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
        ], $attributes));
    }

    private function requiring(array $fields, array $extra = []): array
    {
        return array_merge([
            'required_fields' => array_fill_keys($fields, true),
        ], $extra);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
        ], $extra);
    }

    private function complete(array $extra = []): array
    {
        return $this->payload(array_merge([
            'event_name' => 'Late Night Set',
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '20:00',
            'description' => 'An evening set.',
            'venue_name' => 'The Cellar',
        ], $extra));
    }

    private function storeUrl(Role $role): string
    {
        return route('event.booking_request.store', ['subdomain' => $role->subdomain]);
    }

    private function pageUrl(Role $role): string
    {
        return route('event.booking_request', ['subdomain' => $role->subdomain]);
    }

    /**
     * A selfhosted install. Leave is_testing on to test what the page shows; turn it off to reach the
     * server's registration gate, which skips itself under test. That also makes the route throttle
     * live, so those tests post only a couple of times.
     */
    private function selfhost(bool $allowRegistration, bool $gateLive = false): void
    {
        config([
            'app.hosted' => false,
            'app.allow_registration' => $allowRegistration,
        ]);

        if ($gateLive) {
            config(['app.is_testing' => false]);
        }
    }

    private function dom(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return new \DOMXPath($dom);
    }

    private function page(Role $role, ?User $as = null): \DOMXPath
    {
        $request = $as ? $this->actingAs($as) : $this;

        return $this->dom($request->get($this->pageUrl($role))->assertOk()->getContent());
    }

    private function node(\DOMXPath $xpath, string $query): ?\DOMElement
    {
        $nodes = $xpath->query($query);

        return $nodes->length ? $nodes->item(0) : null;
    }

    // -- The #124 contract ---------------------------------------------------------------------

    public static function pageScenarios(): array
    {
        return [
            'talent' => ['talent', [], null],
            'venue with an optional account' => ['venue', [], null],
            'curator with an optional account' => ['curator', [], null],
            'selfhost with registration closed' => ['venue', [], 'closed'],
            'talent requiring every field, online off' => ['talent', ['booking_form_config' => ['required_fields' => array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, true), 'allow_online' => false]], null],
            'venue requiring every field, online off' => ['venue', ['booking_form_config' => ['required_fields' => array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, true), 'allow_online' => false]], null],
            'signed-in visitor' => ['venue', [], 'signed-in'],
        ];
    }

    /**
     * The browser will not submit a form holding a required control it cannot show, and says so only
     * in the console. So no required control may sit inside anything hidden when the page loads, and
     * the two widget-backed inputs (EasyMDE hides the textarea, Flatpickr swaps the date input for a
     * readonly one) may never carry the attribute at all.
     */
    #[DataProvider('pageScenarios')]
    public function test_no_required_control_is_rendered_where_the_visitor_cannot_reach_it(string $type, array $attributes, ?string $variant): void
    {
        $role = $this->bookingSchedule($type, $attributes);

        if ($variant === 'closed') {
            $this->selfhost(false);
        }

        $xpath = $this->page($role, $variant === 'signed-in' ? $this->createOwner() : null);

        $hidden = "contains(concat(' ', normalize-space(@class), ' '), ' hidden ')"
            ." or contains(translate(@style, ' ', ''), 'display:none')"
            .' or @hidden';

        $offenders = [];
        foreach ($xpath->query("//form[@id='booking-request-form']//*[self::input[not(@type='hidden')] or self::select or self::textarea][@required]") as $control) {
            $hiddenAncestor = $xpath->query("ancestor-or-self::*[$hidden]", $control)->length > 0;
            $isWidget = str_contains(' '.$control->getAttribute('class').' ', ' html-editor ')
                || $control->getAttribute('id') === 'event_date';

            if ($hiddenAncestor || $isWidget) {
                $offenders[] = $control->getAttribute('name') ?: $control->getAttribute('id');
            }
        }

        $this->assertSame([], $offenders);
        $this->assertNotNull($this->node($xpath, "//form[@id='booking-request-form'][@novalidate]"), 'The page validates the form itself');
    }

    public function test_a_talent_offers_an_optional_account_although_its_row_says_require_account(): void
    {
        // The settings page has no Require Account toggle for a talent, so the column default sticks.
        $role = $this->createRole($this->createOwner(), 'talent', ['accept_requests' => true]);
        $this->assertTrue((bool) $role->require_account);

        $xpath = $this->page($role);

        $box = $this->node($xpath, "//input[@id='create_account']");
        $this->assertNotNull($box);
        $this->assertFalse($box->hasAttribute('checked'));
        $this->assertFalse($box->hasAttribute('disabled'));
        $this->assertNull($this->node($xpath, "//input[@type='hidden'][@name='create_account']"));

        $section = $this->node($xpath, "//div[@id='account-fields']");
        $this->assertStringContainsString('hidden', $section->getAttribute('class'));
        $this->assertFalse($this->node($xpath, "//input[@id='account_password']")->hasAttribute('required'));
        $this->assertFalse($this->node($xpath, "//input[@id='account_terms']")->hasAttribute('required'));
    }

    public function test_a_selfhost_with_registration_closed_offers_no_account(): void
    {
        $this->selfhost(false);

        $xpath = $this->page($this->bookingSchedule());

        $this->assertNull($this->node($xpath, "//input[@name='create_account']"));
        $this->assertNull($this->node($xpath, "//input[@name='password']"));
        $this->assertNull($this->node($xpath, "//input[@name='terms']"));
        $this->assertTrue($this->node($xpath, "//input[@name='contact_name']")->hasAttribute('required'));
        $this->assertTrue($this->node($xpath, "//input[@name='contact_email']")->hasAttribute('required'));
    }

    public function test_a_selfhost_that_opened_registration_offers_the_account(): void
    {
        $this->selfhost(true);

        $this->assertNotNull($this->node($this->page($this->bookingSchedule()), "//input[@id='create_account']"));
    }

    public function test_a_signed_in_visitor_gets_no_contact_or_account_fields(): void
    {
        $visitor = $this->createOwner();

        $html = $this->actingAs($visitor)->get($this->pageUrl($this->bookingSchedule()))->assertOk()->getContent();
        $xpath = $this->dom($html);

        $this->assertStringContainsString($visitor->email, $html);
        foreach (['contact_name', 'contact_email', 'create_account', 'password', 'terms'] as $name) {
            $this->assertNull($this->node($xpath, "//input[@name='$name']"), "$name should not render");
        }
    }

    public function test_a_guest_is_sent_to_sign_up_when_a_venue_booking_form_requires_an_account(): void
    {
        $role = $this->bookingSchedule('venue', ['require_account' => true]);

        $this->get($this->pageUrl($role).'?lang=es')
            ->assertRedirect(route('role.request', ['subdomain' => $role->subdomain, 'lang' => 'es']));

        // Not labelled as a booking-form request on the way through.
        $this->assertNull(session('pending_request_form'));

        // An array ?lang= is dropped, not forwarded and not a 500.
        $this->get($this->pageUrl($role).'?lang[]=en')
            ->assertRedirect(route('role.request', ['subdomain' => $role->subdomain]));
    }

    public function test_a_signed_in_visitor_is_not_bounced_from_an_account_only_booking_form(): void
    {
        $role = $this->bookingSchedule('curator', ['require_account' => true]);

        $this->actingAs($this->createOwner())->get($this->pageUrl($role))->assertOk();
    }

    public function test_only_the_required_default_fields_are_marked(): void
    {
        $plain = $this->page($this->bookingSchedule('talent'));
        $this->assertSame(0, $plain->query('//*[@aria-required="true"]')->length);
        $this->assertSame(0, $plain->query("//label//span[@aria-hidden='true'][normalize-space()='*']")->length);

        $role = $this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring(['event_name', 'description']),
        ]);
        $xpath = $this->page($role);

        $this->assertSame('true', $this->node($xpath, "//input[@id='event_name']")->getAttribute('aria-required'));
        $this->assertSame('true', $this->node($xpath, "//textarea[@id='event_description']")->getAttribute('aria-required'));
        $this->assertFalse($this->node($xpath, "//input[@id='event_date']")->hasAttribute('aria-required'));
        $this->assertSame(2, $xpath->query("//label//span[@aria-hidden='true'][normalize-space()='*']")->length);

        // Checked by the page script, never by a native required attribute.
        $this->assertFalse($this->node($xpath, "//input[@id='event_name']")->hasAttribute('required'));
        $this->assertFalse($this->node($xpath, "//textarea[@id='event_description']")->hasAttribute('required'));
    }

    public function test_every_field_has_an_error_anchor_even_when_optional(): void
    {
        $xpath = $this->page($this->bookingSchedule('talent'));

        foreach (['event_name', 'date', 'start_time', 'description', 'location', 'event_url', 'contact_name', 'account_name', 'contact_email', 'account_email', 'password', 'terms', 'create_account'] as $key) {
            $anchor = $this->node($xpath, "//*[@data-error-for='$key']");
            $this->assertNotNull($anchor, "missing the $key error anchor");
            $this->assertSame('error-'.$key, $anchor->getAttribute('id'));
        }
    }

    public function test_turning_online_off_removes_the_online_controls(): void
    {
        $xpath = $this->page($this->bookingSchedule('talent', [
            'booking_form_config' => ['allow_online' => false],
        ]));

        $this->assertNull($this->node($xpath, "//*[@id='is_online']"));
        $this->assertNull($this->node($xpath, "//*[@id='online-url-field']"));
        $this->assertNull($this->node($xpath, "//input[@name='event_url']"));
        $this->assertNotNull($this->node($xpath, "//input[@id='in_person'][@type='hidden']"));
        $this->assertNotNull($this->node($xpath, "//input[@name='venue_name']"));
    }

    public function test_a_venue_without_online_shows_only_its_own_address(): void
    {
        $role = $this->bookingSchedule('venue', [
            'name' => 'The Brick Hall',
            'booking_form_config' => ['allow_online' => false],
        ]);

        $xpath = $this->page($role);

        $this->assertNull($this->node($xpath, "//form[@id='booking-request-form']//fieldset"));
        $this->assertNull($this->node($xpath, "//*[@id='error-location']"));
        $this->assertNull($this->node($xpath, "//input[@name='venue_name']"));
        $this->assertNotNull($this->node($xpath, "//div[@id='location-fields']//div[normalize-space()='The Brick Hall']"));
    }

    public function test_request_terms_render_escaped_with_line_breaks(): void
    {
        $this->get($this->pageUrl($this->bookingSchedule()))
            ->assertOk()
            ->assertDontSee(__('messages.request_terms'));

        $role = $this->bookingSchedule('venue', ['request_terms' => "Bring <b>cables</b>\nNo refunds"]);

        $this->get($this->pageUrl($role))
            ->assertOk()
            ->assertSee(__('messages.request_terms'))
            ->assertSee('Bring &lt;b&gt;cables&lt;/b&gt;<br />', false)
            ->assertSee('No refunds')
            ->assertDontSee('<b>cables</b>', false);
    }

    public function test_request_terms_follow_the_translation_toggle(): void
    {
        $role = $this->bookingSchedule('venue', [
            'language_code' => 'es',
            'translation_language_code' => 'en',
            'request_terms' => 'Traiga sus cables',
            'request_terms_en' => 'Bring your own cables',
        ]);

        $this->get($this->pageUrl($role).'?lang=en')
            ->assertOk()
            ->assertSee('Bring your own cables')
            ->assertDontSee('Traiga sus cables');
    }

    // -- Accounts on the server ----------------------------------------------------------------

    public function test_a_talent_request_needs_no_account(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['accept_requests' => true]);

        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();

        $event = Event::where('name', 'Late Night Set')->firstOrFail();
        $this->assertTrue($event->is_guest_submission);
        $this->assertSame(1, User::count());
    }

    public function test_a_booking_form_that_requires_an_account_refuses_an_accountless_post(): void
    {
        $role = $this->bookingSchedule('venue', ['require_account' => true]);

        $this->postJson($this->storeUrl($role), $this->complete())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['create_account' => 'requires an account']);

        $this->assertSame(0, Event::count());
    }

    public function test_an_optional_account_needs_a_password_and_accepted_terms(): void
    {
        $role = $this->bookingSchedule();

        $this->postJson($this->storeUrl($role), $this->complete(['create_account' => '1']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password', 'terms']);

        $this->postJson($this->storeUrl($role), $this->complete(['create_account' => '1', 'password' => 'short', 'terms' => 'on']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonMissingValidationErrors(['terms']);

        $this->assertGuest();
        $this->assertSame(1, User::count());
        $this->assertSame(0, Event::count());
    }

    public function test_an_optional_account_is_created_and_owns_the_request(): void
    {
        $role = $this->bookingSchedule();

        $this->postJson($this->storeUrl($role), $this->complete([
            'create_account' => '1',
            'password' => 'long-enough-password',
            'terms' => 'on',
        ]))->assertOk();

        $user = User::where('email', 'sam.guest@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        $event = Event::where('name', 'Late Night Set')->firstOrFail();
        $this->assertSame($user->id, $event->user_id);
        $this->assertFalse($event->is_guest_submission);
    }

    public function test_a_selfhost_with_registration_closed_refuses_the_account(): void
    {
        $role = $this->bookingSchedule();
        $this->selfhost(false, gateLive: true);

        $this->postJson($this->storeUrl($role), $this->complete([
            'create_account' => '1',
            'password' => 'long-enough-password',
            'terms' => 'on',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account_email' => 'Account creation is disabled on this server.']);

        $this->assertSame(1, User::count());
        $this->assertSame(0, Event::count());
    }

    /**
     * createAndLoginUser() used to gate on config('app.hosted') alone, so ALLOW_REGISTRATION never
     * reached this path and every booking-form account on a selfhost was refused.
     */
    public function test_a_selfhost_that_opened_registration_creates_the_account(): void
    {
        $role = $this->bookingSchedule();
        $this->selfhost(true, gateLive: true);

        $this->postJson($this->storeUrl($role), $this->complete([
            'create_account' => '1',
            'password' => 'long-enough-password',
            'terms' => 'on',
        ]))->assertOk();

        $this->assertNotNull(User::where('email', 'sam.guest@gmail.com')->first());
        $this->assertSame(1, Event::count());
    }

    // -- Required default fields ---------------------------------------------------------------

    public function test_the_required_default_fields_are_enforced_on_the_server(): void
    {
        $role = $this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring(Role::BOOKING_FORM_REQUIRABLE_FIELDS),
        ]);

        $this->postJson($this->storeUrl($role), $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['event_name', 'date', 'start_time', 'description', 'location'])
            ->assertJsonFragment(['The Event Name field is required.']);

        $this->assertSame(0, Event::count());

        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();
        $this->assertSame(1, Event::count());
    }

    public function test_nothing_is_required_by_default(): void
    {
        $this->postJson($this->storeUrl($this->bookingSchedule('talent')), $this->payload())->assertOk();

        $this->assertSame(__('messages.booking_request'), Event::firstOrFail()->name);
    }

    public function test_a_date_is_only_accepted_with_a_time(): void
    {
        $role = $this->bookingSchedule();

        $this->postJson($this->storeUrl($role), $this->payload(['date' => now()->addDays(3)->format('Y-m-d')]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_time'])
            ->assertJsonMissingValidationErrors(['date']);

        $this->postJson($this->storeUrl($role), $this->payload(['start_time' => '19:30']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date'])
            ->assertJsonMissingValidationErrors(['start_time']);

        $this->assertSame(0, Event::count());
    }

    /**
     * The date rule accepted "15-09-2026", which createFromFormat('Y-m-d H:i') then threw on.
     */
    public function test_a_malformed_date_is_a_validation_error_not_a_crash(): void
    {
        $this->postJson($this->storeUrl($this->bookingSchedule()), $this->payload([
            'date' => '15-09-2026',
            'start_time' => '20:00',
        ]))->assertStatus(422)->assertJsonValidationErrors(['date']);
    }

    public function test_a_required_location_is_satisfied_by_a_venue_detail_or_by_online(): void
    {
        $role = $this->bookingSchedule('talent', ['booking_form_config' => $this->requiring(['location'])]);

        $this->postJson($this->storeUrl($role), $this->payload(['event_name' => 'City Only', 'venue_city' => 'Kassel']))
            ->assertOk();
        $cityOnly = Event::where('name', 'City Only')->firstOrFail();
        $this->assertTrue($cityOnly->roles()->where('roles.type', 'venue')->where('roles.city', 'Kassel')->exists());

        $this->postJson($this->storeUrl($role), $this->payload(['event_name' => 'Stream', 'is_online' => '1']))
            ->assertOk();
        $this->assertSame('online', Event::where('name', 'Stream')->firstOrFail()->event_url);

        $this->postJson($this->storeUrl($role), $this->payload(['event_name' => 'Nowhere']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['location' => 'Please enter a venue or choose Online.']);
        $this->assertNull(Event::where('name', 'Nowhere')->first());
    }

    public function test_location_is_never_required_on_a_venue_schedule(): void
    {
        $role = $this->bookingSchedule('venue', ['booking_form_config' => $this->requiring(['location'])]);
        $this->assertFalse($role->bookingFormRequires('location'));

        $this->postJson($this->storeUrl($role), $this->payload(['event_name' => 'At The Venue']))->assertOk();

        $event = Event::where('name', 'At The Venue')->firstOrFail();
        $this->assertTrue($event->roles()->where('roles.id', $role->id)->exists());
    }

    public function test_online_is_ignored_when_the_schedule_turned_it_off(): void
    {
        $role = $this->bookingSchedule('talent', ['booking_form_config' => ['allow_online' => false]]);

        $this->postJson($this->storeUrl($role), $this->payload([
            'event_name' => 'Sneaky Stream',
            'is_online' => '1',
            'event_url' => 'not a url',
        ]))->assertOk();

        $this->assertNull(Event::where('name', 'Sneaky Stream')->firstOrFail()->event_url);

        $required = $this->bookingSchedule('talent', ['booking_form_config' => $this->requiring(['location'], ['allow_online' => false])]);

        $this->postJson($this->storeUrl($required), $this->payload(['event_name' => 'Still Nowhere', 'is_online' => '1']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['location' => 'Please enter a venue.']);
    }

    public function test_a_request_that_fails_validation_creates_no_account(): void
    {
        $role = $this->bookingSchedule('venue', ['booking_form_config' => $this->requiring(['description'])]);

        $this->postJson($this->storeUrl($role), $this->complete([
            'description' => '',
            'create_account' => '1',
            'password' => 'long-enough-password',
            'terms' => 'on',
        ]))->assertStatus(422)->assertJsonValidationErrors(['description']);

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }
}
