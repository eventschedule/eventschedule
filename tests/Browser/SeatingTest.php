<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

/**
 * The three allocated-seating screens, in a real browser.
 *
 * Every one of them is a Vue app that a Feature test cannot reach: the designer holds the whole
 * plan client-side until Save, the picker mounts itself into a placeholder rendered by a different
 * Vue runtime, and the box office repaints from a poll. A build error, a failed mount or a broken
 * bundle leaves all three rendering an empty div and returning 200 - which is exactly what the
 * Feature suite would call a pass.
 *
 * Each journey therefore ends on a database assertion rather than on pixels: what matters is that
 * clicking through the screen wrote the row it claims to have written.
 */
class SeatingTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * A plan with one section of six seats, written directly.
     *
     * Building it through the designer is test one's job; the other two need a house to sell, not
     * another pass over the same screen.
     */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground', 'position' => 0]);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
            'color' => '#4E81FA', 'x' => 40, 'y' => 40,
        ]);

        for ($n = 1; $n <= 6; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26, 'y' => 0, 'kind' => 'seat',
            ]);
        }

        return $plan->fresh();
    }

    /**
     * Turn the trait's plain ticketed event into an allocated one.
     *
     * The band on the ticket is what makes it allocated - without it the form renders the ordinary
     * quantity select and the picker never mounts.
     *
     * @return array{0: \App\Models\Event, 1: SeatingPlan}
     */
    private function makeSeated(string $slug = 'talent'): array
    {
        $role = Role::subdomain($slug)->firstOrFail();
        $this->upgradeToEnterprise($slug);
        $plan = $this->makePlan($role->fresh());

        $event = $role->events()->latest('events.id')->firstOrFail();
        $event->seating_plan_id = $plan->id;
        $event->save();

        $ticket = $event->tickets()->firstOrFail();
        $ticket->seating_band = 'Stalls';
        $ticket->save();

        return [$event->fresh(), $plan];
    }

    /**
     * The designer: build a house from a preset and save it.
     *
     * Nothing exists server-side until Save fires one PUT carrying the whole structure, so a plan
     * with sections and seats in the database is proof the client state survived the round trip.
     */
    public function test_the_designer_saves_the_plan_it_drew(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);
            $this->upgradeToEnterprise('talent');

            // Creating a plan redirects straight into the designer.
            $browser->visit('/talent/seating')
                ->waitFor('#new_seating_plan_name', 15)
                ->type('#new_seating_plan_name', 'Main House');
            $browser->script('document.querySelector(\'#new_seating_plan_name\').form.requestSubmit();');

            $browser->waitFor('#seating-designer', 20)
                ->waitFor('button[data-preset="theatre"]', 20);

            // The empty state offers presets rather than a blank canvas. Theatre lays out stalls
            // and a balcony and generates the rows for both.
            $browser->script('document.querySelector(\'button[data-preset="theatre"]\').click();');

            // The preset only touches client state, so the toolbar must say so before saving.
            $browser->waitFor('#seating-dirty', 15)
                ->pause(500);

            $browser->script('document.querySelector(\'#seating-save\').click();');

            // Save clears the dirty flag once the PUT comes back OK - a precise signal, where a
            // fixed pause would pass a save that silently failed.
            $browser->waitUntilMissing('#seating-dirty', 30)
                ->assertMissing('#seating-error');

            $role = Role::subdomain('talent')->firstOrFail();
            $plan = SeatingPlan::where('role_id', $role->id)->firstOrFail();

            $this->assertSame('Main House', $plan->name);
            $this->assertGreaterThan(1, SeatingLevel::where('seating_plan_id', $plan->id)->count(), 'the balcony never saved');
            $this->assertGreaterThan(0, SeatingSection::where('seating_plan_id', $plan->id)->count());
            $this->assertGreaterThan(20, SeatingSeat::where('seating_plan_id', $plan->id)->count(), 'the generated rows never saved');
        });
    }

    /**
     * Dragging a section moves the section, and ONLY the section.
     *
     * The shared viewport pans on `pointerdown` on the <svg>; the draggable elements stop
     * `mousedown`. Those are different events and pointerdown fires first, so for one commit a
     * press on a section started a pan as well and the whole view slid out from under the thing
     * being dragged. Nothing else can catch this: a Feature test cannot reach a drag, and the
     * journey above drives the screen through script() clicks.
     *
     * Reads the two `transform` attributes rather than component state, because a <script setup>
     * SFC exposes nothing on the proxy in a production build.
     */
    public function test_dragging_a_section_does_not_pan_the_canvas(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);
            $this->upgradeToEnterprise('talent');

            $browser->visit('/talent/seating')
                ->waitFor('#new_seating_plan_name', 15)
                ->type('#new_seating_plan_name', 'Drag House');
            $browser->script('document.querySelector(\'#new_seating_plan_name\').form.requestSubmit();');

            // One level, one section - the least there is to grab hold of.
            $browser->waitFor('button[data-preset="rows"]', 20);
            $browser->script('document.querySelector(\'button[data-preset="rows"]\').click();');
            $browser->waitFor('#seating-designer svg > g > g > rect', 15)->pause(700);

            $read = 'return {'
                .' pan: document.querySelector("#seating-designer svg > g").getAttribute("transform"),'
                .' section: document.querySelector("#seating-designer svg > g > g").getAttribute("transform")'
                .'};';

            $before = $browser->script($read)[0];

            $rect = $browser->driver->findElement(
                \Facebook\WebDriver\WebDriverBy::cssSelector('#seating-designer svg > g > g > rect')
            );
            // A real WebDriver drag, so the browser emits the pointer and mouse events itself in
            // the order it really would - which is the whole point.
            $browser->driver->action()->clickAndHold($rect)->moveByOffset(60, 40)->release()->perform();
            $browser->pause(500);

            $after = $browser->script($read)[0];

            $this->assertNotSame($before['section'], $after['section'], 'the section did not move');
            $this->assertSame($before['pan'], $after['pan'], 'dragging a section also panned the canvas');
        });
    }

    /**
     * The picker: a buyer takes two seats and checks out.
     *
     * Choosing a quantity asks for best-available, which holds the seats before the form is even
     * submitted. Checkout then has to convert that hold into a sale - so the seats must come out
     * sold and bound to it, not merely held.
     */
    public function test_a_buyer_picks_seats_and_checks_out(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            [$event] = $this->makeSeated();

            $browser->visit('/talent/venue')
                ->waitForText('Buy Tickets', 15)
                ->pause(500);
            $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");

            // The picker mounts itself into a placeholder the ticket form renders, so waiting on
            // the form is not enough - wait for the picker's own control.
            $browser->waitFor('select[id^="seatqty-"]', 20)
                ->pause(500);

            // Two seats, best available. The change handler picks and holds them.
            $browser->script('
                var sel = document.querySelector(\'select[id^="seatqty-"]\');
                sel.value = "2";
                sel.dispatchEvent(new Event("change", { bubbles: true }));
            ');

            // The hold is a POST, and the hidden inputs are what the checkout will claim.
            $browser->waitFor('input[name="seat_ids[]"]', 20)
                ->pause(500);

            $this->assertSame(2, SeatingSeat::whereNotNull('hold_token')->count(), 'the picker never held the seats');

            $browser->scrollIntoView('#ticket-selector button[type="submit"]');
            $browser->script('document.querySelector(\'#ticket-selector button[type="submit"]\').click()');
            $browser->waitForText('ATTENDEE', 30);

            $sold = SeatingSeat::where('status', 'sold')->get();

            $this->assertCount(2, $sold, 'checkout did not convert the hold into a sale');
            $this->assertNotNull($sold->first()->sale_id);
            $this->assertNotNull($sold->first()->sale_ticket_id);
            $this->assertNull($sold->first()->hold_token, 'a sold seat still carries a cart token');

            // ...and against the right occurrence, not merely against the event.
            $map = $sold->first()->eventSeatingMap;
            $this->assertSame($event->saleEventDateFromStartsAt(), $map->event_date);
        });
    }

    /**
     * The box office: hold a seat back with an internal note.
     *
     * The staff console is a different payload from the guest picker's - it carries the note and
     * the booker - so it gets its own journey rather than riding on the picker's.
     */
    public function test_the_box_office_holds_a_seat_back(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            [$event] = $this->makeSeated();
            $map = app(SeatingMapService::class)->materialize($event);
            $seat = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->firstOrFail();
            $versionBefore = $map->version;

            $browser->visit('/talent/seating/box-office/'.UrlUtils::encodeId($event->id))
                ->waitFor('#seating-box-office', 20)
                ->waitFor('#bo-seat-'.$seat->id, 20)
                ->pause(500);

            // Selecting a seat opens the hold panel.
            $browser->script('document.querySelector(\'#bo-seat-'.$seat->id.'\').dispatchEvent(new MouseEvent("click", { bubbles: true }));');
            $browser->waitFor('#bo-hold-note', 15)
                ->pause(300);

            $browser->script('
                var kind = document.getElementById("bo-hold-kind");
                kind.value = "house";
                kind.dispatchEvent(new Event("change", { bubbles: true }));
                var note = document.getElementById("bo-hold-note");
                note.value = "Held for the producer";
                note.dispatchEvent(new Event("input", { bubbles: true }));
            ');
            $browser->pause(300);
            $browser->script('document.getElementById("bo-block").click();');
            $browser->pause(2500);

            $seat->refresh();

            $this->assertSame('held', $seat->status, 'the console never blocked the seat');
            $this->assertSame('house', $seat->hold_kind);
            $this->assertSame('Held for the producer', $seat->hold_note);
            $this->assertNull($seat->hold_expires_at, 'a staff hold must not expire like a cart');

            // A house seat is off sale, so the map's version has to move or every other open
            // console keeps polling with a cursor that never returns it.
            $this->assertGreaterThan($versionBefore, $map->fresh()->version);
        });
    }
}
