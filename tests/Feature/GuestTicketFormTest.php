<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ticket form, as a buyer meets it.
 *
 * It used to open on a name field with the tickets and their prices below it, said what was
 * missing in a browser alert box, shouted its buttons in capitals, and left the phone's "Buy
 * tickets" bar on screen under its own Checkout: that bar is printed at the foot of the document,
 * after the script that was meant to hide it, so the script's one lookup always found nothing.
 */
class GuestTicketFormTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function form(): array
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'creator_role_id' => $role->id,
        ]);
        $this->createTicket($event, ['price' => 10, 'quantity' => 5]);
        $html = $this->get($event->fresh()->getGuestUrl($role->subdomain).'?tickets=true')->assertOk()->getContent();

        $start = strpos($html, 'id="ticket-selector"');
        $this->assertNotFalse($start, 'the form is on the page');
        $end = strpos($html, '</form>', $start);

        return [$html, substr($html, $start, $end - $start)];
    }

    public function test_what_is_being_bought_comes_before_who_is_buying(): void
    {
        [, $form] = $this->form();

        $tickets = strpos($form, 'v-for="(ticket, index) in tickets"');
        $details = strpos($form, __('messages.your_details'));
        $name = strpos($form, 'id="name"');

        $this->assertNotFalse($tickets);
        $this->assertNotFalse($details, 'the details have a heading of their own');
        $this->assertNotFalse($name);
        $this->assertLessThan($details, $tickets, 'the ticket rows are above "Your details"');
        $this->assertLessThan($name, $details, 'and the name field is under that heading');
    }

    public function test_the_form_asks_for_a_ticket_before_it_asks_for_a_name(): void
    {
        [$html, $form] = $this->form();

        // The browser's own check runs before the submit event and stops at the first empty
        // required field, which is the name: so with it on, Checkout with nothing chosen asked
        // for a name. The form checks in its own order and hands the rest back to the browser.
        $this->assertMatchesRegularExpression('/\bnovalidate\b/', $this->formTag($html));
        $ticket = strpos($html, 'this.say('.json_encode(__('messages.please_select_ticket')));
        $browser = strpos($html, 'e.target.reportValidity()');
        $this->assertNotFalse($ticket, 'a missing ticket is said in the page');
        $this->assertNotFalse($browser, 'required fields are still checked');
        $this->assertLessThan($browser, $ticket);
        $this->assertStringContainsString('id="checkout-problem"', $form);
        $this->assertStringContainsString('role="alert"', $form);
    }

    private function formTag(string $html): string
    {
        $this->assertSame(1, preg_match('/<form[^>]*v-on:submit="validateForm"[^>]*>/', $html, $m), 'the checkout form tag');

        return $m[0];
    }

    public function test_nothing_is_said_in_a_browser_alert_box(): void
    {
        [$html] = $this->form();

        $this->assertSame(0, preg_match('/\balert\(\s*["\'@{]/', $html), 'no alert() on the event page');
        foreach (['event/tickets', 'event/rsvp', 'partials/guest-cart'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view.'.blade.php'));
            $this->assertSame(0, preg_match('/(?<![.\w])(?:window\.)?alert\(/', $source), $view.' has no alert()');
            $this->assertStringNotContainsString('strtoupper(__(', $source, $view.' says its buttons as the language file has them');
        }
    }

    public function test_the_actions_are_one_bar_and_a_ticket_has_no_stripe_down_its_side(): void
    {
        [$html, $form] = $this->form();

        $this->assertSame(1, substr_count($form, 'class="gk-buybar '), 'one bar');
        $bar = substr($form, strpos($form, 'class="gk-buybar '));
        $this->assertStringContainsString('id="checkout-problem"', $bar, 'what is missing is said in the bar, which is what stays on screen');
        $this->assertStringContainsString('dusk="add-to-cart"', $bar);
        $this->assertStringContainsString('formatPrice(totalAmount)', $bar, 'the amount is on the button that takes it');
        $this->assertStringNotContainsString('border-s-4', $form);
        $this->assertStringNotContainsString('border-inline-start-color', $form);
        $this->assertStringContainsString('.gk-buybar { position: sticky; bottom: 0;', $html);
    }

    public function test_the_page_looks_for_its_phone_bar_when_it_needs_it(): void
    {
        [$html] = $this->form();

        $script = strpos($html, 'function hidePanelsBelow');
        $bar = strpos($html, 'id="gp-mobile-cta"');
        $this->assertNotFalse($script);
        $this->assertNotFalse($bar);
        $this->assertLessThan($bar, $script, 'the bar is printed after the script, which is why a lookup made once found nothing');
        $this->assertStringContainsString("function mobileCta() { return document.getElementById('gp-mobile-cta'); }", $html);
        $this->assertStringNotContainsString("var mobileCta = document.getElementById('gp-mobile-cta');", $html);
        // A form that opens with the page hides what is below it once the page has been read.
        $this->assertMatchesRegularExpression("/document\.addEventListener\('DOMContentLoaded', function \(\) \{\s*if \(form\.style\.display !== 'none'\) \{ hideCta\(\); hidePanelsBelow\(\); \}/", $html);
        // The ticket form opens on its tickets: only the sign-up form takes the keyboard.
        $this->assertSame(2, substr_count($html, "paramKey === 'rsvp' ? document.getElementById('name') : null"));
    }

    public function test_checkout_can_be_pressed_again_after_coming_back_with_the_back_button(): void
    {
        [$html] = $this->form();

        $this->assertMatchesRegularExpression("/addEventListener\('pageshow', \(event\) => \{\s*if \(event\.persisted\) \{ this\.isSubmitting = false; \}/", $html);
    }
}
