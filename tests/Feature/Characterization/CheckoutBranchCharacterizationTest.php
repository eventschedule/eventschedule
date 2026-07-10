<?php

namespace Tests\Feature\Characterization;

use App\Models\PromoCode;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes TicketController::checkout()'s offline-testable payment
 * branches ahead of the P10 split and P15 decomposition (REFACTOR_PLAN.md):
 * the cash default branch end-to-end, the payment_url redirect-out shape,
 * promo codes applied to PAID sales, volume discounts, and the passBook
 * route. Stripe/InvoiceNinja need live APIs - their branches are pinned only
 * up to what runs offline (see TicketingTest for the free path).
 */
class CheckoutBranchCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function checkoutPayload($event, $ticket, int $qty = 1, array $extra = []): array
    {
        return array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Paid Buyer',
            'email' => 'paid-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ], $extra);
    }

    public function test_cash_branch_creates_unpaid_sale_and_redirects_to_ticket_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'cash',
            'ticket_currency_code' => 'USD',
        ]);
        $ticket = $this->createTicket($event, ['type' => 'Paid', 'price' => 20, 'quantity' => 100]);

        $response = $this->post(
            route('event.checkout', ['subdomain' => $role->subdomain]),
            $this->checkoutPayload($event, $ticket, 2)
        );

        $sale = Sale::where('email', 'paid-buyer@gmail.com')->firstOrFail();

        // Cash sales stay unpaid until the organizer marks them; the buyer is
        // sent straight to their ticket page keyed by the sale secret.
        $response->assertRedirect(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'event_id' => $event->id,
            'status' => 'unpaid',
            'payment_method' => 'cash',
            'payment_amount' => 40,
            'promo_code_id' => null,
        ]);
        $this->assertDatabaseHas('sale_tickets', [
            'sale_id' => $sale->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);
    }

    public function test_payment_url_branch_redirects_to_the_organizers_payment_url(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['payment_url' => 'https://pay.example.org/organizer'])->save();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'payment_url',
        ]);
        $ticket = $this->createTicket($event, ['type' => 'Paid', 'price' => 15, 'quantity' => 100]);

        $response = $this->post(
            route('event.checkout', ['subdomain' => $role->subdomain]),
            $this->checkoutPayload($event, $ticket)
        );

        // Redirect-out shape: the event OWNER's payment_url, verbatim.
        $response->assertRedirect('https://pay.example.org/organizer');

        $this->assertDatabaseHas('sales', [
            'event_id' => $event->id,
            'email' => 'paid-buyer@gmail.com',
            'status' => 'unpaid',
            'payment_method' => 'payment_url',
            'payment_amount' => 15,
        ]);
    }

    public function test_promo_code_is_applied_to_a_paid_cash_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'cash',
        ]);
        $ticket = $this->createTicket($event, ['type' => 'Paid', 'price' => 20, 'quantity' => 100]);
        $promo = PromoCode::create([
            'event_id' => $event->id,
            'code' => 'HALF',
            'type' => 'percentage',
            'value' => 50,
            'is_active' => true,
        ]);

        $this->post(
            route('event.checkout', ['subdomain' => $role->subdomain]),
            // Promo lookup is case-insensitive.
            $this->checkoutPayload($event, $ticket, 2, ['promo_code' => 'half'])
        );

        // 2 x $20 = $40, 50% off -> $20 payable, $20 discount recorded.
        $this->assertDatabaseHas('sales', [
            'event_id' => $event->id,
            'email' => 'paid-buyer@gmail.com',
            'promo_code_id' => $promo->id,
            'payment_amount' => 20,
            'discount_amount' => 20,
        ]);
    }

    public function test_volume_discount_is_applied_at_threshold(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'cash',
        ]);
        $ticket = $this->createTicket($event, [
            'type' => 'Bulk',
            'price' => 10,
            'quantity' => 100,
            'volume_discount' => ['min_quantity' => 3, 'type' => 'percentage', 'value' => 10],
        ]);

        $this->post(
            route('event.checkout', ['subdomain' => $role->subdomain]),
            $this->checkoutPayload($event, $ticket, 3)
        );

        // 3 x $10 = $30, 10% volume discount -> $27.
        $this->assertDatabaseHas('sales', [
            'event_id' => $event->id,
            'email' => 'paid-buyer@gmail.com',
            'payment_amount' => 27,
        ]);
    }

    public function test_pass_book_route_books_an_occurrence_and_rejects_garbage_dates(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createRecurringEvent($role, [
            'tickets_enabled' => true,
            'creator_role_id' => null, // set below - createEvent doesn't set it
        ]);
        $event->forceFill(['creator_role_id' => $role->id])->save();

        $pass = $this->createTicket($event, [
            'type' => 'Season Pass',
            'price' => 50,
            'quantity' => 10,
            'is_pass' => true,
            'pass_usage_type' => 'total',
            'pass_max_uses' => 5,
            'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $pass);

        $date = now()->addDays(7)->format('Y-m-d');
        $params = ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret];

        // Garbage date is rejected before the booking service runs.
        $this->post(route('pass.book', $params), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => 'not-a-date',
        ])->assertRedirect(route('ticket.view', $params))
            ->assertSessionHas('error', __('messages.pass_invalid_date'));

        // A valid booking lands a `kind: booking` entry in the sale ticket's
        // pass_usages JSON (there is no bookings table).
        $this->post(route('pass.book', $params), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ])->assertRedirect(route('ticket.view', $params));

        $usages = collect(json_decode($sale->saleTickets()->first()->fresh()->getRawOriginal('pass_usages') ?? '[]', true));
        $booking = $usages->first(fn ($u) => ($u['kind'] ?? null) === 'booking');
        $this->assertNotNull($booking, 'expected a booking entry in pass_usages');
        $this->assertSame($event->id, (int) $booking['event_id']);
        $this->assertSame($date, $booking['date'] ?? $booking['event_date'] ?? null);
    }
}
