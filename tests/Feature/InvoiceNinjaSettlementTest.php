<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The three Invoice Ninja webhooks, which are the only things that mark an Invoice Ninja sale paid.
 *
 * Written to close a coverage gap found while reviewing #113: InvoiceNinjaConnectTest was the rail's
 * only test file and every one of its tests covers the CONNECT flow, so nothing asserted that an
 * Invoice Ninja sale ever reaches paid - even though the Payfast work rerouted all three settle sites
 * through SaleSettlementService and restructured eventPurchaseWebhook's transaction.
 *
 * These run entirely offline. App\Utils\InvoiceNinja talks over raw curl and so cannot be faked, but
 * nothing on the settle path constructs it: the webhooks authenticate against a stored shared secret
 * and read a JSON body.
 */
class InvoiceNinjaSettlementTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SECRET = 'test-webhook-secret';

    private $owner;

    private $role;

    private $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->owner->forceFill([
            'invoiceninja_api_key' => 'test-key',
            'invoiceninja_api_url' => 'https://invoicing.example',
            'invoiceninja_webhook_secret' => self::SECRET,
        ])->save();

        $this->role = $this->createRole($this->owner);
        $this->event = $this->createEvent($this->role, [
            'tickets_enabled' => true,
            'payment_method' => 'invoiceninja',
        ]);
    }

    private function postJson_(string $url, array $payload)
    {
        return $this->call('POST', $url, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SECRET' => self::SECRET,
        ], json_encode($payload));
    }

    public function test_an_invoice_payment_webhook_settles_the_sale(): void
    {
        // Invoice mode: the sale carries the Invoice Ninja invoice id as its reference, and the
        // webhook reports what was actually paid against it.
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);
        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 300,
            'transaction_reference' => 'inv-123',
            'email' => 'in-buyer@gmail.com',
        ], $ticket, 2);

        $this->postJson_(route('invoiceninja.webhook'), [
            'paymentables' => [['invoice_id' => 'inv-123', 'amount' => 300]],
        ])->assertOk();

        $sale->refresh();
        $this->assertSame('paid', $sale->status);

        // Revenue is booked, once, against this event.
        $this->assertSame(
            300.0,
            (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'),
        );
    }

    public function test_a_short_invoice_payment_is_parked_rather_than_settled(): void
    {
        // The reconciliation that makes the webhook safe to expose: a payload claiming a smaller
        // payment must not clear the sale.
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);
        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 300,
            'transaction_reference' => 'inv-124',
            'email' => 'short@gmail.com',
        ], $ticket, 2);

        $this->postJson_(route('invoiceninja.webhook'), [
            'paymentables' => [['invoice_id' => 'inv-124', 'amount' => 1]],
        ])->assertOk();

        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame(0.0, (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'));
    }

    public function test_an_amount_mismatch_stays_frozen_when_a_later_webhook_reports_the_right_amount(): void
    {
        // onlyWhenUnpaid: true is what preserves this rail's long-standing gate. A sale parked in
        // amount_mismatch was flagged for a human, and a retry has never been able to clear it - so
        // the settlement service must refuse it even though the amount now reconciles.
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);
        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'amount_mismatch',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 300,
            'transaction_reference' => 'inv-125',
            'email' => 'frozen@gmail.com',
        ], $ticket, 2);

        $this->postJson_(route('invoiceninja.webhook'), [
            'paymentables' => [['invoice_id' => 'inv-125', 'amount' => 300]],
        ])->assertOk();

        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame(0.0, (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'));
    }

    public function test_the_purchase_webhook_settles_without_overwriting_the_subscription_marker(): void
    {
        // Payment-link mode parks the subscription id in transaction_reference as "sub:<id>", and
        // purchaseWebhook finds the row with LIKE 'sub:%'. Writing a real reference on settlement
        // would make the sale permanently unfindable by its own lookup, so the reference passed to
        // the service is null on purpose - and settle() only writes one when it is not null.
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);
        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 150,
            'transaction_reference' => 'sub:abc123',
            'email' => 'sub-buyer@gmail.com',
        ], $ticket, 1);

        $this->postJson_(
            route('invoiceninja.purchase_webhook', ['sale' => UrlUtils::encodeId($sale->id)]),
            ['amount' => 150],
        )->assertOk();

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertSame('sub:abc123', $sale->transaction_reference,
            'the subscription marker must survive settlement or the row cannot be found again');
    }

    public function test_the_event_purchase_webhook_creates_tickets_and_settles_against_the_total_it_derived(): void
    {
        // Payment-link mode again, but here the sale starts with NO SaleTickets: the webhook builds
        // them from the payload's line items, prices the sale from them, and only then settles.
        //
        // This is the ordering the Payfast work restructured. settle() re-reads the row under its own
        // lock, so the derived total has to be persisted inside the transaction first - if it were
        // left only in memory the sale would settle against a stale amount.
        $ticket = $this->createTicket($this->event, [
            'type' => 'General',
            'price' => 150,
            'quantity' => 50,
            'invoiceninja_product_id' => 'prod-1',
        ]);

        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 0,
            'transaction_reference' => 'sub:xyz789',
            'email' => 'link-buyer@gmail.com',
        ]);

        $this->assertSame(0, $sale->saleTickets()->count());

        $this->postJson_(
            route('invoiceninja.event_purchase_webhook', ['event' => UrlUtils::encodeId($this->event->id)]),
            [
                'client' => ['contacts' => [['email' => 'link-buyer@gmail.com']]],
                'line_items' => [['product_id' => 'prod-1', 'quantity' => 2]],
                'amount' => 300,
            ],
        )->assertOk();

        $sale->refresh();

        $this->assertSame(2, (int) $sale->saleTickets()->sum('quantity'));
        $this->assertSame(300.0, (float) $sale->payment_amount);
        $this->assertSame('paid', $sale->status);
        $this->assertSame(
            300.0,
            (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'),
        );
    }

    public function test_the_event_purchase_webhook_parks_a_tampered_invoice_total(): void
    {
        // The payload is third-party input: a dropped line item or inflated discount must not settle
        // the sale for less than the tickets it just created are worth.
        $ticket = $this->createTicket($this->event, [
            'type' => 'General',
            'price' => 150,
            'quantity' => 50,
            'invoiceninja_product_id' => 'prod-1',
        ]);

        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 0,
            'transaction_reference' => 'sub:tampered',
            'email' => 'tamper@gmail.com',
        ]);

        $this->postJson_(
            route('invoiceninja.event_purchase_webhook', ['event' => UrlUtils::encodeId($this->event->id)]),
            [
                'client' => ['contacts' => [['email' => 'tamper@gmail.com']]],
                'line_items' => [['product_id' => 'prod-1', 'quantity' => 2]],
                'amount' => 1,
            ],
        )->assertOk();

        $this->assertSame('amount_mismatch', $sale->fresh()->status);
        $this->assertSame(0.0, (float) AnalyticsEventsDaily::where('event_id', $this->event->id)->sum('revenue'));
    }

    public function test_a_webhook_without_the_shared_secret_settles_nothing(): void
    {
        $ticket = $this->createTicket($this->event, ['type' => 'General', 'price' => 150, 'quantity' => 50]);
        $sale = $this->createSale($this->event, $this->role, [
            'status' => 'unpaid',
            'payment_method' => 'invoiceninja',
            'payment_amount' => 300,
            'transaction_reference' => 'inv-126',
            'email' => 'nosecret@gmail.com',
        ], $ticket, 2);

        $this->call('POST', route('invoiceninja.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['paymentables' => [['invoice_id' => 'inv-126', 'amount' => 300]]]))
            ->assertStatus(400);

        $this->assertSame('unpaid', $sale->fresh()->status);
    }
}
