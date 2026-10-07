<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\Payments\PaymentGatewayDriver;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Coming back from a payment that was cancelled.
 *
 * The form saves what was typed just before it hands the buyer to the payment provider, and put it
 * back only after a refused submit. A buyer who backed out of the provider's page landed on an
 * empty form: tickets back to zero, every answer gone, and not a word about whether they had been
 * charged. The three handlers a cancelled payment comes back through now leave one signal, which
 * the page reads to restore the form and to say nothing was charged.
 */
class CancelledPaymentReturnTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->createOwner();
        $owner->forceFill([
            'payment_url' => 'https://pay.example.org/organizer',
            'payment_secret' => 'test-payment-secret',
        ])->save();

        $this->role = $this->createRole($owner);
        $this->event = $this->createEvent($this->role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'creator_role_id' => $this->role->id,
        ]);
        $this->createTicket($this->event, ['price' => 20, 'quantity' => 50]);
    }

    private function unpaidSale(string $method = 'stripe'): Sale
    {
        return $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => $method,
            'payment_amount' => 20,
            'user_id' => $this->event->user_id,
        ]);
    }

    /** The address each of the three handlers answers on, for one sale. */
    private function cancelUrls(Sale $sale): array
    {
        $id = UrlUtils::encodeId($sale->id);

        return [
            'the card checkout' => route('checkout.cancel', [
                'subdomain' => $this->role->subdomain, 'sale_id' => $id, 'date' => $sale->event_date, 'secret' => $sale->secret,
            ]),
            'a payment link' => route('payment_url.cancel', ['subdomain' => $this->role->subdomain, 'sale_id' => $id]).'?secret=test-payment-secret',
            'a gateway driver' => '/payments/cash/cancel/'.$id.'?secret='.$sale->secret,
        ];
    }

    public function test_every_way_back_from_a_cancelled_payment_leaves_the_signal(): void
    {
        foreach (['the card checkout' => 'stripe', 'a payment link' => 'payment_url', 'a gateway driver' => 'cash'] as $name => $method) {
            $sale = $this->unpaidSale($method);
            session()->forget(PaymentGatewayDriver::CANCELLED_FLASH);

            $this->get($this->cancelUrls($sale)[$name])->assertRedirect();

            $this->assertSame('expired', $sale->fresh()->status, $name);
            $flash = session(PaymentGatewayDriver::CANCELLED_FLASH);
            $this->assertSame(UrlUtils::encodeId($this->event->id), $flash['event_id'] ?? null, $name.' says which event the payment was for');
            // A payment on the organizer's own page: the order was not placed, the money is not ours to vouch for.
            $this->assertSame($name === 'a payment link', $flash['charge_unknown'], $name);
        }
    }

    public function test_a_sale_that_was_paid_is_never_reported_as_not_charged(): void
    {
        // A cancel address is a GET guarded by a secret the buyer holds: it can be opened again
        // after the payment went through. "Nothing was charged" would then be false.
        foreach (['the card checkout' => 'stripe', 'a payment link' => 'payment_url', 'a gateway driver' => 'cash'] as $name => $method) {
            $sale = $this->unpaidSale($method);
            $sale->status = 'paid';
            $sale->save();

            $this->get($this->cancelUrls($sale)[$name])->assertRedirect();

            $this->assertSame('paid', $sale->fresh()->status, $name);
            $this->assertNull(session(PaymentGatewayDriver::CANCELLED_FLASH), $name);
        }
    }

    public function test_the_page_it_lands_on_says_nothing_was_charged_and_restores_the_form(): void
    {
        $sale = $this->unpaidSale();

        $target = $this->get($this->cancelUrls($sale)['the card checkout'])->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('tickets=true', $target);

        $html = $this->get($target)->assertOk()->getContent();

        $start = strpos($html, 'data-payment-cancelled');
        $this->assertNotFalse($start, 'the page says so');
        $this->assertStringContainsString(__('messages.payment_cancelled_not_charged'), substr($html, $start, 900));

        // Said INSIDE the form's panel: opening the form scrolls to it, and a line above it would
        // be scrolled out of sight.
        $this->assertGreaterThan(strpos($html, 'id="gp-event-form"'), $start);
        $this->assertLessThan(strpos($html, 'id="ticket-selector"'), $start);

        // And the form puts back what was typed, as it does after a refused submit.
        $this->assertStringContainsString('paymentCancelled: true', $html);
        $this->assertStringContainsString('if (this.hasError || this.paymentCancelled) {', $html);

        // One shot: the next visit is an ordinary one.
        $again = $this->get($target)->assertOk()->getContent();
        $this->assertStringNotContainsString('data-payment-cancelled', $again);
        $this->assertStringContainsString('paymentCancelled: false', $again);
    }

    public function test_the_signal_is_for_the_event_that_was_being_paid_for(): void
    {
        $other = $this->createEvent($this->role, [
            'starts_at' => now()->addDays(9)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'creator_role_id' => $this->role->id,
        ]);
        $this->createTicket($other, ['price' => 20, 'quantity' => 50]);

        $html = $this->withSession([])->flashCancelled($this->event)
            ->get($other->fresh()->getGuestUrl($this->role->subdomain).'?tickets=true')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-payment-cancelled', $html);
        $this->assertStringContainsString('paymentCancelled: false', $html);
    }

    public function test_the_embedded_form_says_it_too(): void
    {
        $html = $this->flashCancelled($this->event)
            ->get($this->event->fresh()->getGuestUrl($this->role->subdomain).'?embed=true&tickets=true')->assertOk()->getContent();

        $this->assertStringContainsString('data-payment-cancelled', $html);
        $this->assertStringContainsString('paymentCancelled: true', $html);
    }

    public function test_a_paid_order_clears_what_the_form_had_saved(): void
    {
        // The saved form holds the buyer's name, email and answers. Nothing removed it after a
        // purchase, so it stayed in the tab for as long as the tab lived.
        $sale = $this->createSale($this->event, $this->role, ['status' => 'paid', 'payment_amount' => 20]);
        $leg = ['subdomain' => $this->role->subdomain, 'event_id' => UrlUtils::encodeId($this->event->id), 'event_date' => (string) $sale->event_date];

        $html = $this->withSession(['cart_purchased' => [$leg]])->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id), 'secret' => $sale->secret,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString("sessionStorage.removeItem('checkout_form_' + leg.event_id)", $html);
        $this->assertStringContainsString("sessionStorage.removeItem('rsvp_form_' + leg.event_id)", $html);
    }

    public function test_a_payment_on_the_organizers_own_page_is_not_vouched_for(): void
    {
        $html = $this->flashCancelled($this->event, true)
            ->get($this->event->fresh()->getGuestUrl($this->role->subdomain).'?tickets=true')->assertOk()->getContent();

        $notice = substr($html, strpos($html, 'data-payment-cancelled'), 900);
        $this->assertStringContainsString(__('messages.payment_not_completed'), $notice);
        $this->assertStringNotContainsString(__('messages.payment_cancelled_not_charged'), $html);
    }

    private function flashCancelled(Event $event, bool $chargeUnknown = false): static
    {
        return $this->withSession([
            PaymentGatewayDriver::CANCELLED_FLASH => ['event_id' => UrlUtils::encodeId($event->id), 'charge_unknown' => $chargeUnknown],
            '_flash' => ['new' => [], 'old' => [PaymentGatewayDriver::CANCELLED_FLASH]],
        ]);
    }
}
