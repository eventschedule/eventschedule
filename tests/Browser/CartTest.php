<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

/**
 * The multi-event cart panel in the guest layout.
 *
 * Its own file rather than a third journey in TicketTest: the panel is shared page furniture, and
 * every assertion here is about what it asks the buyer rather than about a ticket.
 */
class CartTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * The cart panel must not ask for a name and email the buyer has already given.
     *
     * Reported as "the name/email are shown twice": the ticket form asks, and the cart panel that
     * opens on top of it asked again, empty, even for a signed-in buyer whose account answers both.
     * addToCart() now sends the buyer along with the leg, and a signed-in visitor is seeded from
     * their account, so the panel asks only when it genuinely does not know.
     */
    public function test_the_cart_does_not_ask_again_for_a_name_it_already_has(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            $this->openCartWithTickets($browser);

            // Signed in: the account answers both questions, so the panel posts them silently.
            $browser->waitFor('@cart-checkout', 10);
            $browser->assertMissing('#es-cart-name');
            $browser->assertMissing('#es-cart-email');
            $this->assertSame(
                ['Talent', 'test@gmail.com'],
                $this->cartBuyerInputs($browser),
                'the signed-in account must reach checkout without being retyped',
            );
            $this->assertSame(
                ['2'],
                $this->cartPostedQuantities($browser),
                'the quantity picked in the ticket form must travel with the leg',
            );

            // Signed out with nothing remembered, the fields have to come back - hiding them
            // unconditionally would post a blank name and bounce with nothing on screen to fix.
            $browser->script('localStorage.clear();');
            // waitForReload, not a pause: a logout still in flight when the next step navigates
            // re-installs the session cookie it was meant to drop.
            $browser->waitForReload(function (Browser $b) {
                $b->script('
                    var f = document.createElement("form");
                    f.method = "POST"; f.action = "/logout";
                    var t = document.createElement("input");
                    t.type = "hidden"; t.name = "_token";
                    t.value = document.querySelector("meta[name=csrf-token]").content;
                    f.appendChild(t); document.body.appendChild(f); f.submit();
                ');
            }, 30);
            $browser->waitUntil('document.readyState === "complete"', 15);

            $this->openCartWithTickets($browser);
            $browser->waitFor('#es-cart-name', 10)->assertVisible('#es-cart-email');

            // ...and once they have typed them into the ticket form, the panel stops asking again.
            $browser->script('localStorage.clear();');
            $browser->visit('/talent/venue')->waitForText('Buy Tickets', 15)->pause(500);
            $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");
            $browser->pause(1200);
            // Scoped to the ticket form, and it has to be. Dusk's resolveForTyping() has no
            // "#{$field}" step, so a bare field name is an input[name=...] lookup that takes the
            // FIRST match in document order - and event/partials/interest-capture.blade.php now
            // renders its own one-field name="email" form at show-guest.blade.php:1270, above the
            // ticket form at :1309. Unscoped, the address landed in the interest box, the ticket
            // app's email stayed empty, addToCart() sent a buyer with no address, and the panel
            // asked again: the exact regression below, arriving from a page this test never names.
            $browser->within('#ticket-selector', function (Browser $form) {
                $form->type('name', 'Guest Buyer')->type('email', 'guest@example.com');
            })->pause(400);
            $this->pickTwoTicketsAndAddToCart($browser);

            $browser->waitFor('@cart-checkout', 10);
            $browser->assertMissing('#es-cart-name');
            $this->assertSame(
                ['Guest Buyer', 'guest@example.com'],
                $this->cartBuyerInputs($browser),
                'what they typed into the ticket form must travel with the leg',
            );
        });
    }

    private function openCartWithTickets(Browser $browser): void
    {
        $browser->visit('/talent/venue')->waitForText('Buy Tickets', 15);
        $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");
        // #ticket-0 is the quantity select, and the ticket app's own v-for renders it - so one wait
        // proves both that showForm() ran and that Vue mounted. The blind pause(1200) this replaces
        // proved neither, which on a page where the button below is on screen from the start (see
        // the auto-select note) left nothing to tell "not ready yet" from "broken".
        $browser->waitFor('#ticket-0', 10);
        $this->pickTwoTicketsAndAddToCart($browser);
    }

    /**
     * Put two tickets in the cart, and name the link that broke when the panel does not open.
     *
     * TWO, not one: tickets.blade.php auto-selects a quantity of 1 for any event with a single
     * ticket, so the Add to cart button is already on screen before this helper touches anything.
     * "Pick one ticket" therefore could not tell a real selection from the default - the old
     * synthetic `selectedIndex = 1` + `change` dispatch would have kept passing with the whole step
     * deleted - and nothing downstream pinned the quantity that reached the leg.
     */
    private function pickTwoTicketsAndAddToCart(Browser $browser): void
    {
        // waitFor, not assertPresent: it resolves on isDisplayed(), so this is also the assertion
        // that the button is genuinely on screen and usable, which is the half of a WebDriver click
        // worth keeping.
        $browser->select('#ticket-0', '2')->waitFor('@add-to-cart', 10);

        // Records every es-cart-add for the first probe below. The button's own "ADDED TO CART"
        // label would answer the same question, but it clears itself 2500ms after the press.
        $browser->script('window.__esCartAddSeen = 0;
            window.addEventListener("es-cart-add", function () { window.__esCartAddSeen++; });');

        // Pressed through the element's own click(), the way TicketTest submits this same form and
        // the way every other journey here presses a decisive button ("avoids click-targeting
        // issues in headless Chrome", AccountSetupTrait:64). This was a real WebDriver click, the
        // one in the suite left on that path: a dropped click raises nothing at all, so it could
        // only ever arrive as the bare 10-second timeout on the panel that the probes below now
        // break down. A missing button still fails loudly - querySelector returns null and the
        // script throws.
        $browser->script('document.querySelector(\'[dusk="add-to-cart"]\').click();');

        // One probe per link, each naming a different half. The panel lives in a SECOND Vue app
        // (#es-cart-app, mounted by the guest layout) that the ticket form can reach only through a
        // window CustomEvent, so a bare wait on @cart-checkout cannot say which of these failed.
        $browser->waitUntil('window.__esCartAddSeen === 1', 10,
            'the Add to cart press never reached addToCart(), or it returned early');
        $browser->waitUntil('!! (document.querySelector("#es-cart-app") || {}).__vue_app__', 10,
            "the cart panel's own Vue app never mounted on #es-cart-app");
        $browser->waitUntil('JSON.parse(localStorage.getItem("es_cart_talent") || "[]").length === 1', 10,
            'the cart stored no leg, so es-cart-add never crossed between the two Vue apps');
    }

    /** @return array{0: ?string, 1: ?string} the name and email the panel would actually post */
    private function cartBuyerInputs(Browser $browser): array
    {
        $raw = $browser->script('return JSON.stringify([
            (document.querySelector("#es-cart-panel input[type=hidden][name=name]") || {}).value || null,
            (document.querySelector("#es-cart-panel input[type=hidden][name=email]") || {}).value || null,
        ]);');

        return json_decode($raw[0], true);
    }

    /** @return string[] the ticket quantities the panel would actually post for the first leg */
    private function cartPostedQuantities(Browser $browser): array
    {
        $raw = $browser->script('return JSON.stringify(
            Array.from(document.querySelectorAll("#es-cart-panel input[type=hidden]"))
                .filter(function (i) { return i.name.indexOf("legs[0][tickets]") === 0; })
                .map(function (i) { return i.value; })
        );');

        return json_decode($raw[0], true);
    }
}
