<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventPoll;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\SetupGuide;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The event form as someone uses it: what and when, then where, on the first tab; tickets on a tab
 * of their own; the rest behind tabs that say what they hold; and one bar that says what Save will
 * do.
 *
 * tests/Feature/EventFormSectionsTest.php holds the markup. This runs it: almost everything here
 * is decided by the page's script (which tile is on, what a summary reads, which row is open), and
 * none of that is reachable from PHPUnit.
 */
class EventFormJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $owner;

    private Role $talent;

    private Role $venue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->talent = $this->makeRole($this->owner, 'talent', 'journeytalent', ['name' => 'Journey Quartet']);
        $this->venue = $this->makeRole($this->owner, 'venue', 'journeyvenue', ['name' => 'Blue Note', 'address1' => '131 W 3rd St', 'city' => 'New York']);
    }

    private function makeRole(User $user, string $type, string $subdomain, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = ucfirst($subdomain);
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    private function makeEvent(array $attrs = []): Event
    {
        $event = new Event;
        $event->user_id = $this->owner->id;
        $event->creator_role_id = $this->talent->id;
        $event->name = 'Jazz Night';
        $event->slug = 'jazz-night';
        $event->starts_at = Carbon::now()->addDays(10)->setTime(18, 0)->format('Y-m-d H:i:s');
        $event->duration = 2;
        foreach ($attrs as $key => $value) {
            $event->{$key} = $value;
        }
        $event->save();
        $event->roles()->attach($this->talent->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function openNew(Browser $browser): void
    {
        $this->leave($browser);
        $browser->loginAs($this->owner)
            ->visit('/journeytalent/add-event?visit='.uniqid())
            ->waitFor('#event_name', 15)
            ->waitUntil('window.vueApp !== undefined', 15);
    }

    private function openExisting(Browser $browser, Event $event, string $fragment = ''): void
    {
        $this->leave($browser);
        $browser->loginAs($this->owner)
            ->visit('/journeytalent/edit-event/'.UrlUtils::encodeId($event->id).'?visit='.uniqid().$fragment)
            ->waitFor('#edit-form', 15)
            ->waitUntil('window.vueApp !== undefined', 15);
    }

    /**
     * Every test here gets the same event id, so the same address, from a browser the last test
     * left on that address with a changed form. Two things follow, and both produced a page that
     * was the PREVIOUS test's: a visit that differs only by its fragment is not a page load at
     * all, and leaving a changed form asks "leave site?". The query string makes each visit a
     * load; this lets it leave.
     */
    private function leave(Browser $browser): void
    {
        $browser->script('window._skipUnsavedWarning = true;');
    }

    /** A name, a date and a start time, typed the way the fields take them. */
    private function fillBasics(Browser $browser, string $name): void
    {
        $browser->script('
            var set = function (el, value) { el.value = value; el.dispatchEvent(new Event("input", { bubbles: true })); };
            set(document.getElementById("event_name"), '.json_encode($name).');
            document.getElementById("event_date")._flatpickr.setDate("'.now()->addDays(5)->format('Y-m-d').'", true);
            set(document.getElementById("start_time"), "8:00 PM");
            document.getElementById("start_time").dispatchEvent(new Event("change", { bubbles: true }));
            document.getElementById("start_time").dispatchEvent(new Event("blur", { bubbles: true }));
        ');
        $browser->pause(300);
    }

    private function save(Browser $browser): void
    {
        $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
        $browser->waitUntil('! window.location.pathname.includes("add-event") && ! window.location.pathname.includes("edit-event")', 25);
    }

    /**
     * A real click, on an element brought to the middle of the window first: the save bar sits
     * over the bottom of the page, and a row down there is under it until the page is scrolled.
     */
    private function press(Browser $browser, string $selector): void
    {
        $browser->script('document.querySelector('.json_encode($selector).').scrollIntoView({ block: "center" });');
        $browser->pause(150)->click($selector)->pause(200);
    }

    /** What the save bar's status line reads. */
    private function barText(Browser $browser): string
    {
        return trim(preg_replace('/\s+/', ' ', $browser->script('return document.querySelector(".event-save-status").innerText;')[0]));
    }

    /** The Tickets tab, opened the way the save bar offers it. */
    private function openTickets(Browser $browser): void
    {
        $browser->script('window.showEventSection("section-tickets");');
        $browser->waitUntil('document.getElementById("section-tickets").style.display === "block"', 10);
    }

    private function ticketEvent(array $attrs = []): Event
    {
        $event = $this->makeEvent(array_merge(['tickets_enabled' => true, 'ticket_currency_code' => 'USD', 'payment_method' => 'cash'], $attrs));
        foreach ([['General', 25, 100], ['VIP', 60, 20]] as [$type, $price, $quantity]) {
            $ticket = new Ticket;
            $ticket->event_id = $event->id;
            $ticket->type = $type;
            $ticket->price = $price;
            $ticket->quantity = $quantity;
            $ticket->save();
        }

        return $event->fresh();
    }

    public function test_free_registration_is_one_press_on_the_tickets_tab(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openNew($browser);
            $this->fillBasics($browser, 'Open Mic');

            $state = $browser->script('return {
                choice: window.vueApp.ticketChoice,
                onEventTab: document.querySelectorAll("#section-details .event-tile, #section-details [name=rsvp_limit]").length,
            };')[0];
            $this->assertNull($state['choice'], 'sanity check: a schedule with no events starts with no choice made');
            $this->assertSame(0, $state['onEventTab'], 'the Event tab asks nothing about tickets');

            // The bar says how the event will be saved, and nothing else: it used to offer "Add
            // location" and "Add tickets" as well. The way to tickets is their tab, which says
            // "No tickets" under its name.
            $this->assertSame('Visibility: Public', preg_replace('/\s+/', ' ', $this->barText($browser)));
            $this->assertSame('No tickets', $browser->script('return document.querySelector("a[data-section=section-tickets] .section-nav-summary").innerText.trim();')[0]);
            $browser->click('a[data-section="section-tickets"]');
            $browser->waitUntil('document.getElementById("section-tickets").style.display === "block"', 10);

            $browser->click('#ticket_choice_rsvp')->pause(300);

            $state = $browser->script('return {
                pressed: document.getElementById("ticket_choice_rsvp").getAttribute("aria-pressed"),
                mode: window.vueApp.ticketMode,
                posted: [document.querySelector("input[name=rsvp_enabled]").value, document.querySelector("input[name=tickets_enabled]").value],
                summary: document.querySelector("a[data-section=section-tickets] .section-nav-summary").innerText.trim(),
                dot: getComputedStyle(document.querySelector("a[data-section=section-tickets] .section-nav-dot")).display !== "none",
                focused: document.activeElement && document.activeElement.id,
                moreRow: document.querySelector(".event-subrow[data-tab=options]").offsetParent !== null,
                optionsOpen: window.vueApp.activeTicketTab,
            };')[0];

            $this->assertSame('true', $state['pressed']);
            $this->assertSame('rsvp', $state['mode']);
            $this->assertSame(['1', '0'], $state['posted']);
            $this->assertSame('Registration', $state['summary']);
            $this->assertTrue($state['dot'], 'the Tickets tab shows it has an unsaved change');
            $this->assertSame('rsvp_limit', $state['focused'], 'the limit is the first thing asked, and has the caret');
            $this->assertTrue($state['moreRow'], 'the rest is one row away');
            $this->assertSame('tickets', $state['optionsOpen'], 'and closed until asked for');

            // Pressing the tab you are already on leaves its rows alone. It used to press the
            // first one, which opened Payment.
            $browser->click('a[data-section="section-tickets"]')->pause(300);
            $this->assertSame('tickets', $browser->script('return window.vueApp.activeTicketTab;')[0]);

            // A tile that is on does nothing when pressed again: switching off is "Not needed".
            $browser->type('#rsvp_limit', '40')->click('#ticket_choice_rsvp')->pause(200);
            $this->assertSame('rsvp', $browser->script('return window.vueApp.ticketChoice;')[0]);

            $this->save($browser);
        });

        $event = Event::where('name', 'Open Mic')->firstOrFail();
        $this->assertTrue((bool) $event->rsvp_enabled);
        $this->assertFalse((bool) $event->tickets_enabled);
        $this->assertSame(40, (int) $event->rsvp_limit);
    }

    public function test_selling_tickets_is_a_line_per_type_with_the_rest_in_rows(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openNew($browser);
            $this->fillBasics($browser, 'Paid Night');
            $this->openTickets($browser);

            $browser->click('#ticket_choice_tickets')
                ->waitFor('#section-tickets [name="tickets[0][price]"]', 5)
                ->pause(200);
            $this->assertSame('tickets[0][price]', $browser->script('return document.activeElement.getAttribute("name");')[0], 'the price has the caret');

            $browser->type('#section-tickets [name="tickets[0][type]"]', 'Door')
                ->type('#section-tickets [name="tickets[0][price]"]', '12')
                ->type('#section-tickets [name="tickets[0][quantity]"]', '80')
                ->pause(300);

            $state = $browser->script('
                var row = function (tab) { return document.querySelector(".event-subrow[data-tab=" + tab + "] .event-row-summary"); };
                var description = document.querySelector("[name=\'tickets[0][description]\']");
                return {
                    summary: document.querySelector("a[data-section=section-tickets] .section-nav-summary").innerText.trim(),
                    strip: document.querySelectorAll("#section-tickets .ticket-tab.text-center").length,
                    rows: ["payment", "options", "promo_codes", "add_ons"].map(function (tab) { return row(tab) !== null && row(tab).offsetParent !== null; }),
                    payment: row("payment").innerText.trim(),
                    paymentWarns: row("payment").classList.contains("is-warn"),
                    descriptionShown: description.closest(".mt-4").offsetParent !== null,
                    sameLine: document.querySelector("[name=\'tickets[0][price]\']").getBoundingClientRect().top === document.querySelector("[name=\'tickets[0][type]\']").getBoundingClientRect().top,
                };
            ')[0];

            $this->assertSame('Door', $state['summary']);
            $this->assertSame(0, $state['strip'], 'no second row of tabs');
            $this->assertSame([true, true, true, true], $state['rows']);
            $this->assertSame('Connect Stripe to get paid', $state['payment'], 'a price with no way to be paid says so without being opened');
            $this->assertTrue($state['paymentWarns']);
            $this->assertFalse($state['descriptionShown'], 'an empty description stays folded');
            $this->assertTrue($state['sameLine'], 'price and type share a line');

            // A row opens in place, and opening another closes it.
            $this->press($browser, '.event-subrow[data-tab=options]');
            $this->assertTrue($browser->script('return document.querySelector("[data-ticket-pane=options]").offsetParent !== null;')[0]);
            $this->press($browser, '.event-subrow[data-tab=payment]');
            $open = $browser->script('return [document.querySelector("[data-ticket-pane=options]").offsetParent !== null, document.querySelector("[data-ticket-pane=payment]").offsetParent !== null];')[0];
            $this->assertSame([false, true], $open);

            // "Not needed" is the off switch, and the type typed above is still there when the
            // choice comes back on.
            $this->press($browser, '#ticket_choice_none');
            $this->assertNull($browser->script('return window.vueApp.ticketChoice;')[0]);
            $this->press($browser, '#ticket_choice_tickets');
            $this->assertSame('Door', $browser->script('return document.querySelector("[name=\'tickets[0][type]\']").value;')[0]);

            $this->save($browser);
        });

        $event = Event::where('name', 'Paid Night')->firstOrFail();
        $this->assertTrue((bool) $event->tickets_enabled);
        $ticket = Ticket::where('event_id', $event->id)->firstOrFail();
        $this->assertSame('Door', $ticket->type);
        $this->assertEquals(12, (float) $ticket->price);
        $this->assertEquals(80, (int) $ticket->quantity);
    }

    /**
     * A promo code row added and then abandoned, in a choice that is switched off before saving:
     * its empty required field used to refuse the save, with the field nowhere on screen.
     */
    public function test_a_promo_row_left_behind_does_not_refuse_a_registration_save(): void
    {
        $event = $this->ticketEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            $browser->script('window.vueApp.addPromoCode();');
            $browser->pause(300);
            $this->assertFalse($browser->script('return document.getElementById("edit-form").checkValidity();')[0], 'sanity check: while tickets are on, the empty code is a required field');

            $browser->click('#ticket_choice_rsvp')->pause(300);
            $this->assertTrue($browser->script('return document.getElementById("edit-form").checkValidity();')[0], 'with tickets off it is not asked for');

            $this->save($browser);
        });

        $saved = Event::findOrFail($event->id);
        $this->assertTrue((bool) $saved->rsvp_enabled);
        $this->assertFalse((bool) $saved->tickets_enabled);
    }

    public function test_an_empty_promo_code_opens_its_row_and_keeps_the_page_unsaved(): void
    {
        $event = $this->ticketEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);

            // On the Event tab, with every row of the Tickets tab closed.
            $browser->script('
                window.vueApp.addPromoCode();
                window.vueApp.isDirty = true;
                window._skipUnsavedWarning = true;
            ');
            $browser->pause(300);
            $this->assertSame('tickets', $browser->script('return window.vueApp.activeTicketTab;')[0], 'sanity check: no row is open');

            $browser->script('document.getElementById("edit-form").requestSubmit();');
            $browser->pause(700);

            $state = $browser->script('return {
                onForm: window.location.pathname.includes("edit-event"),
                tab: document.getElementById("section-tickets").style.display,
                row: window.vueApp.activeTicketTab,
                fieldShown: document.querySelector("[name=\'promo_codes[0][code]\']").offsetParent !== null,
                focused: document.activeElement.getAttribute("name"),
                dirty: window.vueApp.isDirty,
            };')[0];

            $this->assertTrue($state['onForm']);
            $this->assertSame('block', $state['tab'], 'the Tickets tab is opened');
            $this->assertSame('promo_codes', $state['row'], 'and the row the field is in');
            $this->assertTrue($state['fieldShown']);
            $this->assertSame('promo_codes[0][code]', $state['focused']);
            $this->assertTrue($state['dirty'], 'a save the browser refused leaves the page unsaved');
        });

        $this->assertSame(0, PromoCode::where('event_id', $event->id)->count());
    }

    public function test_a_link_elsewhere_stays_put_while_it_is_retyped_and_comes_back_after_off(): void
    {
        $event = $this->makeEvent(['registration_url' => 'https://tickets.example.org/jazz']);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            $this->assertSame('external', $browser->script('return window.vueApp.ticketChoice;')[0], 'sanity check: a saved link means that choice is on');

            // Clearing the field to retype it must not take the field away.
            $browser->script('
                var field = document.getElementById("registration_url");
                field.value = "";
                field.dispatchEvent(new Event("input", { bubbles: true }));
            ');
            $browser->pause(300);
            $state = $browser->script('return [window.vueApp.ticketChoice, document.getElementById("registration_url").offsetParent !== null];')[0];
            $this->assertSame(['external', true], $state);

            // Off clears what guests would be shown; on again brings back what was there.
            $browser->type('#registration_url', 'https://tickets.example.org/new')->pause(200);
            $browser->click('#ticket_choice_none')->pause(300);
            $off = $browser->script('return [window.vueApp.ticketChoice, window.vueApp.event.registration_url];')[0];
            $this->assertSame([null, ''], $off);

            $browser->click('#ticket_choice_external')->pause(300);
            $this->assertSame('https://tickets.example.org/new', $browser->script('return document.getElementById("registration_url").value;')[0]);
        });

        $this->assertSame('https://tickets.example.org/jazz', Event::findOrFail($event->id)->registration_url, 'nothing was saved');
    }

    /**
     * Saving with tickets off removes the event's ticket types. With people already signed up that
     * is not something a slip of the finger should do.
     */
    public function test_switching_tickets_off_under_people_who_signed_up_asks_first(): void
    {
        $event = $this->ticketEvent();
        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = 'journeytalent';
        $sale->name = 'Buyer Name';
        // Not example.com: that is a test address, and test addresses are not counted as sign-ups.
        $sale->email = 'jazzfan@gmail.com';
        $sale->event_date = Carbon::parse($event->starts_at)->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 25;
        $sale->secret = str_repeat('a', 32);
        $sale->save();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            $this->assertGreaterThan(0, $browser->script('return window.vueApp.registrantCount;')[0], 'sanity check: the form knows somebody signed up');

            // Asked, and answered no: nothing changes.
            $browser->script('window._asked = 0; window.confirm = function () { window._asked++; return false; };');
            $this->press($browser, '#ticket_choice_none');
            $this->press($browser, '#ticket_choice_rsvp');
            $state = $browser->script('return [window._asked, window.vueApp.ticketChoice];')[0];
            $this->assertSame([2, 'tickets'], $state);

            // Answered yes: it is off, and the bar says what Save will now do.
            $browser->script('window.confirm = function () { return true; };');
            $browser->click('#ticket_choice_none')->pause(300);
            $this->assertNull($browser->script('return window.vueApp.ticketChoice;')[0]);
            $this->assertSame("Saving removes this event's ticket types.", $this->barText($browser));

            // Back on: nothing to warn about.
            $browser->click('#ticket_choice_tickets')->pause(300);
            $this->assertStringNotContainsString('removes', $this->barText($browser));
        });

        $this->assertSame(2, Ticket::where('event_id', $event->id)->where('is_deleted', false)->count(), 'nothing was saved');
    }

    public function test_a_saved_venue_is_picked_from_the_list_and_another_is_found_by_email(): void
    {
        // A venue on the platform that this account has nothing to do with yet.
        $stranger = User::factory()->create(['email_verified_at' => now()]);
        $this->makeRole($stranger, 'venue', 'findmehall', ['name' => 'Find Me Hall', 'email' => 'findme@gmail.com', 'address1' => '9 Lookup Lane', 'city' => 'Boston']);

        $this->browse(function (Browser $browser) {
            $this->openNew($browser);
            $this->fillBasics($browser, 'Two Venues');

            // The list of saved venues is where it always was.
            $browser->waitFor('#selected_venue', 5)->select('#selected_venue')->pause(300);
            $this->assertStringContainsString('Blue Note, 131 W 3rd St, New York', $browser->script('return document.querySelector("#event-location .event-picked").innerText;')[0]);

            // Change, then find a different venue by its email.
            $browser->script('
                Array.prototype.find.call(document.querySelectorAll("#event-location .event-picked button"), function (b) { return b.textContent.trim() === "Change"; }).click();
            ');
            $browser->waitFor('#selected_venue', 5);
            $browser->script('
                Array.prototype.find.call(document.querySelectorAll("#event-location button.event-link"), function (b) { return b.textContent.trim() === "New Venue"; }).click();
            ');
            $browser->waitFor('#venue_name', 5);
            $browser->script('
                Array.prototype.find.call(document.querySelectorAll("#event-location button.event-link"), function (b) { return b.textContent.indexOf("email or phone") !== -1; }).click();
            ');
            $browser->waitFor('#venue_email', 5)
                ->type('#venue_email', 'findme@gmail.com');
            $browser->script('document.getElementById("venue_email").dispatchEvent(new Event("blur"));');
            $browser->waitUntil('window.vueApp.venueSearchResults && window.vueApp.venueSearchResults.length === 1', 10);

            $browser->script('
                Array.prototype.find.call(document.querySelectorAll("#event-location button"), function (b) { return b.textContent.trim() === "Select"; }).click();
            ');
            $browser->waitUntil('window.vueApp.selectedVenue && window.vueApp.selectedVenue.name === "Find Me Hall"', 5)->pause(200);
            $this->assertStringContainsString('Find Me Hall, 9 Lookup Lane, Boston', $browser->script('return document.querySelector("#event-location .event-picked").innerText;')[0]);

            $this->save($browser);
        });

        $event = Event::where('name', 'Two Venues')->firstOrFail();
        $this->assertSame(['findmehall'], $event->roles()->where('roles.type', 'venue')->pluck('subdomain')->all());
    }

    public function test_an_event_that_exists_opens_on_its_fields_and_says_what_a_change_will_do(): void
    {
        $event = $this->makeEvent();
        $event->roles()->attach($this->venue->id, ['is_accepted' => true]);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            // Long enough for the form to finish setting itself up: it arms its change tracking
            // after that, and anything still writing to its lists then would read as an edit.
            $browser->waitFor('#event_name', 10)->pause(1500);

            $page = $browser->script('return {
                title: document.querySelector("h2[v-pre], .event-eyebrow + div h2").innerText.trim(),
                eyebrow: document.querySelector(".event-eyebrow").innerText.trim(),
                badge: document.getElementById("event-saved-state").innerText.trim(),
                name: document.getElementById("event_name").value,
                place: document.querySelector("#event-location .event-picked").innerText,
                sections: [document.getElementById("event-basics").offsetParent !== null, document.getElementById("event-location").offsetParent !== null],
                idle: document.querySelector(".event-bar-save").classList.contains("is-idle"),
                link: document.querySelector(".event-url-strip").innerText,
            };')[0];

            $this->assertSame('Jazz Night', $page['title'], 'the page is titled with the event');
            $this->assertSame('EDIT EVENT', strtoupper($page['eyebrow']));
            $this->assertSame('Public', $page['badge']);
            $this->assertSame('Jazz Night', $page['name'], 'and its fields are on screen, with no card to click through');
            $this->assertStringContainsString('Blue Note, 131 W 3rd St, New York', $page['place']);
            $this->assertSame([true, true], $page['sections']);
            $this->assertTrue($page['idle'], 'with nothing changed, Save does not ask to be pressed');
            $this->assertSame('No unsaved changes', $this->barText($browser));
            $this->assertFalse($browser->script('return window.vueApp.isDirty;')[0], 'opening an event is not an edit');
            $this->assertStringContainsString('journeytalent', $page['link']);

            // The badge is the way to where visibility is changed.
            $browser->click('#event-saved-state');
            $browser->waitUntil('document.getElementById("section-listing").style.display === "block"', 10);

            // Choosing Draft: the tab says so, the bar says what Save will do, and the badge keeps
            // saying what is saved.
            $browser->script('document.querySelector("#section-listing input[type=radio][value=draft]").click();');
            $browser->pause(300);

            $state = $browser->script('return {
                summary: document.querySelector("a[data-section=section-listing] .section-nav-summary").innerText.trim(),
                dot: getComputedStyle(document.querySelector("a[data-section=section-listing] .section-nav-dot")).display !== "none",
                save: document.querySelector(".event-bar-save").innerText.trim(),
                idle: document.querySelector(".event-bar-save").classList.contains("is-idle"),
                badge: document.getElementById("event-saved-state").innerText.trim(),
            };')[0];

            $this->assertStringStartsWith('Draft', $state['summary']);
            $this->assertTrue($state['dot']);
            $this->assertSame('Save draft', $state['save']);
            $this->assertFalse($state['idle']);
            $this->assertSame('Public', $state['badge']);
            $this->assertSame('Saving hides this event from the public.', $this->barText($browser));

            // Back to Public: nothing about visibility is left to warn about, the edit is still unsaved.
            $browser->script('document.querySelector("#section-listing input[type=radio][value=public]").click();');
            $browser->pause(300);
            $this->assertSame('Unsaved: Listing', $this->barText($browser));
        });

        $this->assertFalse((bool) Event::findOrFail($event->id)->is_draft, 'nothing was saved');
    }

    public function test_an_event_saved_without_a_place_opens_with_the_venue_fields_showing(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $browser->waitFor('#event_name', 10)->pause(1500);

            $state = $browser->script('return {
                inPerson: window.vueApp.isInPerson,
                picker: document.getElementById("selected_venue") !== null && document.getElementById("selected_venue").offsetParent !== null,
                dirty: window.vueApp.isDirty,
            };')[0];

            $this->assertTrue($state['inPerson'], 'two unpressed pills said nothing about a missing place');
            $this->assertTrue($state['picker'], 'the saved venues are offered');
            $this->assertFalse($state['dirty'], 'and showing them is not an edit');
        });
    }

    public function test_an_empty_name_brings_the_event_tab_back(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-listing');
            $browser->waitUntil('document.getElementById("section-listing").style.display === "block"', 10);

            $this->assertFalse($browser->script('return document.getElementById("event_name").offsetParent !== null;')[0], 'sanity check: the name field is on another tab');

            $browser->script('
                var name = document.getElementById("event_name");
                name.value = "";
                name.dispatchEvent(new Event("input", { bubbles: true }));
                window._skipUnsavedWarning = true;
                document.getElementById("edit-form").requestSubmit();
            ');
            $browser->pause(600);

            $state = $browser->script('return {
                onForm: window.location.pathname.includes("edit-event"),
                tab: document.getElementById("section-details").style.display,
                nameShown: document.getElementById("event_name").offsetParent !== null,
            };')[0];

            $this->assertTrue($state['onForm'], 'the save was refused');
            $this->assertSame('block', $state['tab']);
            $this->assertTrue($state['nameShown'], 'the field that is wrong is on screen');
        });

        $this->assertSame('Jazz Night', Event::findOrFail($event->id)->name);
    }

    public function test_cancel_asks_before_it_discards_and_leaves_at_once_when_there_is_nothing_to_lose(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);

            $browser->script('
                var name = document.getElementById("event_name");
                name.value = "Half a thought";
                name.dispatchEvent(new Event("input", { bubbles: true }));
            ');
            $browser->pause(200);
            $browser->script('document.querySelector(".event-save-actions .event-bar-text").click();');
            $browser->pause(300);

            $this->assertSame('Discard unsaved changes?', $this->barText($browser));
            $this->assertTrue($browser->script('return window.location.pathname.includes("edit-event");')[0], 'Cancel did not leave');

            // Keep editing: the bar goes back to saying what is unsaved.
            $browser->script('
                Array.prototype.find.call(document.querySelectorAll(".event-save-actions button"), function (b) { return b.offsetParent !== null && b.textContent.trim() === "Keep editing"; }).click();
            ');
            $browser->pause(300);
            $this->assertSame('Unsaved: Event', $this->barText($browser));

            // With nothing changed, Cancel leaves without asking. It goes back to where the form
            // was opened from, so this arrives the way a person does: by following a link from
            // the schedule (a visit() carries no referrer, and Cancel would have nowhere to go).
            $this->leave($browser);
            $browser->visit('/journeytalent/schedule')->waitForLocation('/journeytalent/schedule', 15);
            $browser->script('window.location.href = '.json_encode('/journeytalent/edit-event/'.UrlUtils::encodeId($event->id)).';');
            $browser->waitFor('#edit-form', 15)->waitUntil('window.vueApp !== undefined', 15);
            $browser->script('document.querySelector(".event-save-actions .event-bar-text").click();');
            $browser->waitForLocation('/journeytalent/schedule', 15);
        });

        $this->assertSame('Jazz Night', Event::findOrFail($event->id)->name);
    }

    public function test_a_pasted_image_becomes_the_flyer(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openNew($browser);

            $browser->script('
                var bytes = Uint8Array.from(atob("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=="), function (c) { return c.charCodeAt(0); });
                var transfer = new DataTransfer();
                transfer.items.add(new File([bytes], "pasted.png", { type: "image/png" }));
                document.dispatchEvent(new ClipboardEvent("paste", { clipboardData: transfer, bubbles: true, cancelable: true }));
            ');
            $browser->waitUntil('document.getElementById("image_preview").style.display !== "none"', 10);

            $state = $browser->script('return {
                files: document.getElementById("flyer_image").files.length,
                name: document.getElementById("flyer_image").files[0].name,
                tile: document.getElementById("flyer-choose-btn").offsetParent !== null,
                dirty: window.vueApp.sectionDirty["section-details"] === true,
            };')[0];

            $this->assertSame(1, $state['files']);
            $this->assertSame('pasted.png', $state['name']);
            $this->assertFalse($state['tile'], 'the picked image stands where the tile was');
            $this->assertTrue($state['dirty']);

            // A pasted image is not a flyer while another tab is the one on screen.
            $browser->script('document.getElementById("clear-flyer-preview-btn").click(); window.showEventSection("section-tickets");');
            $browser->pause(200);
            $browser->script('
                var transfer = new DataTransfer();
                transfer.items.add(new File([new Uint8Array([1, 2, 3])], "elsewhere.png", { type: "image/png" }));
                document.dispatchEvent(new ClipboardEvent("paste", { clipboardData: transfer, bubbles: true, cancelable: true }));
            ');
            $browser->pause(300);
            $this->assertSame(0, $browser->script('return document.getElementById("flyer_image").files.length;')[0]);
        });
    }

    public function test_enter_in_the_name_of_a_new_event_goes_on_to_the_date(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openNew($browser);

            $browser->click('#event_name')->keys('#event_name', '{enter}')->pause(400);

            $state = $browser->script('return {
                onForm: window.location.pathname.includes("add-event"),
                calendarOpen: document.getElementById("event_date")._flatpickr.isOpen,
                dateError: window.vueApp.dateTimeError,
            };')[0];

            $this->assertTrue($state['onForm']);
            $this->assertTrue($state['calendarOpen'], 'the date picker is open, ready for the next answer');
            $this->assertSame('', $state['dateError'], 'and nobody was told off for not having picked a date yet');
        });
    }

    /**
     * A participant typed into the add form but not added used to be left out of the save
     * without a word, and changing tab could throw away what was typed.
     */
    public function test_a_participant_typed_but_not_added_is_saved_and_survives_a_tab_change(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-participants');
            $browser->waitUntil('document.getElementById("section-participants").style.display === "block"', 10);

            $browser->script('window.vueApp.showAddMemberForm();');
            $browser->waitFor('#member_name', 5)
                ->type('#member_name', 'Bleeding Gums Murphy')
                ->type('#member_email', 'murphy@gmail.com')
                ->pause(300);

            // Another tab and back: nothing blocked the move, and nothing was thrown away.
            $browser->script('window.showEventSection("section-agenda");');
            $browser->pause(300);
            $state = $browser->script('return {
                moved: document.getElementById("section-agenda").style.display,
                name: window.vueApp.memberName,
                phoneWired: !! window.vueApp.phoneInputInstances["member_phone_input"],
            };')[0];
            $this->assertSame('block', $state['moved']);
            $this->assertSame('Bleeding Gums Murphy', $state['name']);
            $this->assertTrue($state['phoneWired'], 'the phone field is wired, whichever way the form came to be open');

            // Saved with Ctrl+S, never having pressed Add.
            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitUntil('! window.location.pathname.includes("edit-event")', 20);
        });

        $names = Event::findOrFail($event->id)->roles()->where('roles.type', 'talent')->pluck('roles.name')->all();
        $this->assertContains('Bleeding Gums Murphy', $names);
    }

    public function test_a_participant_with_no_name_is_shown_and_not_dropped(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-participants');
            $browser->script('window.vueApp.showAddMemberForm();');
            $browser->waitFor('#member_email', 5)->type('#member_email', 'noname@gmail.com')->pause(200);

            $browser->script('window.showEventSection("section-details"); window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->pause(700);

            $state = $browser->script('return {
                onForm: window.location.pathname.includes("edit-event"),
                tab: document.getElementById("section-participants").style.display,
                focused: document.activeElement && document.activeElement.id,
                email: window.vueApp.memberEmail,
            };')[0];
            $this->assertTrue($state['onForm'], 'the save waited');
            $this->assertSame('block', $state['tab']);
            $this->assertSame('member_name', $state['focused']);
            $this->assertSame('noname@gmail.com', $state['email']);
        });
    }

    public function test_agenda_parts_are_a_line_each_and_show_times_says_what_it_removes(): void
    {
        $event = $this->makeEvent();
        foreach ([['Doors', '19:00', '20:00', ''], ['First set', '20:00', '21:00', 'Standards.']] as $i => [$name, $start, $end, $description]) {
            EventPart::create(['event_id' => $event->id, 'name' => $name, 'start_time' => $start, 'end_time' => $end, 'description' => $description, 'sort_order' => $i]);
        }

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-agenda');
            $browser->waitFor('.event-agenda-row', 10)->pause(1200);

            $state = $browser->script('
                var rows = document.querySelectorAll(".event-agenda-row");
                var top = function (el) { return Math.round(el.getBoundingClientRect().top); };
                var first = rows[0];
                return {
                    rows: rows.length,
                    oneLine: top(first.querySelector(".event-agenda-name")) === top(first.querySelector(".event-agenda-times input")),
                    emptyDescriptionFolded: first.querySelector("textarea").offsetParent === null && first.querySelector(".event-agenda-sub .event-link") !== null,
                    writtenDescriptionShown: rows[1].querySelector(".event-agenda-sub > div").offsetParent !== null,
                    dirty: window.vueApp.isDirty,
                };
            ')[0];
            $this->assertSame(2, $state['rows']);
            $this->assertTrue($state['oneLine'], 'start, end and name share a line');
            $this->assertTrue($state['emptyDescriptionFolded']);
            $this->assertTrue($state['writtenDescriptionShown']);
            $this->assertFalse($state['dirty'], 'opening the agenda is not an edit');

            // Enter in a part's name is not Save.
            $browser->click('.event-agenda-row .event-agenda-name')->keys('.event-agenda-row .event-agenda-name', '{enter}')->pause(400);
            $this->assertTrue($browser->script('return window.location.pathname.includes("edit-event");')[0]);

            // Show times off: the fields go, the times are kept until Save, and the bar says what Save will do.
            $browser->script('document.querySelector("#section-agenda .event-tab-title button[role=switch]").click();');
            $browser->pause(300);
            $state = $browser->script('return {
                fields: document.querySelectorAll(".event-agenda-times").length,
                kept: window.vueApp.eventParts[0].start_time,
                posted: document.querySelector("input[name=\'event_parts[0][start_time]\']").value,
                dot: getComputedStyle(document.querySelector("a[data-section=section-agenda] .section-nav-dot")).display !== "none",
            };')[0];
            $this->assertSame(0, $state['fields']);
            $this->assertSame('19:00', $state['kept'], 'still there if the switch goes back on');
            $this->assertSame('', $state['posted'], 'and sent as empty, said, not left out');
            $this->assertTrue($state['dot'], 'a switch marks its tab unsaved');
            $this->assertSame('Saving removes the times from this agenda.', $this->barText($browser));

            $browser->script('document.querySelector("#section-agenda .event-tab-title button[role=switch]").click();');
            $browser->pause(300);
            $this->assertStringNotContainsString('removes', $this->barText($browser));
            $this->assertSame('19:00', $browser->script('return document.querySelector("input[name=\'event_parts[0][start_time]\']").value;')[0]);
        });

        $this->assertSame('19:00', EventPart::where('event_id', $event->id)->orderBy('sort_order')->first()->start_time, 'nothing was saved');
    }

    public function test_engagement_is_rows_that_say_their_setting_and_an_unfinished_poll_is_shown(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-engagement');
            $browser->waitUntil('document.getElementById("section-engagement").style.display === "block"', 10)->pause(800);

            $state = $browser->script('return {
                strip: document.querySelectorAll("#section-engagement nav .engagement-tab").length,
                rows: Array.prototype.map.call(document.querySelectorAll("#section-engagement .event-subrow.engagement-tab"), function (r) { return r.getAttribute("data-tab"); }),
                open: window.vueApp.activeEngagementTab,
                fan: document.querySelector(".event-subrow[data-tab=fan_content] .event-row-summary").innerText.trim(),
            };')[0];
            $this->assertSame(0, $state['strip'], 'no second row of tabs');
            $this->assertSame(['polls', 'fan_content', 'feedback'], $state['rows']);
            $this->assertSame('', $state['open'], 'every row closed until one is asked for');
            // What is in force, then that it is the schedule's doing: a new schedule takes all three.
            $this->assertSame("Comments, Photos, Videos \u{00B7} Same as schedule", $state['fan']);

            // The row says the setting as it is chosen.
            $this->press($browser, '.event-subrow[data-tab=fan_content]');
            $browser->script('document.querySelector("input[name=fan_comments_enabled][value=\'0\']").click();');
            $browser->pause(300);
            $this->assertSame('Photos, Videos', $browser->script('return document.querySelector(".event-subrow[data-tab=fan_content] .event-row-summary").innerText.trim();')[0]);

            // Pressing the tab again leaves the open row open.
            $browser->click('a[data-section="section-engagement"]')->pause(300);
            $this->assertSame('fan_content', $browser->script('return window.vueApp.activeEngagementTab;')[0]);

            // A poll with a question and one option: Save would drop it. It is shown instead.
            $browser->script('
                var v = window.vueApp;
                v.polls.push({hash: null, question: "Which song first?", options: ["Only one", ""], is_active: true, allow_user_options: false, require_option_approval: false, pending_options: [], votes_count: 0, results: []});
                v.markTabDirty("section-engagement");
                window.showEventSection("section-details");
                window._skipUnsavedWarning = true;
            ');
            $browser->pause(300);
            $browser->script('document.getElementById("edit-form").requestSubmit();');
            $browser->pause(700);
            $state = $browser->script('return {
                onForm: window.location.pathname.includes("edit-event"),
                tab: document.getElementById("section-engagement").style.display,
                row: window.vueApp.activeEngagementTab,
                message: window.vueApp.pollError,
            };')[0];
            $this->assertTrue($state['onForm']);
            $this->assertSame('block', $state['tab']);
            $this->assertSame('polls', $state['row']);
            $this->assertSame('A poll needs a question and at least two options.', $state['message']);
        });

        $this->assertSame(0, EventPoll::where('event_id', $event->id)->count());
        $this->assertNull(Event::findOrFail($event->id)->fan_comments_enabled, 'nothing was saved');
    }

    public function test_leaving_an_events_own_sponsors_is_announced_and_a_photo_change_is_unsaved(): void
    {
        $event = $this->makeEvent(['sponsor_mode' => 'custom', 'sponsor_logos' => json_encode([['name' => 'Duff', 'logo' => 'demo_flyer_jazz.jpg', 'url' => null, 'tier' => 'gold']])]);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-event-settings');
            $browser->waitUntil('document.getElementById("section-event-settings").style.display === "block"', 10)->pause(800);

            $state = $browser->script('return {
                tiles: document.querySelectorAll("#section-event-settings .event-tile").length,
                on: document.querySelector("#section-event-settings .event-tile.is-on .event-tile-title").innerText.trim(),
                rows: document.querySelectorAll("#section-event-settings .sponsor-item").length,
            };')[0];
            $this->assertSame(3, $state['tiles']);
            $this->assertStringContainsString("This event's own", $state['on']);
            $this->assertSame(1, $state['rows']);

            // "Same as schedule": saving would delete this event's list and its logo. Said first.
            $browser->script('document.querySelectorAll("#section-event-settings .event-tile")[0].click();');
            $browser->pause(300);
            $this->assertSame("Saving removes this event's sponsors.", $this->barText($browser));
            $this->assertTrue($browser->script('return getComputedStyle(document.querySelector("a[data-section=section-event-settings] .section-nav-dot")).display !== "none";')[0]);

            $browser->script('document.querySelectorAll("#section-event-settings .event-tile")[2].click();');
            $browser->pause(300);
            $this->assertStringNotContainsString('removes', $this->barText($browser));
        });

        $this->assertSame('custom', Event::findOrFail($event->id)->sponsor_mode, 'nothing was saved');

        // A photo added, removed or moved goes through no field of the form: the gallery's own
        // change hook marks the tab, where the bar used to go on saying "No unsaved changes".
        $plain = $this->makeEvent();
        $this->browse(function (Browser $browser) use ($plain) {
            $this->openExisting($browser, $plain);
            $browser->waitFor('#event_name', 10)->pause(1500);
            $this->assertSame('No unsaved changes', $this->barText($browser));

            // A real photo, handed to the gallery the way a drop hands it over.
            $browser->script('
                var bytes = atob("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==");
                var data = new Uint8Array(bytes.length);
                for (var i = 0; i < bytes.length; i++) { data[i] = bytes.charCodeAt(i); }
                window.vueApp.galleryStore.addFiles([new File([data], "one.png", { type: "image/png" })]);
            ');
            $browser->waitUntil('window.vueApp.sectionDirty["section-gallery"] === true', 10);
            $this->assertSame('Unsaved: Gallery', $this->barText($browser));
            $browser->script('window._skipUnsavedWarning = true;');
        });
    }

    public function test_also_list_on_holds_only_ticks_that_decide_something(): void
    {
        $curator = $this->makeRole($this->owner, 'curator', 'journeycurator', ['name' => 'Springfield Live']);
        $event = $this->makeEvent();
        $event->roles()->attach($this->venue->id, ['is_accepted' => true]);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event, '#section-listing');
            $browser->waitUntil('document.getElementById("section-listing").style.display === "block"', 10)->pause(500);

            $state = $browser->script('return {
                boxes: Array.prototype.map.call(document.querySelectorAll("#section-listing input[name=\'curators[]\']"), function (box) { return document.querySelector("label[for=" + box.id + "]").innerText.trim(); }),
                hint: document.querySelector("#section-listing .event-hint").innerText,
                links: document.querySelectorAll("#section-listing #copy-event-url-btn").length,
            };')[0];
            $this->assertContains('Springfield Live', $state['boxes']);
            $this->assertNotContains('Blue Note', $state['boxes'], 'the venue is decided on the Event tab');
            $this->assertStringContainsString('Blue Note', $state['hint'], 'and named, so it is not a mystery where it went');
            $this->assertSame(0, $state['links'], 'the link is under the title, once');

            // Tick the curator and save: the venue, which has no box, is still the venue.
            $browser->script('document.querySelector("#section-listing input[name=\'curators[]\']").click();');
            $browser->pause(200);
            $this->save($browser);
        });

        $attached = Event::findOrFail($event->id)->roles()->pluck('roles.id')->all();
        $this->assertContains($this->venue->id, $attached);
        $this->assertContains($curator->id, $attached);
    }

    /**
     * The setup guide follows a new organizer across the app. On a first event its card stands
     * beside the form; afterwards it is a ring in the bottom corner, which is where Save lives.
     */
    public function test_the_setup_guide_keeps_its_place_beside_the_form_and_clear_of_save(): void
    {
        $newcomer = User::factory()->create(['email_verified_at' => now()]);
        $first = $this->makeRole($newcomer, 'talent', 'journeyfirst', ['name' => 'First Timers']);
        SetupGuide::start($newcomer, $first);

        $this->browse(function (Browser $browser) use ($newcomer, $first) {
            $this->leave($browser);
            $browser->resize(1440, 900)->loginAs($newcomer)
                ->visit('/journeyfirst/add-event?visit='.uniqid())
                ->waitFor('#event_name', 15)
                ->waitUntil('window.vueApp !== undefined', 15)
                ->waitFor('.sg-dock', 15)
                ->pause(600);

            $dock = $browser->script('
                var dock = document.querySelector(".sg-dock").getBoundingClientRect();
                var form = document.getElementById("event-basics").getBoundingClientRect();
                return { left: dock.left, right: dock.right, width: dock.width, formRight: form.right, viewport: window.innerWidth };
            ')[0];
            $this->assertGreaterThan($dock['formRight'], $dock['left'], 'the card stands to the right of the fields, not over them');
            $this->assertLessThanOrEqual($dock['viewport'], $dock['right'], 'and inside the window');
            $this->assertGreaterThan(150, $dock['width']);

            // Once a first event exists the guide is a ring in the corner.
            $event = new Event;
            $event->user_id = $newcomer->id;
            $event->creator_role_id = $first->id;
            $event->name = 'Opening Night';
            $event->slug = 'opening-night';
            $event->starts_at = Carbon::now()->addDays(10)->setTime(18, 0)->format('Y-m-d H:i:s');
            $event->duration = 2;
            $event->save();
            $event->roles()->attach($first->id, ['is_accepted' => true]);

            $this->leave($browser);
            $browser->visit('/journeyfirst/add-event?visit='.uniqid())
                ->waitFor('#event_name', 15)
                ->waitUntil('window.vueApp !== undefined', 15)
                ->waitFor('.sg-corner', 15)
                ->pause(600);

            $overlap = $browser->script('
                var ring = document.querySelector(".sg-corner").getBoundingClientRect();
                var save = document.querySelector(".event-bar-save").getBoundingClientRect();
                var bar = document.querySelector(".event-save-bar").getBoundingClientRect();
                var apart = function (a, b) { return a.right <= b.left || b.right <= a.left || a.bottom <= b.top || b.bottom <= a.top; };
                return { shown: ring.width > 0, clearOfSave: apart(ring, save), clearOfBar: apart(ring, bar) };
            ')[0];
            $this->assertTrue($overlap['shown'], 'sanity check: the ring is on screen');
            $this->assertTrue($overlap['clearOfSave'], 'the ring does not sit on Save');
            $this->assertTrue($overlap['clearOfBar'], 'nor anywhere on the bar');
        });
    }

    /**
     * A save the server refuses gives the Tickets tab back as it was being edited. It used to
     * come back with "Sell tickets" still chosen and the ticket types as they are stored, so the
     * next save put the old ones back. The bar names the tab to check until something on it
     * changes, and Cancel asks, because nothing typed has been saved.
     */
    public function test_a_refused_save_keeps_the_ticket_types_and_the_bar_lets_go_of_the_tab(): void
    {
        $event = $this->ticketEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            // Set the way the page's fields take a value: typed over, not appended to (a real
            // keystroke on a field Vue has filled lands after what is there). The other type is
            // left as it is stored, whichever of the two it is.
            $other = $browser->script('return window.vueApp.tickets[1].type;')[0];
            foreach (['tickets[0][type]' => 'Balcony', 'tickets[0][price]' => '31'] as $name => $value) {
                $browser->script('var el = document.querySelector('.json_encode('#section-tickets [name="'.$name.'"]').'); el.value = '.json_encode($value).'; el.dispatchEvent(new Event("input", { bubbles: true }));');
            }
            $browser->pause(200);

            // Something only the server checks: a coupon code longer than it stores. It rides in
            // a hidden field here, so the browser has nothing to say about it.
            $browser->script('
                window.__beforeRefusal = true;
                window.vueApp.event.coupon_code = "x".repeat(300);
            ');
            $browser->pause(200);
            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitUntil('window.__beforeRefusal === undefined && window.vueApp !== undefined && window.location.pathname.includes("edit-event")', 25);
            $browser->pause(800);

            $state = $browser->script('return {
                choice: window.vueApp.ticketChoice,
                types: window.vueApp.tickets.map(function (ticket) { return ticket.type; }),
                price: parseFloat(window.vueApp.tickets[0].price),
                unsaved: window.vueApp.isDirty,
            };')[0];
            $this->assertSame('tickets', $state['choice']);
            $this->assertSame(['Balcony', $other], $state['types'], 'what was typed, not what is stored');
            $this->assertEquals(31, $state['price']);
            $this->assertTrue($state['unsaved'], 'nothing typed has been saved');
            $this->assertStringStartsWith('Check:', $this->barText($browser));
            $this->assertStringContainsString('Tickets', $this->barText($browser));

            // A change on the tab it named: the bar stops asking for it to be checked.
            $this->openTickets($browser);
            $browser->type('#section-tickets [name="tickets[0][quantity]"]', '90')->pause(300);
            $this->assertStringStartsNotWith('Check:', $this->barText($browser));
            $this->assertStringContainsString('Tickets', $this->barText($browser), 'still unsaved, and says where');

            // Cancel asks first.
            $browser->script('document.querySelector(".event-save-actions .event-bar-text").click();');
            $browser->pause(300);
            $this->assertTrue($browser->script('return window.vueApp.confirmingDiscard;')[0]);
            $this->assertTrue($browser->script('return window.location.pathname.includes("edit-event");')[0]);
        });

        $this->assertSame(['General', 'VIP'], Ticket::where('event_id', $event->id)->orderBy('id')->pluck('type')->all(), 'nothing was saved');
    }

    /**
     * The fields of a choice that is not the one chosen are switched off: a link left half-typed
     * under "Tickets elsewhere" used to refuse the save from a field nobody could see. What is
     * sent in its place is the link the event was saved with, and "Not needed" still clears it.
     */
    public function test_a_field_of_a_choice_that_is_not_chosen_cannot_refuse_the_save(): void
    {
        $event = $this->makeEvent(['registration_url' => 'https://tickets.example.org/jazz']);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            // Typed over what is there, not after it: appended to the saved link it would still
            // be an address a browser accepts.
            $browser->script('var el = document.getElementById("registration_url"); el.value = "not a link"; el.dispatchEvent(new Event("input", { bubbles: true }));');
            $browser->pause(200);
            $this->assertFalse($browser->script('return document.getElementById("edit-form").checkValidity();')[0], 'sanity check: the browser refuses it while it is the choice');

            $browser->click('#ticket_choice_rsvp')->pause(300);
            $state = $browser->script('
                var form = document.getElementById("edit-form");
                var standIn = form.querySelector("input[type=hidden][name=registration_url]");
                return {
                    fieldOff: document.getElementById("registration_url").matches(":disabled"),
                    sent: standIn ? standIn.value : null,
                    limitLive: ! document.getElementById("rsvp_limit").matches(":disabled"),
                    limitStandIns: form.querySelectorAll("input[type=hidden][name=rsvp_limit]").length,
                    noneIsCurrent: document.getElementById("ticket_choice_none").classList.contains("is-current"),
                };
            ')[0];
            $this->assertTrue($state['fieldOff']);
            $this->assertSame('https://tickets.example.org/jazz', $state['sent'], 'the saved link, not the half-typed one');
            $this->assertTrue($state['limitLive']);
            $this->assertSame(0, $state['limitStandIns'], 'one of the two is live at a time');
            $this->assertFalse($state['noneIsCurrent']);

            $this->save($browser);
        });

        $saved = Event::findOrFail($event->id);
        $this->assertTrue((bool) $saved->rsvp_enabled, 'the save went through');
        $this->assertSame('https://tickets.example.org/jazz', $saved->registration_url);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            $browser->click('#ticket_choice_none')->pause(300);
            $this->assertTrue($browser->script('return document.getElementById("ticket_choice_none").classList.contains("is-current");')[0], '"Not needed" reads as the choice in force');
            $this->save($browser);
        });

        $saved = Event::findOrFail($event->id);
        $this->assertFalse((bool) $saved->rsvp_enabled);
        $this->assertEmpty($saved->registration_url, 'off means off: no link is left for guests to be sent to');
    }

    /** Saving with tickets off deletes the event's add-ons as well as its ticket types. Said first. */
    public function test_switching_tickets_off_says_the_add_ons_go_too(): void
    {
        $event = $this->ticketEvent();
        $addon = new Ticket;
        $addon->event_id = $event->id;
        $addon->type = 'Parking';
        $addon->price = 10;
        $addon->is_addon = true;
        $addon->save();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);

            $this->assertSame('Parking', $browser->script('return document.querySelector(".event-subrow[data-tab=add_ons] .event-row-summary").innerText.trim();')[0]);

            $browser->click('#ticket_choice_none')->pause(300);
            $bar = $this->barText($browser);
            $this->assertStringContainsString("Saving removes this event's ticket types.", $bar);
            $this->assertStringContainsString("Saving removes this event's add-ons.", $bar);

            $browser->click('#ticket_choice_tickets')->pause(300);
            $this->assertStringNotContainsString('removes', $this->barText($browser));
        });

        $this->assertSame(3, Ticket::where('event_id', $event->id)->count(), 'nothing was saved');
    }

    /** Ctrl+S held down, or pressed twice, used to send the form twice. */
    public function test_a_second_save_while_the_first_is_on_its_way_is_ignored(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);

            $stopped = $browser->script('
                window.vueApp.isSaving = true;
                var again = new Event("submit", { cancelable: true, bubbles: true });
                document.getElementById("edit-form").dispatchEvent(again);
                return again.defaultPrevented;
            ')[0];
            $this->assertTrue($stopped);
            $this->assertTrue($browser->script('return window.location.pathname.includes("edit-event");')[0]);
        });
    }

    /**
     * The first save of a public event is what puts it in front of people, and the button says so.
     * The bar keeps its one quiet line.
     */
    public function test_a_new_public_event_is_published_by_a_button_that_says_so(): void
    {
        // Not this account's first event: that one has a form of its own, whose button reads
        // "Create event" whatever the visibility.
        $this->makeEvent(['name' => 'An earlier night', 'slug' => 'an-earlier-night']);

        $this->browse(function (Browser $browser) {
            $this->openNew($browser);
            $browser->pause(600);

            $label = 'return document.querySelector(".event-bar-save").innerText.trim();';
            $this->assertSame('Publish', $browser->script($label)[0]);
            $this->assertSame('Visibility: Public', $this->barText($browser));
            $this->assertTrue($browser->script('return document.getElementById("ticket_choice_none").classList.contains("is-current");')[0], 'with no tickets, "Not needed" is the choice shown');

            $browser->script('window.vueApp.visibility = "draft";');
            $browser->pause(300);
            $this->assertSame('Save draft', $browser->script($label)[0]);
        });

        $event = $this->makeEvent();
        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $browser->pause(400);
            $this->assertSame('Save', $browser->script('return document.querySelector(".event-bar-save").innerText.trim();')[0], 'an event that exists is saved, not published again');
        });
    }

    /** Help opens the guide at whatever is on screen: the tab, then the row opened inside it. */
    public function test_help_follows_the_tab_and_the_row(): void
    {
        $event = $this->ticketEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $browser->pause(400);

            $help = 'var link = document.querySelector(".js-help-link"); return link.getAttribute("href").replace(/^https?:\/\/[^\/]+/, "");';

            $browser->script('document.querySelector("a.section-nav-link[data-section=section-engagement]").click();');
            $browser->pause(300);
            $this->assertSame('/docs/creating-events#engagement', $browser->script($help)[0]);

            $browser->script('document.querySelector(".event-subrow.engagement-tab[data-tab=fan_content]").click();');
            $browser->pause(300);
            $this->assertSame('/docs/creating-events#fan-content', $browser->script($help)[0]);

            $browser->script('document.querySelector("a.section-nav-link[data-section=section-tickets]").click();');
            $browser->pause(300);
            $this->assertSame('/docs/tickets#general', $browser->script($help)[0]);

            $browser->script('document.querySelector(".event-subrow.ticket-tab[data-tab=promo_codes]").click();');
            $browser->pause(300);
            $this->assertSame('/docs/tickets#promo-codes', $browser->script($help)[0]);

            $browser->script('document.getElementById("ticket_choice_external").click();');
            $browser->pause(300);
            $this->assertSame('/docs/tickets#external', $browser->script($help)[0]);

            $browser->script('document.getElementById("ticket_choice_none").click();');
            $browser->pause(300);
            $this->assertSame('/docs/tickets#general', $browser->script($help)[0], '"Not needed" has no page of its own');
        });
    }

    /** A name longer than the server stores: set past the field's own limit, so only the server objects. */
    private function refuseOverTheName(Browser $browser): void
    {
        $browser->script('
            window.__beforeRefusal = true;
            var name = document.getElementById("event_name");
            name.value = "x".repeat(300);
            name.dispatchEvent(new Event("input", { bubbles: true }));
        ');
        $browser->pause(200);
        $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
        $browser->waitUntil('window.__beforeRefusal === undefined && window.vueApp !== undefined && window.location.pathname.includes("edit-event")', 25);
        $browser->pause(800);
    }

    private function nameItAgain(Browser $browser, string $name): void
    {
        $browser->script('var name = document.getElementById("event_name"); name.value = '.json_encode($name).'; name.dispatchEvent(new Event("input", { bubbles: true }));');
        $browser->pause(200);
    }

    /**
     * The choice came back from a refused save and what was typed under it did not: a limit typed,
     * the save refused over another field, and the next save made registration unlimited.
     */
    public function test_a_limit_typed_before_a_refused_save_is_the_limit_that_is_saved(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);
            $this->press($browser, '#ticket_choice_rsvp');
            $browser->script('var el = document.getElementById("rsvp_limit"); el.value = "50"; el.dispatchEvent(new Event("input", { bubbles: true })); window.vueApp.event.ask_phone = true;');
            $browser->pause(200);

            $this->refuseOverTheName($browser);

            $state = $browser->script('return {
                choice: window.vueApp.ticketChoice,
                limit: window.vueApp.event.rsvp_limit,
                field: document.getElementById("rsvp_limit").value,
                fieldOn: ! document.getElementById("rsvp_limit").disabled,
                phone: window.vueApp.event.ask_phone,
            };')[0];
            $this->assertSame('rsvp', $state['choice']);
            $this->assertEquals(50, $state['limit'], 'the limit came back with the choice');
            $this->assertSame('50', $state['field'], 'and is in its field');
            $this->assertTrue($state['fieldOn']);
            $this->assertTrue($state['phone'], 'a switch on the Options row came back as it was left');

            $this->nameItAgain($browser, 'Jazz Night');
            $this->save($browser);
        });

        $saved = Event::find($event->id);
        $this->assertTrue((bool) $saved->rsvp_enabled);
        $this->assertSame(50, (int) $saved->rsvp_limit, 'the limit that was typed is the limit that is saved');
        $this->assertTrue((bool) $saved->ask_phone);
    }

    /**
     * Promo codes and add-ons are not sent while tickets are off. A refused save made that way
     * brought both back empty, and "Sell tickets" pressed again and saved deleted every one.
     */
    public function test_switching_tickets_back_on_after_a_refused_save_keeps_the_codes_and_add_ons(): void
    {
        $event = $this->ticketEvent();
        $addon = new Ticket;
        $addon->event_id = $event->id;
        $addon->type = 'T-shirt';
        $addon->price = 15;
        $addon->is_addon = true;
        $addon->save();
        $promo = \App\Models\PromoCode::create(['event_id' => $event->id, 'code' => 'EARLY', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);
            $this->press($browser, '#ticket_choice_none');
            $this->assertNull($browser->script('return window.vueApp.ticketChoice;')[0]);

            $this->refuseOverTheName($browser);

            $state = $browser->script('return {
                choice: window.vueApp.ticketChoice,
                codes: window.vueApp.promoCodes.map(function (code) { return code.code; }),
                addons: window.vueApp.addons.map(function (addon) { return addon.type; }),
                types: window.vueApp.tickets.map(function (ticket) { return ticket.type; }),
            };')[0];
            $this->assertNull($state['choice'], '"Not needed" is still the choice');
            $this->assertSame(['EARLY'], $state['codes'], 'the codes were not in the post, so they are what is stored');
            $this->assertSame(['T-shirt'], $state['addons']);
            // In no particular order: the two were made in the same second.
            $this->assertEqualsCanonicalizing(['General', 'VIP'], $state['types']);

            $this->openTickets($browser);
            $this->press($browser, '#ticket_choice_tickets');
            $this->nameItAgain($browser, 'Jazz Night');
            $this->save($browser);
        });

        $this->assertNotNull(\App\Models\PromoCode::find($promo->id), 'the promo code is still there');
        $this->assertFalse((bool) Ticket::find($addon->id)->is_deleted, 'and so is the add-on');
        $this->assertSame(['General', 'VIP'], Ticket::where('event_id', $event->id)->where('is_addon', false)->where('is_deleted', false)->orderBy('id')->pluck('type')->all());
    }

    /** A link typed under "Tickets elsewhere" on an event that had none came back as "Not needed". */
    public function test_a_ticket_link_typed_before_a_refused_save_is_still_on_screen(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);
            $this->openTickets($browser);
            $this->press($browser, '#ticket_choice_external');
            $browser->script('var el = document.querySelector("#section-tickets fieldset.event-fieldset [name=\\"registration_url\\"]"); el.value = "https://tickets.example.org/jazz"; el.dispatchEvent(new Event("input", { bubbles: true }));');
            $browser->pause(200);

            $this->refuseOverTheName($browser);
            $this->openTickets($browser);

            $state = $browser->script('var el = document.querySelector("#section-tickets fieldset.event-fieldset [name=\\"registration_url\\"]"); return {
                choice: window.vueApp.ticketChoice,
                field: el.value,
                shown: el.offsetParent !== null && ! el.disabled,
            };')[0];
            $this->assertSame('external', $state['choice']);
            $this->assertSame('https://tickets.example.org/jazz', $state['field']);
            $this->assertTrue($state['shown']);

            $this->nameItAgain($browser, 'Jazz Night');
            $this->save($browser);
        });

        $this->assertSame('https://tickets.example.org/jazz', Event::find($event->id)->registration_url);
    }

    /**
     * The Save button before and after the page's script: one label at a time, and a page the
     * browser brings back from its back-forward cache is not still "saving".
     */
    public function test_the_save_button_reads_one_thing_and_a_restored_page_can_save(): void
    {
        $event = $this->makeEvent();

        $this->browse(function (Browser $browser) use ($event) {
            $this->openExisting($browser, $event);

            $labels = $browser->script('return Array.prototype.filter.call(document.querySelectorAll(".event-bar-save span"), function (span) { return span.offsetParent !== null; }).map(function (span) { return span.textContent.trim(); });')[0];
            $this->assertSame([__('messages.save')], $labels, 'one label once the page has mounted');

            $browser->script('window.vueApp.isSaving = true; window._skipUnsavedWarning = true;');
            $browser->pause(100);
            $this->assertTrue((bool) $browser->script('return document.querySelector(".event-bar-save").disabled;')[0], 'sanity check: saving disables Save');

            // An ordinary page show (not a restore) changes nothing.
            $browser->script('window.dispatchEvent(new PageTransitionEvent("pageshow", { persisted: false }));');
            $browser->pause(100);
            $this->assertTrue((bool) $browser->script('return window.vueApp.isSaving;')[0]);

            $browser->script('window.dispatchEvent(new PageTransitionEvent("pageshow", { persisted: true }));');
            $browser->pause(200);
            $this->assertFalse((bool) $browser->script('return window.vueApp.isSaving;')[0], 'a restored page is not still saving');
            $this->assertFalse((bool) $browser->script('return document.querySelector(".event-bar-save").disabled;')[0]);
            $this->assertFalse((bool) $browser->script('return window._skipUnsavedWarning;')[0], 'and warns about unsaved changes again');
        });
    }
}
