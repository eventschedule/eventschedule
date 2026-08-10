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

    public function test_a_free_first_leg_does_not_settle_an_order_that_still_owes(): void
    {
        [$eventA, $eventB, $ticketA] = $this->twoEvents();

        // Leg A is free, leg B is not. The free-order short circuit reads the total, and reading
        // only the FIRST leg's total sees 0 - marking the order paid, cascading paid to leg B, and
        // handing over tickets nobody paid for.
        $ticketB = $this->createTicket($eventB, ['price' => 20, 'quantity' => 50]);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $sales = Sale::where('email', 'cart@example.com')->get();
        $this->assertCount(2, $sales);

        foreach ($sales as $sale) {
            $this->assertNotSame('paid', $sale->status,
                'an order with an unpaid leg must not be settled by its free one');
        }
    }

    public function test_a_cart_lands_on_an_order_page_listing_every_event(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $response = $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $primary = Sale::where('email', 'cart@example.com')->orderBy('id')->first();
        $response->assertRedirect(route('ticket.order', [
            'order_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
        ]));

        // Both events must be reachable from it; a single ticket page would hide the other.
        $this->get(route('ticket.order', [
            'order_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
        ]))->assertOk()
            ->assertSee($eventA->name)
            ->assertSee($eventB->name);
    }

    public function test_the_order_page_refuses_a_wrong_secret(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();
        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $primary = Sale::where('email', 'cart@example.com')->orderBy('id')->first();

        $this->get(route('ticket.order', [
            'order_id' => UrlUtils::encodeId($primary->id),
            'secret' => 'not-the-secret',
        ]))->assertNotFound();
    }

    public function test_abandoning_the_payment_expires_the_whole_order(): void
    {
        [$eventA, $eventB, $ticketA] = $this->twoEvents();
        $ticketB = $this->createTicket($eventB, ['price' => 20, 'quantity' => 50]);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $sales = Sale::where('email', 'cart@example.com')->orderBy('id')->get();
        $primary = $sales->first();

        // The Stripe cancel_url carries the order primary, and expiring it cascades - so the
        // abandoned order releases every leg's seats, not just the first one's.
        // Both extras go through route() as query params. Appending "?secret=" by hand produces a
        // second "?" - the path-based checkout.cancel has no {date} segment, so date is already a
        // query param - and the secret is then never parsed, which reads as a 403 rather than a
        // failed cascade.
        $this->get(route('checkout.cancel', [
            'subdomain' => $this->role->subdomain,
            'sale_id' => UrlUtils::encodeId($primary->id),
            'date' => $primary->event_date,
            'secret' => $primary->secret,
        ]))->assertRedirect();

        foreach ($sales as $sale) {
            $this->assertSame('expired', $sale->fresh()->status);
        }
    }

    public function test_the_cart_widget_renders_outside_the_ticket_selector_mount(): void
    {
        [$eventA] = $this->twoEvents();

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $this->role->subdomain,
            'slug' => $eventA->slug,
        ]))->assertOk()->assertSee('es-cart-app', false)->getContent();

        // Vue compiles the template of whatever it mounts, so a cart nested inside #ticket-selector
        // would be consumed by that app instead of its own. Assert the ordering rather than trust
        // where the include happens to sit in the layout.
        $this->assertGreaterThan(
            strpos($html, 'id="ticket-selector"'),
            strpos($html, 'id="es-cart-app"'),
            'the cart mount must come after the ticket selector mount closes'
        );
    }

    public function test_a_paid_cart_also_lands_on_the_order_page(): void
    {
        [$eventA, $eventB, $ticketA] = $this->twoEvents();

        // Priced, so checkout takes the payment rail instead of the free short circuit. This is the
        // path a real cart uses, and it used to drop the buyer on one leg's ticket with no sign the
        // others existed - the free path was the only one that reached the order page.
        $ticketA->price = 15;
        $ticketA->save();
        $ticketB = $this->createTicket($eventB, ['price' => 20, 'quantity' => 50]);

        $response = $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $primary = Sale::where('email', 'cart@example.com')->orderBy('id')->first();
        $this->assertNotNull($primary);
        $response->assertRedirect(route('ticket.order', [
            'order_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
        ]));
    }

    public function test_a_multi_event_order_sends_a_ticket_email_for_every_leg(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $sales = Sale::where('email', 'cart@example.com')->orderBy('id')->get();
        $this->assertCount(2, $sales);

        // Recorded at the seam rather than through the mail transport, whose own gates (test
        // domains, demo schedules, configured sender) would decide the outcome instead.
        $recorder = new class extends \App\Services\EmailService
        {
            public array $ticketed = [];

            public function sendTicketEmail(Sale $sale, ?\App\Models\Role $role = null, bool $queue = true): string|true
            {
                $this->ticketed[] = $sale->id;

                return true;
            }
        };

        $recorder->sendSaleConfirmationEmails($sales->first()->fresh());

        // One ticket per event. Driving this from the order primary alone sent the buyer the first
        // leg's ticket and never told them about the second event they had paid for.
        $this->assertEqualsCanonicalizing($sales->pluck('id')->all(), $recorder->ticketed);
    }

    public function test_a_cart_refused_by_validation_shows_the_reason(): void
    {
        // session('error') is already toasted on every guest page (app-guest nests inside
        // layouts/app), so the eligibility refusals above were visible. VALIDATION errors are not
        // toasted anywhere - and the cart collected only name and email while
        // TicketCheckoutRequest unions require_phone across legs, so a cart holding a
        // phone-required event was refused with nothing whatsoever on screen.
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents(['ask_phone' => true, 'require_phone' => true]);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHasErrors('phone');

        $this->get(route('role.view_guest', ['subdomain' => $this->role->subdomain]))
            ->assertOk()
            ->assertSee('es-cart-error', false);
    }

    public function test_the_event_page_tells_the_cart_whether_to_ask_for_a_phone(): void
    {
        [$eventA] = $this->twoEvents();
        $eventA->ask_phone = true;
        $eventA->require_phone = true;
        $eventA->save();

        // The cart lives in the guest layout and knows nothing about any event's settings, so the
        // event page has to hand it these with the leg. Without them the phone field never renders
        // and the checkout cannot satisfy the rule the server applies.
        $this->get(route('event.view_guest', [
            'subdomain' => $this->role->subdomain,
            'slug' => $eventA->slug,
        ]))->assertOk()
            ->assertSee('ask_phone: true', false)
            ->assertSee('require_phone: true', false);
    }

    public function test_a_cart_can_check_out_an_event_that_requires_a_phone(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents(['ask_phone' => true, 'require_phone' => true]);

        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Cart Buyer',
            'email' => 'cart@example.com',
            'phone' => '+1 555 0100',
            'legs' => [$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)],
        ]);

        $sales = Sale::where('email', 'cart@example.com')->get();
        $this->assertCount(2, $sales, 'a phone-required event must be purchasable from the cart');
        $this->assertNotEmpty($sales->first()->phone);
    }

    public function test_a_completed_purchase_tells_the_cart_to_drop_what_was_bought(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('cart_purchased');

        // The landing pages render through x-app-layout, where the cart widget does not exist, so
        // it cannot empty itself. Left holding the completed purchase it offered a CHECKOUT button
        // on the buyer's next visit that would have charged them a second time.
        $primary = Sale::where('email', 'cart@example.com')->orderBy('id')->first();
        $purchased = collect(session('cart_purchased'));

        $this->assertCount(2, $purchased, 'every leg of the order must be cleared');
        $this->assertEqualsCanonicalizing(
            [UrlUtils::encodeId($eventA->id), UrlUtils::encodeId($eventB->id)],
            $purchased->pluck('event_id')->all()
        );

        $this->get(route('ticket.order', [
            'order_id' => UrlUtils::encodeId($primary->id),
            'secret' => $primary->secret,
        ]))->assertOk()->assertSee('es_cart_', false);
    }

    public function test_an_abandoned_payment_keeps_the_cart(): void
    {
        [$eventA, $eventB, $ticketA] = $this->twoEvents();
        $ticketB = $this->createTicket($eventB, ['price' => 20, 'quantity' => 50]);

        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $primary = Sale::where('email', 'cart@example.com')->orderBy('id')->first();

        // Abandoning at the payment step must leave the cart intact - the buyer is expected to come
        // back and try again, and an emptied cart would make that impossible.
        $this->get(route('checkout.cancel', [
            'subdomain' => $this->role->subdomain,
            'sale_id' => UrlUtils::encodeId($primary->id),
            'date' => $primary->event_date,
            'secret' => $primary->secret,
        ]))->assertRedirect();

        $this->assertNull(session('cart_purchased'), 'an abandoned payment must not clear the cart');
    }

    public function test_a_cart_naming_an_unavailable_event_does_not_404(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        // Unpublished after the buyer added it - the cart is built to outlive the visit, so this is
        // ordinary. It used to abort(404) with an unstyled page and no clue which event was at
        // fault, while the cart went on holding it so every retry 404'd again.
        $eventB->is_draft = true;
        $eventB->save();

        $response = $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('messages.cart_event_unavailable', ['event' => $eventB->name]));
        $response->assertSessionHas('cart_invalid_legs', [UrlUtils::encodeId($eventB->id).'|'.\Carbon\Carbon::parse($eventB->starts_at)->format('Y-m-d')]);

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());
    }

    public function test_a_per_attendee_event_cannot_be_carted(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents(['individual_tickets' => true]);

        // The cart collects one name and email for the whole purchase, so carting a per-attendee
        // event silently turned it into one anonymous multi-seat sale and lost the guest list the
        // organizer turned this on to collect.
        // Its own message: the event is available, it just cannot be carted, so telling the buyer it
        // is "no longer available" would send them to delete something they can still buy.
        $this->checkout([$this->leg($eventA, $ticketA), $this->leg($eventB, $ticketB)])
            ->assertSessionHas('error', __('messages.cart_event_needs_own_checkout', ['event' => $eventB->name]));

        $this->assertSame(0, Sale::where('email', 'cart@example.com')->count());

        // ...and the button is not offered in the first place. Asserted on the button's own label
        // rather than the handler name: the addToCart() method is always defined, only the button
        // that calls it is gated.
        $this->get(route('event.view_guest', [
            'subdomain' => $this->role->subdomain,
            'slug' => $eventB->slug,
        ]))->assertOk()->assertDontSee(strtoupper(__('messages.add_to_cart')), false);
    }

    public function test_the_event_page_hands_the_cart_the_occurrence_the_buyer_is_viewing(): void
    {
        [$eventA] = $this->twoEvents();

        // The leg is built from the ticket selector's own state. Until event_date was a property of
        // that state, addToCart() read undefined, JSON.stringify dropped the key, and every cart leg
        // reached the server with an empty date - so a recurring event sold its series start rather
        // than the occurrence on screen. Asserted on the rendered state, which is what the browser
        // actually sends, rather than on a hand-built payload.
        $date = \Carbon\Carbon::parse($eventA->starts_at)->format('Y-m-d');

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $this->role->subdomain,
            'slug' => $eventA->slug,
        ]).'?date='.$date)->assertOk()->getContent();

        // Anchored to its neighbour in the data block. A bare search for the date would also match
        // the waitlist fetch further down the same file, which renders event_date from Blade and
        // would keep this test green with the property missing.
        $this->assertMatchesRegularExpression(
            '/event_date:\s*"'.preg_quote($date, '/').'",\s*eventCustomValues/',
            $html,
            'the ticket selector must carry the occurrence in its own state, not just in Blade'
        );
    }

    public function test_a_recurring_events_second_date_keeps_its_own_occurrence(): void
    {
        $owner = $this->createOwner();
        $this->role = $this->createRole($owner);

        $friday = \Carbon\Carbon::now()->next(\Carbon\Carbon::FRIDAY)->format('Y-m-d');
        $saturday = \Carbon\Carbon::now()->next(\Carbon\Carbon::SATURDAY)->format('Y-m-d');

        $event = $this->createEvent($this->role, [
            'tickets_enabled' => true, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'days_of_week' => '0000011',
        ]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);

        // Server-side half of the occurrence contract: two dated legs for one event stay two
        // distinct sales. The client-side half - that the browser actually puts a date on the wire
        // - is pinned by the test above; this one posts the dates itself and so cannot see it.
        $this->checkout([
            ['event_id' => UrlUtils::encodeId($event->id), 'event_date' => $friday, 'tickets' => [UrlUtils::encodeId($ticket->id) => 1]],
            ['event_id' => UrlUtils::encodeId($event->id), 'event_date' => $saturday, 'tickets' => [UrlUtils::encodeId($ticket->id) => 1]],
        ]);

        $sales = Sale::where('email', 'cart@example.com')->get();

        $this->assertCount(2, $sales, 'two occurrences are two sales');
        $this->assertEqualsCanonicalizing([$friday, $saturday], $sales->pluck('event_date')->all(),
            'each leg must keep the occurrence the buyer picked');
    }

    /**
     * event_date is handed straight to Carbon by canSellTickets() -> getStartDateTime(), and that
     * happens BEFORE checkout()'s try block, so a non-scalar was an uncaught TypeError rather
     * than a validation failure. event_id was hardened against exactly this; event_date was not.
     */
    public function test_a_non_scalar_event_date_is_rejected_not_fatal(): void
    {
        [$eventA, $eventB, $ticketA, $ticketB] = $this->twoEvents();

        $leg = $this->leg($eventA, $ticketA);
        $leg['event_date'] = ['2026-08-10'];

        $this->checkout([$leg, $this->leg($eventB, $ticketB)])
            ->assertSessionHasErrors('legs.0.event_date');

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
