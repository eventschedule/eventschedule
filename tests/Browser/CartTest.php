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

            $this->openCartWithOneTicket($browser);

            // Signed in: the account answers both questions, so the panel posts them silently.
            $browser->waitFor('@cart-checkout', 10);
            $browser->assertMissing('#es-cart-name');
            $browser->assertMissing('#es-cart-email');
            $this->assertSame(
                ['Talent', 'test@gmail.com'],
                $this->cartBuyerInputs($browser),
                'the signed-in account must reach checkout without being retyped',
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

            $this->openCartWithOneTicket($browser);
            $browser->waitFor('#es-cart-name', 10)->assertVisible('#es-cart-email');

            // ...and once they have typed them into the ticket form, the panel stops asking again.
            $browser->script('localStorage.clear();');
            $browser->visit('/talent/venue')->waitForText('Buy Tickets', 15)->pause(500);
            $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");
            $browser->pause(1200);
            $browser->type('name', 'Guest Buyer')->type('email', 'guest@example.com')->pause(400);
            $this->pickOneTicketAndAddToCart($browser);

            $browser->waitFor('@cart-checkout', 10);
            $browser->assertMissing('#es-cart-name');
            $this->assertSame(
                ['Guest Buyer', 'guest@example.com'],
                $this->cartBuyerInputs($browser),
                'what they typed into the ticket form must travel with the leg',
            );
        });
    }

    private function openCartWithOneTicket(Browser $browser): void
    {
        $browser->visit('/talent/venue')->waitForText('Buy Tickets', 15)->pause(500);
        $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");
        $browser->pause(1200);
        $this->pickOneTicketAndAddToCart($browser);
    }

    private function pickOneTicketAndAddToCart(Browser $browser): void
    {
        $browser->script('
            document.querySelectorAll("#ticket-selector select").forEach(function (s) {
                if (s.options.length > 1) { s.selectedIndex = 1; s.dispatchEvent(new Event("change")); }
            });
        ');
        $browser->pause(1000);
        $browser->scrollIntoView('@add-to-cart')->click('@add-to-cart')->pause(1500);
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
}
