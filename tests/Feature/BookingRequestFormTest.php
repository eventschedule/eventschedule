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
            // ask_phone alongside the required_fields: `phone` is requirable but only reaches the
            // page when the schedule asks for it, so without this the scenario would quietly stop
            // covering the field it claims to require.
            'talent requiring every field, online off' => ['talent', ['booking_form_config' => ['required_fields' => array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, true), 'allow_online' => false, 'ask_phone' => true]], null],
            'venue requiring every field, online off' => ['venue', ['booking_form_config' => ['required_fields' => array_fill_keys(Role::BOOKING_FORM_REQUIRABLE_FIELDS, true), 'allow_online' => false, 'ask_phone' => true]], null],
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
        // Scoped past the contact labels: name and email are required of every guest whatever the
        // owner configured, so they carry an unconditional marker. This test is about the fields the
        // owner CAN configure, and counting every label on the page would conflate the two.
        $configurable = "//label[not(@for='contact_name' or @for='contact_email' or @for='contact_phone')]//span[@aria-hidden='true'][normalize-space()='*']";

        $plain = $this->page($this->bookingSchedule('talent'));
        $this->assertSame(0, $plain->query('//*[@aria-required="true"]')->length);
        $this->assertSame(0, $plain->query($configurable)->length);

        // ... and the two constants really are marked.
        $this->assertSame(2, $plain->query("//label[@for='contact_name' or @for='contact_email']//span[@aria-hidden='true'][normalize-space()='*']")->length);

        $role = $this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring(['event_name', 'description']),
        ]);
        $xpath = $this->page($role);

        $this->assertSame('true', $this->node($xpath, "//input[@id='event_name']")->getAttribute('aria-required'));
        $this->assertSame('true', $this->node($xpath, "//textarea[@id='event_description']")->getAttribute('aria-required'));
        $this->assertFalse($this->node($xpath, "//input[@id='event_date']")->hasAttribute('aria-required'));
        $this->assertSame(2, $xpath->query($configurable)->length);

        // Checked by the page script, never by a native required attribute.
        $this->assertFalse($this->node($xpath, "//input[@id='event_name']")->hasAttribute('required'));
        $this->assertFalse($this->node($xpath, "//textarea[@id='event_description']")->hasAttribute('required'));

        // Phone follows the same contract once the schedule asks for it.
        $asked = $this->page($this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]));
        $this->assertFalse($this->node($asked, "//input[@id='contact_phone']")->hasAttribute('aria-required'));
        $this->assertSame(0, $asked->query("//label[@for='contact_phone']//span[@aria-hidden='true'][normalize-space()='*']")->length);

        $required = $this->page($this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring(['phone'], ['ask_phone' => true]),
        ]));
        $this->assertSame('true', $this->node($required, "//input[@id='contact_phone']")->getAttribute('aria-required'));
        $this->assertFalse($this->node($required, "//input[@id='contact_phone']")->hasAttribute('required'));
        $this->assertSame(1, $required->query("//label[@for='contact_phone']//span[@aria-hidden='true'][normalize-space()='*']")->length);
    }

    public function test_every_field_has_an_error_anchor_even_when_optional(): void
    {
        $xpath = $this->page($this->bookingSchedule('talent'));

        foreach (['event_name', 'date', 'start_time', 'description', 'location', 'event_url', 'contact_name', 'account_name', 'contact_email', 'account_email', 'password', 'terms', 'create_account'] as $key) {
            $anchor = $this->node($xpath, "//*[@data-error-for='$key']");
            $this->assertNotNull($anchor, "missing the $key error anchor");
            $this->assertSame('error-'.$key, $anchor->getAttribute('id'));
        }

        // contact_phone needs its own page: the schedule above does not ask for a phone, so its
        // anchor is legitimately absent there.
        $this->assertNull($this->node($xpath, "//*[@data-error-for='contact_phone']"));

        $withPhone = $this->page($this->bookingSchedule('talent', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]));
        $anchor = $this->node($withPhone, "//*[@data-error-for='contact_phone']");
        $this->assertNotNull($anchor, 'missing the contact_phone error anchor');
        $this->assertSame('error-contact_phone', $anchor->getAttribute('id'));
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

    // -- The submitter's contact details -----------------------------------------------------

    public function test_a_guest_request_keeps_the_contact_details_it_asked_for(): void
    {
        $role = $this->bookingSchedule('venue');

        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();

        $event = Event::latest('id')->first();
        $this->assertSame('Sam Guest', $event->contact_name);
        $this->assertSame('sam.guest@gmail.com', $event->contact_email);
        $this->assertNull($event->contact_phone);

        // The row itself still borrows the owner, which is exactly why the columns have to exist.
        $this->assertTrue($event->is_guest_submission);
        $this->assertSame($role->user->id, $event->user_id);
    }

    public function test_a_signed_in_request_records_that_account(): void
    {
        $role = $this->bookingSchedule('venue');
        $visitor = User::factory()->create(['name' => 'Dana Member', 'email' => 'dana.member@gmail.com']);

        $this->actingAs($visitor)
            ->postJson($this->storeUrl($role), $this->complete())
            ->assertOk();

        $event = Event::latest('id')->first();
        $this->assertSame('Dana Member', $event->contact_name);
        $this->assertSame('dana.member@gmail.com', $event->contact_email);
        $this->assertFalse($event->is_guest_submission);
    }

    public function test_a_phone_is_only_stored_when_the_schedule_asks_for_one(): void
    {
        $silent = $this->bookingSchedule('venue');
        $this->postJson($this->storeUrl($silent), $this->complete(['contact_phone' => '+49 170 1234567']))->assertOk();
        $this->assertNull(Event::latest('id')->first()->contact_phone);

        $asking = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]);
        $this->postJson($this->storeUrl($asking), $this->complete(['contact_phone' => '+49 170 1234567']))->assertOk();
        $this->assertSame('+49 170 1234567', Event::latest('id')->first()->contact_phone);
    }

    public function test_a_phone_is_scrubbed_of_markup(): void
    {
        $role = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]);

        $this->postJson($this->storeUrl($role), $this->complete([
            'contact_phone' => '  <b>555 1234</b>  ',
        ]))->assertOk();

        $this->assertSame('555 1234', Event::latest('id')->first()->contact_phone);
    }

    public function test_a_phone_is_required_only_where_the_owner_required_it(): void
    {
        $optional = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]);
        $this->postJson($this->storeUrl($optional), $this->complete())->assertOk();

        $required = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring(['phone'], ['ask_phone' => true]),
        ]);
        $this->postJson($this->storeUrl($required), $this->complete())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact_phone']);
    }

    /**
     * A stored `phone => true` is inert while the field is switched off, so an owner who required a
     * phone and then stopped asking for one does not lock every visitor out of the form.
     */
    public function test_a_required_phone_is_inert_while_the_schedule_does_not_ask_for_one(): void
    {
        $role = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring(['phone'], ['ask_phone' => false]),
        ]);

        $this->assertFalse($role->bookingFormRequires('phone'));
        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();
    }

    /**
     * The phone field sits outside the signed-out-only contact block, because an account supplies a
     * name and an email but never a phone number.
     */
    public function test_the_phone_field_renders_for_a_signed_in_visitor_too(): void
    {
        $role = $this->bookingSchedule('venue', [
            'booking_form_config' => $this->requiring([], ['ask_phone' => true]),
        ]);
        $visitor = User::factory()->create();

        $xpath = $this->page($role, $visitor);

        $this->assertNotNull($this->node($xpath, "//input[@id='contact_phone']"));
        $this->assertNull($this->node($xpath, "//input[@id='contact_name']"));
        $this->assertNull($this->node($xpath, "//input[@id='contact_email']"));
    }

    public function test_the_phone_field_is_absent_unless_the_schedule_asks_for_it(): void
    {
        $this->assertNull($this->node($this->page($this->bookingSchedule('venue')), "//input[@id='contact_phone']"));
    }

    /**
     * The form takes a name, an email and possibly a phone from somebody with no account, so it has
     * to say who sees them - the same disclosure the Follow flow already makes.
     */
    public function test_the_form_discloses_who_sees_the_contact_details(): void
    {
        $role = $this->bookingSchedule('venue');

        $this->get($this->pageUrl($role))
            ->assertOk()
            ->assertSee(__('messages.booking_contact_privacy_note'), false)
            ->assertSee(policy_url('privacy'), false);
    }

    /**
     * Not in $fillable, deliberately: EventRepo::saveEvent() does a blanket fill($request->all())
     * that an anonymous guest reaches through guestImport(), so a fillable contact block could be
     * forged, and buildClonePayload() walks getFillable() and would copy a stranger's address onto
     * every clone.
     */
    public function test_the_contact_columns_are_not_mass_assignable(): void
    {
        $fillable = (new Event)->getFillable();

        foreach (['contact_name', 'contact_email', 'contact_phone'] as $column) {
            $this->assertNotContains($column, $fillable, "$column must not be mass assignable");
        }
    }

    public function test_a_clone_does_not_carry_the_submitters_details(): void
    {
        $role = $this->bookingSchedule('venue');
        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();

        $payload = \App\Repos\EventRepo::buildClonePayload(Event::latest('id')->first());

        $this->assertArrayNotHasKey('contact_name', $payload);
        $this->assertArrayNotHasKey('contact_email', $payload);
        $this->assertArrayNotHasKey('contact_phone', $payload);
    }

    /**
     * Owner-facing only. Every Event serializer in the app is field-explicit, and this fails if one
     * is ever refactored into something generic.
     */
    public function test_the_contact_details_stay_out_of_the_public_api_payload(): void
    {
        $role = $this->bookingSchedule('venue');
        $this->postJson($this->storeUrl($role), $this->complete())->assertOk();

        $data = json_decode(json_encode(Event::latest('id')->first()->toApiData()), true);

        foreach (['contact_name', 'contact_email', 'contact_phone'] as $column) {
            $this->assertArrayNotHasKey($column, $data);
        }
    }

    /**
     * The CSP carries a nonce, which disables the 'unsafe-inline' fallback, so one inline script
     * without a nonce is a hard block. Reported against this page more than once.
     */
    public function test_every_inline_script_on_the_page_carries_a_nonce(): void
    {
        $html = $this->get($this->pageUrl($this->bookingSchedule('venue')))->assertOk()->getContent();

        preg_match_all('#<script\b([^>]*)>#i', $html, $matches);

        foreach ($matches[1] as $attributes) {
            if (stripos($attributes, 'src=') !== false) {
                continue;
            }

            $this->assertStringContainsStringIgnoringCase('nonce=', $attributes, 'inline <script'.$attributes.'> has no CSP nonce');
        }
    }
}
