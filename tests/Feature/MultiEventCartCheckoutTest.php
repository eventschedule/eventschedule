<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Buying tickets to several events of one schedule in a single checkout.
 *
 * Free tickets throughout, so these exercise the leg loop and the eligibility guard without
 * involving a payment rail.
 */
class MultiEventCartCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    /** Two ticketed events on one schedule, same owner, currency and payment method. */
    private function twoEvents(array $overridesB = []): array
    {
        $owner = $this->createOwner();
        $this->role = $this->createRole($owner);

        $base = ['tickets_enabled' => true, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD'];

        $eventA = $this->createEvent($this->role, $base);
        $eventB = $this->createEvent($this->role, $base + $overridesB);

        if ($overridesB) {
            foreach ($overridesB as $key => $value) {
                $eventB->{$key} = $value;
            }
            $eventB->save();
        }

        return [
            $eventA,
            $eventB,
            $this->createTicket($eventA, ['price' => 0, 'quantity' => 50]),
            $this->createTicket($eventB, ['price' => 0, 'quantity' => 50]),
        ];
    }

    private function leg(Event $event, $ticket, int $qty = 1): array
    {
        return [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ];
    }

    private function checkout(array $legs)
    {
        return $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Cart Buyer',
            'email' => 'cart@example.com',
            'legs' => $legs,
        ]);
    }

    public function test_a_two_leg_cart_creates_one_sale_per_event_sharing_an_order_id(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB, 2)]);

        $sales = Sale::where('email', 'cart@example.com')->orderBy('id')->get();
        $this->assertCount(2, $sales, 'one sale per event');

        $this->assertSame($sales[0]->id, $sales[0]->order_id, 'the first leg anchors the order');
        $this->assertSame($sales[0]->id, $sales[1]->order_id, 'both legs share it');
        $this->assertEqualsCanonicalizing(
            [$eventA->id, $eventB->id],
            $sales->pluck('event_id')->all()
        );
    }

    public function test_a_leg_that_cannot_be_filled_rolls_back_the_whole_cart(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $ticketB->quantity = 1;
        $ticketB->save();

        // Leg A is satisfiable, leg B is not. Neither may be written: the buyer pays once, so a
        // partially-filled order would charge for tickets they did not get.
        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB, 5)]);

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count(),
            'the whole transaction must roll back, including the leg that was fine');
    }

    public function test_events_with_different_currencies_cannot_share_a_cart(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents(['ticket_currency_code' => 'EUR']);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('error', __('messages.cart_events_incompatible'));

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());
    }

    public function test_events_with_different_payment_methods_cannot_share_a_cart(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents(['payment_method' => 'stripe']);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('error', __('messages.cart_events_incompatible'));

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());
    }

    public function test_events_with_different_owners_cannot_share_a_cart(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        // A curator schedule lists events from several owners, and Stripe Connect routes by
        // events.user_id, so one session cannot pay them both.
        $eventB->user_id = $this->createOwner()->id;
        $eventB->save();

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('error', __('messages.cart_events_incompatible'));

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());
    }

    public function test_an_unsupported_payment_method_cannot_be_used_for_a_cart(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        // Both events agree, but the rail itself cannot represent an order.
        foreach ([$eventA, $eventB] as $event) {
            $event->payment_method = 'payment_url';
            $event->save();
        }

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('error', __('messages.cart_payment_method_unsupported'));

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());
    }

    public function test_a_single_leg_checkout_writes_no_order_id(): void
    {
        [$eventA, , $ticketA] = $this->twoEvents();

        $this->checkout([$this->leg($eventA, $ticketA)]);

        $sale = Sale::where('email', 'cart@example.com')->firstOrFail();
        $this->assertNull($sale->order_id, 'a one-event purchase is not an order and must be untouched');
    }
}
