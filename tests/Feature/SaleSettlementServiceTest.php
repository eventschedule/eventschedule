<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Services\SaleSettlementService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The single settle-to-paid path every rail now goes through.
 *
 * Each test here corresponds to something one of the seven hand-written copies got wrong, so these are
 * the reason the consolidation was worth doing rather than incidental coverage of it.
 */
class SaleSettlementServiceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function settlement(): SaleSettlementService
    {
        return app(SaleSettlementService::class);
    }

    private function saleFor(array $eventAttrs = [], float $price = 100, int $qty = 1): Sale
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'payment_method' => 'cash',
            'ticket_currency_code' => 'USD',
        ], $eventAttrs));
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => $price, 'quantity' => 50]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Buyer',
            'email' => 'buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $qty],
        ]);

        return Sale::where('email', 'buyer@gmail.com')->firstOrFail();
    }

    public function test_a_released_sale_is_never_revived(): void
    {
        // The bug paymentUrlSuccess() carried: it short-circuited only on 'paid', so an expired sale
        // could be flipped back. Expiry has already returned the seats and credited back any gift
        // card, and marking paid does not re-take them, so this oversells the event.
        foreach (['expired', 'cancelled', 'refunded'] as $status) {
            $sale = $this->saleFor();
            $sale->status = $status;
            $sale->save();

            $outcome = $this->settlement()->settle($sale, 'ref-1', null, 'test');

            $this->assertSame('released', $outcome, $status.' must not settle');
            $this->assertSame($status, $sale->fresh()->status);
        }
    }

    public function test_settling_twice_books_revenue_once(): void
    {
        $sale = $this->saleFor(price: 100);

        $this->assertSame('paid', $this->settlement()->settle($sale, 'ref-1', 100.0, 'test'));
        $this->assertSame('already_paid', $this->settlement()->settle($sale, 'ref-1', 100.0, 'test'));

        $this->assertSame(
            100.0,
            (float) AnalyticsEventsDaily::where('event_id', $sale->event_id)->sum('revenue'),
        );
    }

    public function test_a_short_payment_is_flagged_rather_than_accepted(): void
    {
        $sale = $this->saleFor(price: 100);

        $this->assertSame('amount_mismatch', $this->settlement()->settle($sale, 'ref-1', 60.0, 'test'));

        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame(0.0, (float) AnalyticsEventsDaily::where('event_id', $sale->event_id)->sum('revenue'));
    }

    public function test_rounding_within_a_cent_still_settles(): void
    {
        // Gateways round to their own smallest unit and our totals are summed from decimals, so exact
        // equality cannot be required even when nothing is wrong.
        $sale = $this->saleFor(price: 100);

        $this->assertSame('paid', $this->settlement()->settle($sale, 'ref-1', 100.009, 'test'));
        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_the_strict_gate_keeps_a_flagged_sale_frozen(): void
    {
        // What the three Invoice Ninja sites rely on. Their `!== unpaid` gate means a sale parked in
        // amount_mismatch has never been settleable by a retry, so a human has to look at it. Without
        // $onlyWhenUnpaid the permissive gate would let a later webhook clear it silently.
        $sale = $this->saleFor(price: 100);
        $sale->status = 'amount_mismatch';
        $sale->save();

        $this->assertSame(
            'not_settleable',
            $this->settlement()->settle($sale, 'ref-1', 100.0, 'test', onlyWhenUnpaid: true),
        );
        $this->assertSame('amount_mismatch', $sale->fresh()->status);

        // Without the strict gate the same call does settle it - which is the behaviour the Invoice
        // Ninja rails must NOT get.
        $this->assertSame('paid', $this->settlement()->settle($sale, 'ref-1', 100.0, 'test'));
    }

    public function test_a_null_reference_leaves_the_stored_one_alone(): void
    {
        // Invoice Ninja's purchaseWebhook depends on this: it finds its sale by
        // `transaction_reference LIKE 'sub:%'`, so overwriting the marker would make the row
        // permanently unfindable.
        $sale = $this->saleFor(price: 100);
        $sale->transaction_reference = 'sub:12345';
        $sale->save();

        $this->settlement()->settle($sale, null, null, 'test');

        $this->assertSame('sub:12345', $sale->fresh()->transaction_reference);
        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_null_amount_leaves_payment_amount_alone(): void
    {
        // Rails that report no amount must not zero out the server-computed figure.
        $sale = $this->saleFor(price: 100);

        $this->settlement()->settle($sale, 'ref-1', null, 'test');

        $this->assertSame(100.0, (float) $sale->fresh()->payment_amount);
    }

    public function test_the_audit_row_records_the_real_prior_status(): void
    {
        // The hand-written copies hardcoded ['status' => 'unpaid'], which is wrong exactly when the
        // sale came from amount_mismatch - the case someone reading the trail most needs to see.
        $sale = $this->saleFor(price: 100);
        $sale->status = 'amount_mismatch';
        $sale->save();

        $this->settlement()->settle($sale, 'ref-1', 100.0, 'test');

        // Decoded rather than compared as a JSON string: MySQL normalises whitespace inside a JSON
        // column, so a literal json_encode() comparison fails on formatting alone.
        $logged = \DB::table('audit_logs')
            ->where('action', 'sale.paid')
            ->where('model_id', $sale->id)
            ->value('old_values');

        $this->assertSame(['status' => 'amount_mismatch'], json_decode((string) $logged, true));
    }

    public function test_a_leg_already_paid_by_hand_is_not_credited_twice(): void
    {
        // The bug the Stripe copies carried. They read leg statuses AFTER the save and credited every
        // leg then showing 'paid', so a leg an owner had already marked paid at the door got its
        // revenue booked a second time when the anchor settled. The service reads pre-save statuses and
        // skips it.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $eventA = $this->createEvent($role, [
            'tickets_enabled' => true, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
        ]);
        $eventB = $this->createEvent($role, [
            'tickets_enabled' => true, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
        ]);
        $ticketA = $this->createTicket($eventA, ['type' => 'A', 'price' => 30, 'quantity' => 10]);
        $ticketB = $this->createTicket($eventB, ['type' => 'B', 'price' => 50, 'quantity' => 10]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'name' => 'Cart Buyer',
            'email' => 'cart-buyer@gmail.com',
            'legs' => [
                [
                    'event_id' => UrlUtils::encodeId($eventA->id),
                    'event_date' => Carbon::parse($eventA->starts_at)->format('Y-m-d'),
                    'tickets' => [UrlUtils::encodeId($ticketA->id) => 1],
                ],
                [
                    'event_id' => UrlUtils::encodeId($eventB->id),
                    'event_date' => Carbon::parse($eventB->starts_at)->format('Y-m-d'),
                    'tickets' => [UrlUtils::encodeId($ticketB->id) => 1],
                ],
            ],
        ]);

        $anchor = Sale::where('email', 'cart-buyer@gmail.com')->whereNotNull('order_id')->orderBy('id')->firstOrFail();
        $legB = Sale::where('order_id', $anchor->order_id)->where('id', '!=', $anchor->id)->firstOrFail();

        // The owner collects leg B in person first. markSalePaid() books its revenue then.
        $legB->status = 'paid';
        $legB->save();
        AnalyticsEventsDaily::incrementSale($legB->event_id, $legB->legTotalPayment());

        $creditedBefore = (float) AnalyticsEventsDaily::where('event_id', $legB->event_id)->sum('revenue');

        // Now the whole order settles through the gateway.
        $this->settlement()->settle($anchor, 'ref-1', 80.0, 'test');

        $this->assertSame(
            $creditedBefore,
            (float) AnalyticsEventsDaily::where('event_id', $legB->event_id)->sum('revenue'),
            'a leg that was already paid must not have its revenue booked a second time',
        );
    }

    public function test_cancel_then_success_on_a_payment_url_sale_does_not_mint_a_ticket(): void
    {
        // End to end shape of the bug paymentUrlSuccess() shipped with. Both callbacks are GETs
        // guarded by the same secret, so a buyer who abandons and then re-opens their success link used
        // to get a paid ticket out of seats the cancel had already released.
        $owner = $this->createOwner();
        $owner->forceFill([
            'payment_url' => 'https://pay.example.org/organizer',
            'payment_secret' => 'test-payment-secret',
        ])->save();

        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'payment_url',
            'ticket_currency_code' => 'USD',
        ]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 40, 'quantity' => 10]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Link Buyer',
            'email' => 'link-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ]);

        $sale = Sale::where('email', 'link-buyer@gmail.com')->firstOrFail();
        $encoded = UrlUtils::encodeId($sale->id);

        // route() rather than a literal path: the guest routes are subdomain-based hosted and
        // path-based on selfhost, and tests run in the path-based mode.
        $cancelUrl = route('payment_url.cancel', ['subdomain' => $role->subdomain, 'sale_id' => $encoded]);
        $successUrl = route('payment_url.success', ['subdomain' => $role->subdomain, 'sale_id' => $encoded]);

        $this->get($cancelUrl.'?secret=test-payment-secret');
        $this->assertSame('expired', $sale->fresh()->status);

        $this->get($successUrl.'?secret=test-payment-secret');

        $this->assertSame('expired', $sale->fresh()->status,
            'a released sale must not be revived by re-opening the success link');
        $this->assertSame(0.0, (float) AnalyticsEventsDaily::where('event_id', $event->id)->sum('revenue'));
    }

    public function test_a_deleted_sale_is_never_settled(): void
    {
        // deleteSale() cancels live rows first, but an amount_mismatch row keeps its status and only
        // gains the is_deleted flag - so it stays findable by transaction_reference, and without the
        // guard a correct-amount retry would settle a sale the owner deleted, credit its revenue and
        // email its ticket.
        $sale = $this->saleFor(price: 100);
        $sale->status = 'amount_mismatch';
        $sale->is_deleted = true;
        $sale->saveQuietly();

        $outcome = $this->settlement()->settle($sale, 'ref-1', 100.0, 'test');

        $this->assertSame('deleted', $outcome);
        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame(
            0.0,
            (float) AnalyticsEventsDaily::where('event_id', $sale->event_id)->sum('revenue'),
            'a deleted sale must not be credited',
        );
    }

    public function test_a_paid_then_deleted_sale_reports_already_paid_not_deleted(): void
    {
        // Order of the guards matters. This sale WAS honoured - the buyer holds a ticket - so a
        // redelivered ITN must report already_paid. Reporting 'deleted' would fire the
        // money-with-no-ticket alarm on the channel that exists to catch real ones.
        $sale = $this->saleFor(price: 100);

        $this->settlement()->settle($sale, 'ref-1', 100.0, 'test');
        $this->assertSame('paid', $sale->fresh()->status);

        $sale->refresh();
        $sale->is_deleted = true;
        $sale->saveQuietly();

        $this->assertSame(
            'already_paid',
            $this->settlement()->settle($sale, 'ref-1', 100.0, 'test'),
        );
    }

    public function test_a_missing_sale_is_reported_rather_than_fataling(): void
    {
        $sale = $this->saleFor();
        $id = $sale->id;
        Sale::where('id', $id)->forceDelete();

        $this->assertSame('missing', $this->settlement()->settle($sale, 'ref-1', null, 'test'));
    }
}
