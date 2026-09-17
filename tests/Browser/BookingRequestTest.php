<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

/**
 * The guest booking request form (issue #124).
 *
 * The form posts over fetch, so the browser's own constraint validation is the only thing between a
 * visitor and the server - and a Feature test's postJson() skips it entirely. That is how a hidden
 * `required` password field silently blocked every request from a guest who did not want an
 * account, for months, with every Feature test green. These journeys drive the real page.
 */
class BookingRequestTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    private const SUBDOMAIN = 'bookingvenue';

    public function test_a_guest_can_send_a_booking_request_without_an_account(): void
    {
        $this->seedBookingVenue();

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->fillBookingForm($browser, 'Guest Jam Session', 'A relaxed evening set.');

            // The #124 regression: with "Create an account" left alone, nothing may be invalid.
            $this->assertSame('', $this->invalidControls($browser), 'The booking form would refuse to submit');

            $this->submitBookingForm($browser);
            $browser->waitForTextIn('#form-message', __('messages.booking_request_submitted'), 15);

            $event = Event::where('name', 'Guest Jam Session')->first();
            $this->assertNotNull($event, 'The request never reached the server');
            $this->assertTrue((bool) $event->is_guest_submission);
            $this->assertNull($event->event_url);
            $this->assertSame(1, User::count(), 'No account may be created for a guest who did not ask for one');
        });
    }

    public function test_a_required_description_is_checked_before_anything_is_sent(): void
    {
        $this->seedBookingVenue([
            'booking_form_config' => ['required_fields' => ['description' => true]],
        ]);

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->fillBookingForm($browser, 'Needs A Description', null);

            // The description lives in an editor that hides its textarea, so requiring it must not
            // put a required attribute anywhere the browser would trip over.
            $this->assertSame('', $this->invalidControls($browser), 'A hidden control would block the submit');

            $this->submitBookingForm($browser);

            $browser->waitFor('#error-description', 10)
                ->assertSeeIn('#error-description', __('messages.field_is_required'))
                ->assertAttributeContains('[data-error-ring="description"]', 'class', 'ring-red-500')
                ->assertPathIs('/'.self::SUBDOMAIN.'/booking-request');
            $this->assertSame(0, Event::count(), 'Nothing may be sent while a required field is empty');

            $browser->script('document.getElementById("event_description")._easyMDE.value("Now it has one.");');
            $this->submitBookingForm($browser);
            $browser->waitForTextIn('#form-message', __('messages.booking_request_submitted'), 15)
                ->assertMissing('#error-description');

            $this->assertSame('Now it has one.', Event::where('name', 'Needs A Description')->firstOrFail()->description);
        });
    }

    public function test_a_schedule_can_turn_the_online_option_off(): void
    {
        $this->seedBookingVenue([
            'booking_form_config' => ['allow_online' => false],
        ]);

        $this->browse(function (Browser $browser) {
            // openBookingForm() waits for the date picker, which the page script creates after it
            // binds the Online checkbox - so this also proves a missing checkbox throws nothing.
            $this->openBookingForm($browser);

            $browser->assertNotPresent('#is_online')
                ->assertNotPresent('#online-url-field');

            $this->fillBookingForm($browser, 'In Person Only', 'At the venue.');
            $this->assertSame('', $this->invalidControls($browser));

            $this->submitBookingForm($browser);
            $browser->waitForTextIn('#form-message', __('messages.booking_request_submitted'), 15);

            $this->assertNull(Event::where('name', 'In Person Only')->firstOrFail()->event_url);
        });
    }

    public function test_the_owner_sets_the_booking_form_options_on_the_requests_tab(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);

            $browser->visit('/venue/edit')
                ->waitFor('#edit-form', 15);

            $browser->script('
                document.querySelector(\'a[data-section="section-engagement"]\').click();
                document.querySelector(\'.engagement-tab[data-tab="requests"]\').click();
                var accept = document.getElementById("accept_requests");
                if (!accept.checked) { accept.click(); }
                var requireAccount = document.getElementById("require_account");
                if (requireAccount.checked) { requireAccount.click(); }
                document.querySelector(\'input[name="event_request_form"][value="import"]\').click();
            ');

            // The form picker is visible, so the tab is showing and a hidden section below means hidden
            // by the rule, not by an inactive tab.
            $browser->waitFor('#event_request_form_section', 10)
                ->waitUntilMissing('#booking_form_section', 10);

            // Picking the Booking Form shows the options, without a Location row on a venue.
            $browser->script('document.querySelector(\'input[name="event_request_form"][value="booking"]\').click();');
            $browser->waitFor('#booking_form_section', 10)
                ->assertPresent('#booking_required_description')
                ->assertNotPresent('#booking_required_location');

            // Require Account hides them again: those guests are sent to sign up instead.
            $browser->script('document.getElementById("require_account").click();');
            $browser->waitUntilMissing('#booking_form_section', 10);
            $browser->script('document.getElementById("require_account").click();');
            $browser->waitFor('#booking_form_section', 10);

            $browser->script('
                document.getElementById("booking_required_description").click();
                var online = document.getElementById("booking_allow_online");
                if (online.checked) { online.click(); }
                window._skipUnsavedWarning = true;
                document.getElementById("edit-form").requestSubmit();
            ');

            $this->landOn($browser, '/venue/schedule', 45);

            $venue = Role::where('subdomain', 'venue')->firstOrFail();
            $this->assertTrue((bool) $venue->accept_requests);
            $this->assertFalse((bool) $venue->require_account);
            $this->assertSame('booking', $venue->event_request_form);
            $this->assertTrue($venue->bookingFormRequires('description'));
            $this->assertFalse($venue->bookingFormRequires('event_name'));
            $this->assertFalse($venue->bookingFormAllowsOnline());
        });
    }

    /**
     * A claimed venue that takes booking-form requests from guests. Claimed (a verified address,
     * user_id and the owner pivot) because acceptEventRequests() only honours accept_requests on a
     * claimed schedule, and bookingRequest() borrows the owner as the stand-in submitter. No address,
     * so saving does not try to geocode.
     */
    private function seedBookingVenue(array $attributes = []): Role
    {
        $owner = User::create([
            'name' => 'Venue Owner',
            'email' => 'venue.owner@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $owner->email_verified_at = now();
        $owner->save();

        $role = new Role;
        $role->type = 'venue';
        $role->subdomain = self::SUBDOMAIN;
        $role->name = 'Booking Venue';
        $role->email = 'booking.venue@gmail.com';
        $role->email_verified_at = now();
        $role->user_id = $owner->id;
        $role->timezone = 'America/New_York';
        $role->language_code = 'en';
        $role->accept_requests = true;
        $role->require_account = false;
        $role->require_approval = true;
        $role->event_request_form = 'booking';

        foreach ($attributes as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();
        $role->users()->attach($owner->id, ['level' => 'owner']);

        return $role;
    }

    /**
     * Open the form as a signed-out visitor and wait for the page script to finish booting. The date
     * picker is created at the end of the page's own DOMContentLoaded handler, so its presence also
     * proves nothing earlier in that handler threw.
     */
    private function openBookingForm(Browser $browser): void
    {
        $this->startFromACleanSession($browser);

        $browser->visit('/'.self::SUBDOMAIN.'/booking-request')
            ->assertPathIs('/'.self::SUBDOMAIN.'/booking-request')
            ->waitFor('#booking-request-form', 15)
            ->waitUntil('!!document.getElementById("event_date")._flatpickr', 15)
            ->waitUntil('!!document.getElementById("event_description")._easyMDE', 15);
    }

    /**
     * Fill the form by script. Headless Chrome drops keystrokes from type() here, and the date and
     * description are widgets rather than plain inputs anyway.
     */
    private function fillBookingForm(Browser $browser, string $eventName, ?string $description): void
    {
        $date = now()->addDays(10)->format('Y-m-d');

        $browser->script('
            function setValue(id, value) {
                var field = document.getElementById(id);
                if (!field) { return; }
                field.value = value;
                field.dispatchEvent(new Event("input", { bubbles: true }));
                field.dispatchEvent(new Event("change", { bubbles: true }));
            }
            setValue("event_name", '.json_encode($eventName).');
            document.getElementById("event_date")._flatpickr.setDate('.json_encode($date).', true);
            setValue("event_start_time", "20:00");
            '.($description === null ? '' : 'document.getElementById("event_description")._easyMDE.value('.json_encode($description).');').'
            setValue("contact_name", "Guest Visitor");
            setValue("contact_email", "guest.visitor@gmail.com");
        ');

        $hidden = $browser->script('return [document.getElementById("hidden_date").value, document.getElementById("hidden_start_time").value];')[0];
        $this->assertSame([$date, '20:00'], $hidden, 'The date and time pickers did not fill their hidden inputs');
    }

    /**
     * The controls the browser would refuse to submit, by name. requestSubmit() does nothing at all
     * when one is invalid - no navigation, no message - so name them instead of timing out.
     */
    private function invalidControls(Browser $browser): string
    {
        return (string) $browser->script('
            var form = document.getElementById("booking-request-form");
            if (!form) { return "no booking form on " + window.location.pathname; }
            return Array.from(form.elements).filter(function (el) {
                return el.willValidate && !el.checkValidity();
            }).map(function (el) {
                return el.name || el.id || el.tagName;
            }).join(", ");
        ')[0];
    }

    /**
     * requestSubmit() rather than form.submit(): only the former runs validation and the page's own
     * submit handler, which is what a visitor pressing the button gets.
     */
    private function submitBookingForm(Browser $browser): void
    {
        $browser->script('document.getElementById("booking-request-form").requestSubmit();');
    }
}
