<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

/**
 * What the ticket form does when Checkout is pressed too early.
 *
 * The feature tests can only read the page's script. This presses the button: the form is
 * novalidate and checks in its own order, so a mistake there is a form that either submits what
 * the browser used to stop or stops with nothing said.
 */
class TicketFormJourneyTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_checkout_says_what_is_missing_in_the_page_and_does_not_send_the_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            // A phone: the page's own bar is at the foot of the screen until the form opens.
            $browser->resize(390, 844)->visit('/talent/venue')->waitForText('Buy Tickets', 15);
            $this->assertTrue($this->js($browser, "return document.getElementById('gp-mobile-cta').offsetHeight > 0;"), 'fixture: the phone bar is showing');

            $browser->script("window.dispatchEvent(new CustomEvent('show-event-form'))");
            $browser->waitFor('#ticket-0', 10);
            // ...and gives way to the form's own. It used to stay, offering Buy tickets beside Checkout.
            $browser->waitUntil("document.getElementById('gp-mobile-cta').offsetHeight === 0", 5);

            // Nothing chosen (a lone ticket type starts at one, so take it back to none).
            $this->setQuantities($browser, 0);
            $this->pressCheckout($browser);
            $browser->waitFor('#checkout-problem', 5)
                ->assertSeeIn('#checkout-problem', __('messages.please_select_ticket'))
                ->assertPathIs('/talent/venue');
            $this->assertTrue($this->js($browser, "var p = document.getElementById('checkout-problem').getBoundingClientRect(); return p.top >= 0 && p.bottom <= window.innerHeight;"), 'said where it can be read: in the bar, on screen');

            // Choosing one answers it.
            $this->setQuantities($browser, 1);
            $browser->waitUntilMissing('#checkout-problem', 5);

            // A ticket and no name: the browser's own check is off (novalidate), and the form
            // still stops, on the name, with the name clear of the bar at the foot.
            $browser->script("var n = document.getElementById('name'); n.value = ''; n.dispatchEvent(new Event('input', { bubbles: true }));");
            $this->pressCheckout($browser);
            $browser->pause(800)->assertPathIs('/talent/venue');
            $state = $this->js($browser, "
                var name = document.getElementById('name').getBoundingClientRect();
                var bar = document.querySelector('.gk-buybar').getBoundingClientRect();
                return {
                    focus: document.activeElement.id,
                    invalid: document.getElementById('name').matches(':invalid'),
                    clear: name.bottom <= bar.top,
                    sending: document.querySelector('#ticket-selector').__vue_app__._container._vnode.component.proxy.isSubmitting,
                    problem: !!document.getElementById('checkout-problem'),
                };");
            $this->assertSame('name', $state['focus'], 'the missing field has the keyboard');
            $this->assertTrue($state['invalid']);
            $this->assertTrue($state['clear'], 'and is not under the bar');
            $this->assertFalse($state['sending'], 'nothing was sent');
            $this->assertFalse($state['problem'], 'the browser names a missing field itself');

            // Opened with the page: the phone bar is gone from the start, and the things in the
            // bottom corner are told how much room the form's bar takes.
            $browser->visit('/talent/venue?tickets=true')->waitFor('#ticket-0', 10);
            $browser->waitUntil("document.getElementById('gp-mobile-cta').offsetHeight === 0", 5);
            $browser->waitUntil("document.documentElement.classList.contains('es-a11y-cta-offset')", 5);
            $this->assertTrue($this->js($browser, "return document.querySelectorAll('[data-hidden-by-form]').length > 0;"), 'and what follows the form is put away, as it is when the button opens it');
        });
    }

    private function js(Browser $browser, string $script): mixed
    {
        return $browser->script($script)[0];
    }

    private function setQuantities(Browser $browser, int $quantity): void
    {
        $browser->script("
            document.querySelectorAll('#ticket-selector select[id^=\"ticket-\"]').forEach(function (select, index) {
                select.value = index === 0 ? '{$quantity}' : '0';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        ");
    }

    private function pressCheckout(Browser $browser): void
    {
        $browser->script("document.querySelector('#ticket-selector button[type=\"submit\"]').click();");
    }
}
