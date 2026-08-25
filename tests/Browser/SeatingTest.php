<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
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
                'position' => $n, 'x' => $n * 26, 'y' => 0, 'kind' => 'standard',
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
            // assertPathIs does not wait - it reads getCurrentURL() once - and the URL only
            // becomes .../design after POST -> 302 -> GET. waitForReload watches for the new
            // document, which is what the rest of this suite does after a requestSubmit().
            $browser->waitForReload(function (Browser $b) {
                $b->script('document.querySelector(\'#new_seating_plan_name\').form.requestSubmit();');
            }, 20);

            // #seating-loading is v-if="loading" INSIDE the component, so waiting for it to go
            // before Vue has mounted passes instantly and proves nothing. Mount first, load second.
            $browser->assertPathIs('/talent/seating/*/design')
                ->waitFor('#seating-designer', 20)
                ->waitUntilMissing('#seating-loading', 20)
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
            $browser->waitForReload(function (Browser $b) {
                $b->script('document.querySelector(\'#new_seating_plan_name\').form.requestSubmit();');
            }, 20);

            // One level, one section - the least there is to grab hold of. The presets are not
            // offered until the plan has loaded, so clicking one cannot be undone by the fetch -
            // but only if the wait for that load happens after Vue has mounted.
            $browser->assertPathIs('/talent/seating/*/design')
                ->waitFor('#seating-designer', 20)
                ->waitUntilMissing('#seating-loading', 20)
                ->waitFor('button[data-preset="rows"]', 20);
            $browser->script('document.querySelector(\'button[data-preset="rows"]\').click();');
            // The preset only touches client state, so the toolbar saying so is proof the layout
            // was built, where a bare pause is a guess.
            $browser->waitFor('#seating-dirty', 15)
                ->waitFor('#seating-designer svg > g > g > rect', 15)
                ->pause(700);

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
            //
            // clickAndHold($el) presses the element's CENTRE, and that has to be section
            // background rather than a seat, or the seat's own @mousedown.stop wins and the
            // section never moves. It is, by construction: sectionBox() is the seat extent plus
            // 16 units of padding, so a preset with an EVEN number of rows and an even number of
            // seats per row centres in the gap between four seats. `rows` is 8 x 10, spaced 26 x
            // 30, giving a centre of (117, 105) - 13 units clear horizontally, 15 vertically.
            // Change those preset numbers to odd ones and this test fails on "the section did not
            // move", which is the readable failure to fix by aiming at the padding ring instead.
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

            // The hold is a POST, and the hidden inputs are what the checkout will claim. waitFor()
            // is no use here: it requires isDisplayed(), and a type="hidden" input never is.
            $browser->waitUntil('document.querySelectorAll(\'input[name="seat_ids[]"]\').length === 2', 20)
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
                ->assertPathBeginsWith('/talent/seating/box-office/')
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

    /**
     * The box office asks before it takes a seat off a customer, and an armed exchange can be
     * called off.
     *
     * Both were one click from doing real damage: Release went straight through from a bare text
     * link, and once Exchange was armed the NEXT click on any other seat moved the booking, with no
     * cancel and no Escape. Neither is reachable from a Feature test - the confirm is a browser
     * dialog and the exchange is component state.
     */
    public function test_the_box_office_guards_its_destructive_actions(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            [$event] = $this->makeSeated();
            $map = app(SeatingMapService::class)->materialize($event);
            $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->get();

            // A real booking to act on.
            $sale = Sale::create([
                'event_id' => $event->id, 'event_date' => $map->event_date,
                // NOT NULL with no default, and nothing fills it in - Sale's saving hook touches
                // only phone and paid_at, and there is no observer.
                'subdomain' => 'talent',
                'name' => 'Jane Smith', 'email' => 'jane@example.com',
                'status' => 'paid', 'payment_method' => 'box_office', 'secret' => Str::random(32),
            ]);
            $seats->take(2)->each(fn ($s) => $s->update(['status' => 'sold', 'sale_id' => $sale->id]));

            $sold = $seats->first();
            $other = $seats->get(4);

            $browser->visit('/talent/seating/box-office/'.UrlUtils::encodeId($event->id))
                ->assertPathBeginsWith('/talent/seating/box-office/')
                ->waitFor('#seating-box-office', 20)
                ->waitFor('#bo-seat-'.$sold->id, 20)
                ->pause(500);

            // 1. Release asks, and a dismissed dialog changes nothing.
            $browser->script('document.querySelector(\'#bo-seat-'.$sold->id.'\').dispatchEvent(new MouseEvent("click", { bubbles: true }));');
            $browser->pause(400);
            $browser->script('[...document.querySelectorAll("button")].find(b => b.textContent.trim() === '.json_encode(__('messages.seating_release_seat')).').click();');
            $browser->pause(300)->dismissDialog()->pause(1200);

            $this->assertSame('sold', $sold->fresh()->status, 'dismissing the confirm still released the seat');

            // 2. An armed exchange lets go on Escape, and the next seat click is then harmless.
            $browser->script('[...document.querySelectorAll("button")].find(b => b.textContent.trim() === '.json_encode(__('messages.seating_exchange')).').click();');
            // Dispatched on window, where the listener lives, rather than through keys(): Dusk
            // resolves a keys() selector against the page root, so 'body' became 'body body' and
            // threw NoSuchElement - and a <div> is not an interactable sendKeys target either.
            $browser->pause(400);
            $browser->script('window.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape", bubbles: true }))');
            $browser->pause(300);
            $browser->script('document.querySelector(\'#bo-seat-'.$other->id.'\').dispatchEvent(new MouseEvent("click", { bubbles: true }));');
            $browser->pause(1200);

            $this->assertSame($sale->id, (int) $sold->fresh()->sale_id, 'Escape did not disarm the exchange');
            $this->assertSame('available', $other->fresh()->status, 'the booking moved after Escape');

            // 3. Accepting the confirm does release it.
            $browser->script('document.querySelector(\'#bo-seat-'.$sold->id.'\').dispatchEvent(new MouseEvent("click", { bubbles: true }));');
            $browser->pause(400);
            $browser->script('[...document.querySelectorAll("button")].find(b => b.textContent.trim() === '.json_encode(__('messages.seating_release_seat')).').click();');
            $browser->pause(300)->acceptDialog()->pause(1500);

            $this->assertSame('available', $sold->fresh()->status, 'accepting the confirm did not release the seat');
        });
    }

    /**
     * A section holding a sold seat cannot be removed, and says so straight away.
     *
     * The server has always refused - but only at Save, after the room had been restructured. The
     * seat-by-seat path refused immediately, so deleting the section AROUND those seats being the
     * lenient one was exactly backwards. Only reachable on the occurrence editor, because a
     * template seat is never sold.
     */
    public function test_a_section_with_a_sold_seat_cannot_be_removed(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            [$event] = $this->makeSeated();
            $map = app(SeatingMapService::class)->materialize($event);
            $seat = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->firstOrFail();
            $seat->update(['status' => 'sold']);

            $browser->visit('/talent/seating/occurrence/'.UrlUtils::encodeId($event->id).'/design?date='.$map->event_date)
                ->waitUntilMissing('#seating-loading', 20)
                ->waitFor('#seating-designer', 20)
                ->waitFor('#seating-designer svg > g > g', 20)
                ->pause(700);

            // Select the section, which is what reveals its Remove button.
            $browser->script('document.querySelector("#seating-designer svg > g > g > rect").dispatchEvent(new MouseEvent("mousedown", { bubbles: true }));');
            $browser->waitFor('#seating-remove-section', 15)->pause(300);

            $before = $browser->script('return document.querySelectorAll("#seating-designer svg > g > g circle, #seating-designer svg > g > g rect").length')[0];

            $browser->script('document.getElementById("seating-remove-section").click();');
            $browser->waitFor('#seating-error', 10);

            $after = $browser->script('return document.querySelectorAll("#seating-designer svg > g > g circle, #seating-designer svg > g > g rect").length')[0];

            $this->assertSame($before, $after, 'the section was removed despite holding a sold seat');
            $this->assertSame('sold', $seat->fresh()->status);
        });
    }
}
