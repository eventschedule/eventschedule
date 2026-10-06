<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The public "Submit your event" page as a stranger uses it: say what the event is, say who you
 * are, press Submit, type the emailed code, see what was sent.
 *
 * tests/Feature/GuestSubmitPageTest.php holds the markup and GuestSubmitProtectionTest the
 * endpoint. This runs the page: what the bar says, where a refusal lands, what a typed time
 * becomes and what survives a reload are all decided by its script.
 *
 * The browser-test install is not hosted and takes no new accounts by default, so each journey
 * that makes one switches the page's own two flags (registrationEnabled, and requiresCode for the
 * code step); the server, under APP_TESTING, accepts the account and does not check the code.
 */
class GuestSubmitJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $owner;

    private Role $curator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['email_verified_at' => now()]);

        $role = new Role;
        $role->subdomain = 'journeycurator';
        $role->user_id = $this->owner->id;
        $role->type = 'curator';
        $role->name = 'Springfield Live';
        $role->email = 'journeycurator@gmail.com';
        $role->timezone = 'America/New_York';
        $role->country_code = 'us';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->accept_requests = true;
        $role->require_account = true;
        $role->require_approval = true;
        $role->event_custom_fields = [
            'cf1' => ['name' => 'Contact phone', 'type' => 'string', 'show_on_request' => true],
        ];
        $role->save();
        $role->users()->attach($this->owner->id, ['level' => 'owner']);
        $this->curator = $role->fresh();
    }

    private function open(Browser $browser, bool $withCode = false): void
    {
        // One browser runs every journey, and a journey that submits leaves it signed in as the
        // account it made. The tables are emptied between tests, so the next test's first new
        // user would get that same id and the page would greet a stranger as signed in.
        $browser->driver->manage()->deleteAllCookies();

        // The page restores an unfinished event as it loads, and the last journey may have left
        // one. Storage belongs to the origin, so it can only be emptied from a page on it: load,
        // empty, and load again for the page the journey uses.
        $browser->visit('/journeycurator/guest-submit?visit='.uniqid())
            ->waitUntil('window.__submitApp !== undefined', 15);
        $browser->script('
            try { Object.keys(sessionStorage).forEach(function (k) { if (k.indexOf("es_guest_submit_") === 0) sessionStorage.removeItem(k); }); } catch (e) {}
            try { Object.keys(localStorage).forEach(function (k) { if (k.indexOf("es_guest_submit_") === 0) localStorage.removeItem(k); }); } catch (e) {}
            window.__submitApp.submitted = true;
        ');

        $browser->visit('/journeycurator/guest-submit?visit='.uniqid())
            ->waitUntil('window.__submitApp !== undefined && !! window.__submitApp.pickers.date', 15);
        $browser->script('
            var a = window.__submitApp;
            a.registrationEnabled = true;
            a.accountMode = "register";
            a.requiresCode = '.($withCode ? 'true' : 'false').';
        ');
        $browser->pause(200);
    }

    /** What the bar's status line reads. */
    private function barText(Browser $browser): string
    {
        return trim(preg_replace('/\s+/', ' ', $browser->script('return document.querySelector(".gs-bar-status").innerText;')[0]));
    }

    private function fillEvent(Browser $browser, string $name = 'Jazz Night'): void
    {
        $browser->type('#submit_event_name', $name);
        $browser->script('window.__submitApp.setDate("'.now()->addDays(12)->format('Y-m-d').'");');
        $browser->type('#submit_event_time', '7:15pm')->keys('#submit_event_time', '{tab}');
        $browser->type('#submit_venue_name', 'The Blue Room')
            ->type('#submit_venue_city', 'Springfield');
    }

    private function fillAccount(Browser $browser, string $email = 'sam.rivera@gmail.com'): void
    {
        $browser->type('#account_email', $email)->keys('#account_email', '{tab}')->pause(600)
            ->type('#account_name', 'Sam Rivera')
            ->type('#account_password', 'correct-horse');
        $browser->script('document.getElementById("account_terms").scrollIntoView({ block: "center" });');
        $browser->pause(150)->check('#account_terms');
    }

    private function pressSubmit(Browser $browser): void
    {
        $browser->script('document.querySelector(".gs-bar-go").click();');
        $browser->pause(500);
    }

    public function test_pressing_submit_on_an_empty_form_says_what_is_missing_and_takes_you_there(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            // Before anything is typed the bar is its two buttons and says nothing.
            $this->assertFalse($browser->script('return document.querySelector(".gs-bar-status").offsetParent !== null;')[0]);

            $this->pressSubmit($browser);

            $bar = $this->barText($browser);
            $this->assertStringStartsWith('Still needed: Event Name, Date, Start Time', $bar);
            // Three names, then a count: a longer list covered the field on a phone.
            $this->assertMatchesRegularExpression('/, \+\d+$/', $bar);
            $this->assertSame('Required', trim($browser->text('#err_name')));

            $state = $browser->script('
                var bar = document.getElementById("submit-bar").getBoundingClientRect();
                return { focused: document.activeElement.id, barOnScreen: bar.top >= 0 && bar.bottom <= window.innerHeight + 1 };
            ')[0];
            $this->assertSame('submit_event_name', $state['focused']);
            $this->assertTrue($state['barOnScreen'], 'the bar stays on screen wherever the page is');

            // Each name in the bar is a way to its field.
            $browser->script('document.querySelectorAll(".gs-bar-status button")[1].click();');
            $browser->pause(700);
            $this->assertTrue($browser->script('return document.activeElement === window.__submitApp.shownInput(window.__submitApp.pickers.date);')[0]);
        });
    }

    public function test_a_time_is_kept_to_the_minute_and_an_unreadable_one_is_refused_in_words(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $browser->type('#submit_event_time', '7:15pm')->keys('#submit_event_time', '{tab}')->pause(200);
            $state = $browser->script('return { shown: document.getElementById("submit_event_time").value, kept: window.__submitApp.event.event_start_time };')[0];
            $this->assertSame('7:15 PM', $state['shown']);
            $this->assertSame('19:15', $state['kept']);

            // An end before the start is the next day, and the page says so.
            $browser->type('#submit_event_end_time', '1am')->keys('#submit_event_end_time', '{tab}')->pause(200);
            $browser->assertSee('Ends the next day');

            // "715pm" could be 7:15 or a slip: refused, never guessed at.
            $browser->type('#submit_event_time', '715pm')->keys('#submit_event_time', '{tab}')->pause(200);
            $this->pressSubmit($browser);
            $this->assertSame('Enter a time like 7:15 PM', trim($browser->text('#err_event_start_time')));
            $this->assertSame('', $browser->script('return window.__submitApp.event.event_start_time;')[0]);
        });
    }

    /**
     * A second flyer replaces what the first one wrote. It used to find every field already
     * filled, so the picture was the new flyer's and every word the old one's, and Undo, its
     * memory written over, took back nothing. The flyer reader is an AI service and is stood in
     * for here; the tile, the fields, the date and time boxes and Undo are the page's own.
     */
    public function test_a_second_flyer_replaces_what_the_first_one_wrote_and_undo_takes_it_back(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            $first = now()->addDays(20)->format('Y-m-d');
            $second = now()->addDays(30)->format('Y-m-d');
            $browser->script('
                var a = window.__submitApp;
                a.aiEnabled = true;
                window.__flyers = [
                    { event_name: "Jazz Night", event_date_time: "'.$first.' 19:15", event_duration: 2, venue_name: "The Blue Room", event_city: "Springfield", event_postal_code: 62701 },
                    { event_name: "Folk Evening", event_date_time: "'.$second.' 20:00", venue_name: "Green Hall", event_city: "Shelbyville" }
                ];
                var real = window.fetch;
                window.fetch = function (url) {
                    if (String(url).indexOf("guest-parse") !== -1) {
                        return Promise.resolve(new Response(JSON.stringify([window.__flyers.shift()]), { status: 200, headers: { "Content-Type": "application/json" } }));
                    }
                    return real.apply(this, arguments);
                };
                window.__addFlyer = function () {
                    a.setFlyer(new File([new Uint8Array([137, 80, 78, 71])], "flyer.png", { type: "image/png" }));
                };
            ');
            $read = 'var a = window.__submitApp; return { name: a.event.name, date: a.event.event_date, start: a.event.event_start_time, end: a.event.event_end_time,'
                .' venue: a.event.venue_name, city: a.event.venue_city, postal: a.event.venue_postal_code, state: a.event.venue_state, filled: a.autoFillDone,'
                .' startBox: document.getElementById("submit_event_time").value, endBox: document.getElementById("submit_event_end_time").value,'
                .' dateBox: a.shownInput(a.pickers.date).value, tile: document.querySelector(".gs-flyer-text").innerText };';

            $browser->script('window.__addFlyer();');
            $browser->waitUntil('window.__submitApp.autoFillDone && ! window.__submitApp.flyerBusy', 10);
            $state = $browser->script($read)[0];
            $this->assertSame(['Jazz Night', $first, '19:15', '21:15', 'The Blue Room', 'Springfield', '62701'],
                [$state['name'], $state['date'], $state['start'], $state['end'], $state['venue'], $state['city'], $state['postal']]);
            $this->assertSame('7:15 PM', $state['startBox']);
            $this->assertStringContainsString('Form filled', $state['tile']);

            // Something the visitor adds themselves, which neither flyer says.
            $browser->type('#submit_venue_state', 'IL');

            $browser->script('window.__addFlyer();');
            $browser->waitUntil('window.__submitApp.event.name === "Folk Evening" && ! window.__submitApp.flyerBusy', 10);
            $state = $browser->script($read)[0];
            $this->assertSame(['Folk Evening', $second, '20:00', 'Green Hall', 'Shelbyville'],
                [$state['name'], $state['date'], $state['start'], $state['venue'], $state['city']]);
            // What the first flyer wrote and the second does not say is gone with the first.
            $this->assertSame(['', ''], [$state['end'], $state['postal']]);
            $this->assertSame(['8:00 PM', ''], [$state['startBox'], $state['endBox']]);
            $this->assertNotSame('', $state['dateBox']);
            $this->assertSame('IL', $state['state']);
            $this->assertTrue($state['filled']);

            // Undo is the button beside "Form filled", and it takes back what the second one wrote.
            $browser->script('document.querySelector(".gs-flyer-text .gs-ok .gs-link").click();');
            $browser->pause(200);
            $state = $browser->script($read)[0];
            $this->assertSame(['', '', '', '', ''], [$state['name'], $state['date'], $state['start'], $state['venue'], $state['city']]);
            $this->assertSame(['', ''], [$state['startBox'], $state['dateBox']]);
            $this->assertSame('IL', $state['state']);
            $this->assertFalse($state['filled']);
        });
    }

    public function test_a_new_visitor_submits_and_sees_what_was_sent(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            $this->fillEvent($browser);

            // Started, not yet refused: what is left, said plainly.
            $this->assertStringStartsWith('Still needed: Email', $this->barText($browser));

            $this->fillAccount($browser);
            $this->assertSame('Ready to send to Springfield Live.', $this->barText($browser));

            $this->pressSubmit($browser);
            $browser->waitFor('#submission-success-heading', 20);

            $this->assertSame('Submitted - pending review', trim($browser->text('#submission-success-heading')));
            // The card of what was sent rises in after the tick; wait for it to be there to read.
            $browser->waitUntil('getComputedStyle(document.querySelector("[data-gs-sent]")).opacity === "1"', 5);
            // The code step keeps a copy of this card out of sight; this is the one on the page.
            $this->assertSame('Jazz Night', trim($browser->text('[data-gs-sent] .gs-ticket-name')));
            $this->assertStringContainsString('7:15 PM', $browser->text('[data-gs-sent]'));
            $this->assertStringContainsString('The Blue Room, Springfield', $browser->text('[data-gs-sent]'));

            $event = Event::where('name', 'Jazz Night')->firstOrFail();
            // 7:15 in the evening in New York, to the minute.
            $this->assertSame('19:15', \Carbon\Carbon::parse($event->starts_at, 'UTC')->setTimezone('America/New_York')->format('H:i'));
            $pivot = $event->roles()->where('roles.id', $this->curator->id)->firstOrFail()->pivot;
            $this->assertNull($pivot->is_accepted, 'a schedule that reviews holds the event');
            $this->assertNotNull(User::where('email', 'sam.rivera@gmail.com')->first());

            // "Submit another" keeps the place and the person, and clears the event.
            $browser->script('Array.from(document.querySelectorAll("button")).find(function (b) { return b.innerText.trim() === "Submit another event"; }).click();');
            $browser->pause(500);
            $state = $browser->script('var a = window.__submitApp; return { name: a.event.name, venue: a.event.venue_name, city: a.event.venue_city, postingAs: a.postingAsName, followLine: document.getElementById("event-submit-app").innerText.indexOf("will see your name and email") > -1 };')[0];
            $this->assertSame('', $state['name']);
            $this->assertSame('The Blue Room', $state['venue']);
            $this->assertSame('Springfield', $state['city']);
            $this->assertSame('Sam Rivera', $state['postingAs']);
            $this->assertFalse($state['followLine'], 'they follow the schedule now, so the line is not repeated');
        });
    }

    public function test_the_emailed_code_is_its_own_step_in_the_sign_up_boxes(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, withCode: true);
            $this->fillEvent($browser, 'Code Night');
            $this->fillAccount($browser, 'code.person@gmail.com');

            $this->pressSubmit($browser);
            $browser->waitUntil('window.__submitApp.step === "code"', 15);

            $browser->assertSee('Check your email')->assertSee('code.person@gmail.com');
            $this->assertSame('Code Night', trim($browser->text('.gs-step .gs-ticket-name')));
            $this->assertSame(0, Event::where('name', 'Code Night')->count(), 'nothing is sent until the code is in');

            $browser->script('document.getElementById("verification_code").focus();');
            $browser->keys('#verification_code', '4', '8', '2')->pause(200);
            $state = $browser->script('
                var slots = Array.from(document.querySelectorAll("#code-boxes [data-code-slot]"));
                return {
                    digits: slots.map(function (s) { return s.querySelector("[data-digit]").textContent; }).join("|"),
                    active: slots.findIndex(function (s) { return s.classList.contains("is-active"); }),
                };
            ')[0];
            $this->assertSame('4|8|2|||', $state['digits']);
            $this->assertSame(3, $state['active'], 'the next box to fill carries the ring');

            // The sixth digit is the Submit press.
            $browser->keys('#verification_code', '9', '3', '1');
            $browser->waitFor('#submission-success-heading', 20);
            $this->assertSame(1, Event::where('name', 'Code Night')->count());
        });
    }

    public function test_a_reload_keeps_the_event_the_person_and_the_answers_but_not_the_password(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            $this->fillEvent($browser, 'Kept Night');
            $browser->type('#submit_custom_field_cf1', '+1 555 0100');
            $this->fillAccount($browser, 'kept.person@gmail.com');
            $browser->pause(900);

            // What the page keeps is the event (for a week) and who is filling it in (for this tab).
            // The password is in neither.
            $stored = $browser->script('return JSON.stringify(Object.assign({}, localStorage)) + JSON.stringify(Object.assign({}, sessionStorage));')[0];
            $this->assertStringContainsString('kept.person@gmail.com', $stored);
            $this->assertStringNotContainsString('correct-horse', $stored);

            $browser->refresh()->waitUntil('window.__submitApp !== undefined && !! window.__submitApp.pickers.date', 15)->pause(600);

            $state = $browser->script('var a = window.__submitApp; return { name: a.event.name, start: a.event.event_start_time, answer: a.customFieldValues.cf1, email: a.userEmail, person: a.userName, password: a.userPassword };')[0];
            $this->assertSame('Kept Night', $state['name']);
            $this->assertSame('19:15', $state['start']);
            $this->assertSame('+1 555 0100', $state['answer']);
            $this->assertSame('kept.person@gmail.com', $state['email']);
            $this->assertSame('Sam Rivera', $state['person']);
            $this->assertSame('', $state['password']);
            $browser->assertSee('Your unfinished event was restored.');
        });
    }

    public function test_a_wrong_password_lands_on_the_password_field(): void
    {
        $member = User::factory()->create(['email' => 'returning.person@gmail.com', 'password' => bcrypt('their-password'), 'email_verified_at' => now()]);

        $this->browse(function (Browser $browser) use ($member) {
            $this->open($browser);
            $this->fillEvent($browser, 'Returning Night');

            // The address is known, so the page asks for its password instead of a new account.
            $browser->type('#account_email', $member->email)->keys('#account_email', '{tab}');
            $browser->waitFor('#login_password', 10)->assertSee('You already have an account');
            $browser->type('#login_password', 'not-their-password');

            $this->pressSubmit($browser);
            $browser->waitFor('#err_account_password', 15);

            $this->assertSame('Incorrect password. Please try again.', trim($browser->text('#err_account_password')));
            $this->assertSame('Check: Password', $this->barText($browser));
            $this->assertSame('login_password', $browser->script('return document.activeElement.id;')[0]);
            $this->assertSame(0, Event::where('name', 'Returning Night')->count());
        });
    }
}
