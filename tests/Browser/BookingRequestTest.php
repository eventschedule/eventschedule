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
 * The guest booking request form.
 *
 * It began with issue #124: the form posts over fetch, so the browser's own constraint validation
 * was the only thing between a visitor and the server, and a hidden `required` password field
 * silently blocked every request from a guest who did not want an account, for months, with every
 * Feature test green. The page now judges every field itself and says what is missing on a bar
 * that stays on screen. These journeys drive the real page.
 *
 * The browser-test install is not hosted and takes no new accounts, so the page offers none here:
 * the account and its emailed code are held by BookingRequestFormTest and
 * BookingRequestProtectionTest on the server.
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

            // Before anything is typed the bar says nothing; once begun, it says what is left.
            $this->assertSame('', $this->barText($browser));
            $this->fillBookingForm($browser, 'Guest Jam Session', 'A relaxed evening set.');
            $this->assertStringContainsString('Ready to send to Booking Venue', $this->barText($browser));

            $this->pressSend($browser);
            $this->waitForSent($browser);

            $sent = $this->sentText($browser);
            // A venue that reviews: the event is not on the schedule yet, and the page says who answers and where.
            $this->assertStringContainsString('Request sent', $sent);
            $this->assertStringContainsString('Guest Jam Session', $sent);
            $this->assertStringContainsString('replies to you directly, at guest.visitor@gmail.com', $sent);
            $browser->assertPathIs('/'.self::SUBDOMAIN.'/booking-request');

            $event = Event::where('name', 'Guest Jam Session')->first();
            $this->assertNotNull($event, 'The request never reached the server');
            $this->assertTrue((bool) $event->is_guest_submission);
            $this->assertSame('guest.visitor@gmail.com', $event->contact_email);
            $this->assertNull($event->event_url);
            $this->assertSame(1, User::count(), 'No account may be created for a guest who did not ask for one');
        });
    }

    public function test_pressing_send_with_things_missing_says_which_in_the_pages_own_words(): void
    {
        $this->seedBookingVenue([
            'booking_form_config' => ['required_fields' => ['description' => true]],
        ]);

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->pressSend($browser);

            // Every missing thing, named on the bar, in page order, each a way to its field; and
            // the reason under the field. Never the browser's own bubble: no control is `required`.
            $this->assertSame('Still needed: Description, Name, Email', $this->barText($browser));
            $this->assertTrue($browser->script('var r = document.querySelector(".gs-bar-status").getBoundingClientRect(); return r.top >= 0 && r.bottom <= window.innerHeight;')[0], 'the bar is on screen');
            $browser->assertSeeIn('#err_description', 'Required')
                ->assertSeeIn('#err_account_email', 'Required');
            $this->assertSame(0, $browser->script('return document.querySelectorAll("#booking-request-form [required]").length;')[0]);
            $this->assertSame(0, Event::count(), 'Nothing may be sent while a required field is empty');

            // Fill the rest and leave the description out: the bar shortens to the one thing left.
            $this->fillBookingForm($browser, 'Needs A Description', null);
            $this->assertSame('Still needed: Description', $this->barText($browser));
            $this->pressSend($browser);
            $this->assertSame(0, Event::count());

            $browser->script('window.__submitApp.setDescription("Now it has one.");');
            $browser->pause(200);
            $this->pressSend($browser);
            $this->waitForSent($browser);

            $this->assertSame('Now it has one.', Event::where('name', 'Needs A Description')->firstOrFail()->description);
        });
    }

    public function test_a_schedule_can_turn_the_online_option_off(): void
    {
        $this->seedBookingVenue([
            'booking_form_config' => ['allow_online' => false],
        ]);

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);

            $browser->assertNotPresent('#is_online')
                ->assertNotPresent('#submit_event_url');

            $this->fillBookingForm($browser, 'In Person Only', 'At the venue.');
            $this->pressSend($browser);
            $this->waitForSent($browser);

            $this->assertNull(Event::where('name', 'In Person Only')->firstOrFail()->event_url);
        });
    }

    /** Times are typed the way they are said, kept to the minute, and an end time is the event's length. */
    public function test_a_time_typed_as_it_is_said_and_an_end_time_are_kept(): void
    {
        $this->seedBookingVenue();

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->fillBookingForm($browser, 'Late Set', 'Two sets.');

            $this->typeTime($browser, 'start', '7:15p');
            $this->typeTime($browser, 'end', '11pm');
            $state = $browser->script('var a = window.__submitApp; return [document.getElementById("submit_event_time").value, a.event.event_start_time, document.getElementById("submit_event_end_time").value, a.event.event_end_time];')[0];
            $this->assertSame(['7:15 PM', '19:15', '11:00 PM', '23:00'], $state);

            // Something that is not a time is refused in words, at once, and never guessed at.
            $this->typeTime($browser, 'end', 'late');
            $this->pressSend($browser);
            $browser->assertSeeIn('#err_event_end_time', 'Enter a time like');
            $this->assertSame(0, Event::count());

            $this->typeTime($browser, 'end', '23:00');
            $this->pressSend($browser);
            $this->waitForSent($browser);

            $event = Event::where('name', 'Late Set')->firstOrFail();
            $this->assertSame('19:15', \Carbon\Carbon::parse($event->starts_at, 'UTC')->setTimezone('America/New_York')->format('H:i'));
            $this->assertEquals(3.75, $event->duration);
        });
    }

    /** Nothing typed used to survive a reload. The request is kept in the browser; who is asking, for the tab. */
    public function test_a_reload_keeps_the_request_and_who_is_asking(): void
    {
        $this->seedBookingVenue();

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->fillBookingForm($browser, 'Half Written', 'Still deciding on the second set.');
            $browser->pause(900);

            $browser->refresh();
            $this->waitForThePage($browser);
            $browser->pause(400);

            $state = $browser->script('var a = window.__submitApp; return { name: a.event.name, date: a.event.event_date, start: a.event.event_start_time, text: a.event.description, who: a.userName, email: a.userEmail, shown: a.shownInput(a.pickers.date).value, banner: a.draftRestored };')[0];
            $this->assertSame('Half Written', $state['name']);
            $this->assertSame(now()->addDays(10)->format('Y-m-d'), $state['date']);
            $this->assertSame('20:00', $state['start']);
            $this->assertSame('Still deciding on the second set.', $state['text']);
            $this->assertSame(['Guest Visitor', 'guest.visitor@gmail.com'], [$state['who'], $state['email']]);
            $this->assertNotSame('', $state['shown'], 'the date is back in the box a person sees');
            $this->assertTrue($state['banner']);
        });
    }

    /**
     * A page left open past its session used to answer "CSRF token mismatch." under the button,
     * below the screen on a phone. It now says what to do, offers the one thing that works, and
     * keeps what was typed through it. The expired session is stood in for; the bar, the button
     * and what survives the reload are the page's own.
     */
    public function test_an_expired_page_offers_reload_and_keeps_what_was_typed(): void
    {
        $this->seedBookingVenue();

        $this->browse(function (Browser $browser) {
            $this->openBookingForm($browser);
            $this->fillBookingForm($browser, 'Left Open Overnight', 'Typed yesterday.');
            $browser->script('
                var real = window.fetch;
                window.fetch = function (url) {
                    if (String(url).indexOf("booking-request") !== -1) {
                        return Promise.resolve(new Response(JSON.stringify({ message: "CSRF token mismatch." }), { status: 419, headers: { "Content-Type": "application/json" } }));
                    }
                    return real.apply(this, arguments);
                };
            ');

            $this->pressSend($browser);
            $browser->waitFor('#reload-btn', 10)->assertMissing('#submit-btn');
            $this->assertStringContainsString('Reload it to continue', $this->barText($browser));
            $this->assertStringNotContainsString('CSRF', $this->barText($browser));
            // Typing does not make the message go away: the page is still expired.
            $browser->type('#submit_event_name', 'Left Open Until Morning')->pause(200);
            $this->assertStringContainsString('Reload it to continue', $this->barText($browser));

            $browser->click('#reload-btn');
            $this->waitForThePage($browser);
            $browser->pause(400);

            // What was typed after the page expired is kept too: Reload writes the draft before it leaves.
            $state = $browser->script('var a = window.__submitApp; return [a.event.name, a.event.description, a.userEmail, !!document.getElementById("submit-btn")];')[0];
            $this->assertSame(['Left Open Until Morning', 'Typed yesterday.', 'guest.visitor@gmail.com', true], $state);
            $this->assertSame(0, Event::count());
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
     * Open the form as a signed-out visitor and wait for the page to finish booting: the Vue app
     * mounted, and the date picker and the description editor it creates once the document is ready.
     */
    private function openBookingForm(Browser $browser): void
    {
        $this->startFromACleanSession($browser);

        $browser->visit('/'.self::SUBDOMAIN.'/booking-request')
            ->assertPathIs('/'.self::SUBDOMAIN.'/booking-request');
        $this->waitForThePage($browser);
    }

    private function waitForThePage(Browser $browser): void
    {
        $browser->waitFor('#booking-request-form', 15)
            ->waitUntil('window.__submitApp !== undefined && !! window.__submitApp.pickers.date', 15)
            ->waitUntil('!! document.getElementById("submit_description")._easyMDE', 15);
    }

    /**
     * Fill the form. The name, the place and the person are typed; the date and the description are
     * widgets, set the way the page itself sets them.
     */
    private function fillBookingForm(Browser $browser, string $eventName, ?string $description): void
    {
        $date = now()->addDays(10)->format('Y-m-d');

        $browser->type('#submit_event_name', $eventName);
        $browser->script('window.__submitApp.setDate('.json_encode($date).'); window.__submitApp.setTime("start", "20:00");'
            .($description === null ? '' : ' window.__submitApp.setDescription('.json_encode($description).');'));
        $browser->type('#account_name', 'Guest Visitor')
            ->type('#account_email', 'guest.visitor@gmail.com')
            ->pause(200);

        $state = $browser->script('var a = window.__submitApp; return [a.event.name, a.event.event_date, a.event.event_start_time, a.userName, a.userEmail];')[0];
        $this->assertSame([$eventName, $date, '20:00', 'Guest Visitor', 'guest.visitor@gmail.com'], $state, 'The form did not take what was put into it');
    }

    /**
     * A time typed into its box. The box is emptied through the page first: WebDriver's own clear()
     * fires no input event, so the page would put the old time back before the new one was typed.
     */
    private function typeTime(Browser $browser, string $which, string $text): void
    {
        $box = $which === 'start' ? '#submit_event_time' : '#submit_event_end_time';

        $browser->script('window.__submitApp.setTime('.json_encode($which).', "");');
        $browser->pause(150)->type($box, $text)->keys($box, '{tab}')->pause(200);
    }

    /** What the bar's status line reads. */
    private function barText(Browser $browser): string
    {
        return trim(preg_replace('/\s+/', ' ', $browser->script('var b = document.querySelector(".gs-bar-status"); return getComputedStyle(b).display === "none" ? "" : b.innerText;')[0]));
    }

    /** The button on the bar, pressed as a visitor presses it. */
    private function pressSend(Browser $browser): void
    {
        $browser->script('document.getElementById("submit-btn").click();');
        $browser->pause(500);
    }

    private function waitForSent(Browser $browser): void
    {
        $browser->waitFor('#submission-success-heading', 15);
        // The card rises into place; read it once it has arrived.
        $browser->pause(900);
    }

    private function sentText(Browser $browser): string
    {
        return trim(preg_replace('/\s+/', ' ', $browser->script('return document.getElementById("event-submit-app").innerText;')[0]));
    }
}
