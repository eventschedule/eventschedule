<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A multi-event order settled through Stripe.
 *
 * The fixture here is the shape an ORDINARY cart writes: one sale per event with group_id null.
 * SaleOrderCascadeTest builds its order primary as a group primary too (group_id = its own id),
 * which is what an individual-tickets leg looks like - and that difference is exactly what hid
 * these bugs. isPrimarySale() is false on an ordinary cart's order primary, so the webhook's
 * "only overwrite payment_amount for ungrouped sales" guard did not hold and the whole order's
 * total was written onto the one leg that anchored it.
 */
class StripeMultiEventOrderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_multi_event_order';

    private string $ownerApiKey = '';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.key' => 'sk_test_multi_event_order',
            'services.stripe.webhook_secret' => self::WEBHOOK_SECRET,
        ]);
    }

    /**
     * Two unpaid legs of one order, priced $50 and $30, the way checkout() writes them.
     *
     * @return array{0: Sale, 1: Sale, 2: \App\Models\Event, 3: \App\Models\Event}
     */
    private function createTwoLegOrder(float $legA = 50, float $legB = 30, string $email = 'cart-buyer@gmail.com'): array
    {
        $owner = $this->createOwner();
        $this->ownerApiKey = $this->apiKey($owner);
        $role = $this->createRole($owner);

        $base = ['tickets_enabled' => true, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD'];
        $eventA = $this->createEvent($role, $base);
        $eventB = $this->createEvent($role, $base);

        $saleA = $this->createSale($eventA, $role, [
            'email' => $email, 'payment_amount' => $legA, 'payment_method' => 'stripe', 'status' => 'unpaid',
        ], $this->createTicket($eventA, ['price' => $legA, 'quantity' => 50]));

        $saleB = $this->createSale($eventB, $role, [
            'email' => $email, 'payment_amount' => $legB, 'payment_method' => 'stripe', 'status' => 'unpaid',
        ], $this->createTicket($eventB, ['price' => $legB, 'quantity' => 50]));

        // group_id stays null on both: only an individual-tickets leg sets it.
        $saleA->order_id = $saleA->id;
        $saleA->saveQuietly();
        $saleB->order_id = $saleA->id;
        $saleB->saveQuietly();

        return [$saleA->fresh(), $saleB->fresh(), $eventA, $eventB];
    }

    /**
     * Post a genuinely signed payment_intent.succeeded.
     *
     * The secret is ours in the test config, so signing the payload here is the same work Stripe
     * does - and it exercises webhook() through its signature check rather than reaching past it.
     */
    private function payOrder(Sale $primary, float $amount): void
    {
        $payload = json_encode([
            'id' => 'evt_'.$primary->id,
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_'.$primary->id,
                'object' => 'payment_intent',
                'amount' => (int) round($amount * 100),
                'currency' => 'usd',
                'metadata' => ['sale_id' => UrlUtils::encodeId($primary->id)],
            ]],
        ]);

        $timestamp = time();

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, self::WEBHOOK_SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();
    }

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(\App\Models\User $user): string
    {
        $raw = 'testapikey_'.\Illuminate\Support\Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = \Illuminate\Support\Facades\Hash::make($raw);
        $user->save();

        return $raw;
    }

    /**
     * Drive an owner action through the API rather than the AP route: both run the same
     * HandlesSaleStatusActions methods, and the AP route sits behind the app_subdomain middleware,
     * which a test request cannot satisfy.
     */
    private function ownerAction(Sale $sale, string $action): void
    {
        $this->withHeaders(['X-API-Key' => $this->ownerApiKey])
            ->putJson('/api/sales/'.UrlUtils::encodeId($sale->id), ['action' => $action])
            ->assertStatus(200);
    }

    private function revenueFor(int $eventId): float
    {
        return (float) (AnalyticsEventsDaily::where('event_id', $eventId)
            ->where('date', now()->toDateString())
            ->value('revenue') ?? 0);
    }

    public function test_paying_an_order_leaves_each_legs_own_amount_alone(): void
    {
        [$legA, $legB] = $this->createTwoLegOrder();

        $this->payOrder($legA, 80);

        $this->assertSame('paid', $legA->fresh()->status);
        $this->assertSame('paid', $legB->fresh()->status);

        // The charge was 80 for the pair. Writing it onto leg A would make the order look like 110.
        $this->assertEqualsWithDelta(50.0, (float) $legA->fresh()->payment_amount, 0.001,
            'the anchoring leg must keep its own price, not the whole order total');
        $this->assertEqualsWithDelta(30.0, (float) $legB->fresh()->payment_amount, 0.001);
        $this->assertEqualsWithDelta(80.0, $legA->fresh()->orderTotalPayment(), 0.001,
            'the order must still reconcile to what Stripe charged');
    }

    public function test_paying_an_order_credits_each_event_its_own_revenue(): void
    {
        [$legA, , $eventA, $eventB] = $this->createTwoLegOrder();

        $this->payOrder($legA, 80);

        // analytics_events_daily is keyed by event, so the order total cannot be posted to one.
        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001,
            'the second event must be credited its own leg, not left at zero');
    }

    public function test_refunding_an_order_gives_back_every_events_revenue(): void
    {
        [$legA, , $eventA, $eventB] = $this->createTwoLegOrder();

        $this->payOrder($legA, 80);

        $this->ownerAction($legA, 'refund');

        $this->assertSame('refunded', $legA->fresh()->status);
        $this->assertSame('refunded', Sale::where('order_id', $legA->id)->where('id', '!=', $legA->id)->first()->status);

        // The refund cascades to both legs, so both events' revenue has to come back off. Posting
        // the whole order to one event on the way in and taking one leg off on the way out left a
        // permanent residue.
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventB->id), 0.001);
    }

    public function test_a_leg_released_before_payment_is_not_revived(): void
    {
        [$legA, $legB, $eventA, $eventB] = $this->createTwoLegOrder();

        // The organizer cancels one leg while the order is still unpaid - allowed, because the
        // non-primary block only covers GROUPED sales and a cart leg has no group.
        $this->ownerAction($legB, 'cancel');

        $this->assertSame('cancelled', $legB->fresh()->status);

        // Cancelling released leg B's seats. The paid cascade must not hand them back out: the raw
        // update does not re-take inventory, so flipping it to paid oversells the event.
        $this->payOrder($legA, 80);

        $this->assertSame('paid', $legA->fresh()->status);
        $this->assertSame('cancelled', $legB->fresh()->status,
            'a released leg must stay released when the rest of the order is paid');
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventB->id), 0.001,
            'a cancelled leg earns its event nothing');
        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
    }

    public function test_a_single_event_stripe_sale_still_takes_the_webhook_amount(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD']);

        // An ordinary ungrouped sale: the webhook is still the authority on what was charged, so
        // the order-primary carve-out must not have widened into this path.
        $sale = $this->createSale($event, $role, [
            'email' => 'solo-buyer@gmail.com', 'payment_amount' => 25, 'payment_method' => 'stripe', 'status' => 'unpaid',
        ], $this->createTicket($event, ['price' => 25, 'quantity' => 50]));

        $this->payOrder($sale, 25);

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertEqualsWithDelta(25.0, (float) $sale->payment_amount, 0.001);
        $this->assertEqualsWithDelta(25.0, $this->revenueFor($event->id), 0.001);
    }

    public function test_a_refund_nets_off_the_amount_the_settlement_credited(): void
    {
        $owner = $this->createOwner();
        $this->ownerApiKey = $this->apiKey($owner);
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD']);

        // sales.payment_amount is decimal(13,3), and our totals are summed from decimals - a fixed
        // volume discount over a multi-quantity line lands on a fractional cent routinely. Stripe can
        // only ever charge whole cents, so it reports 25.00 against our stored 25.004.
        $sale = $this->createSale($event, $role, [
            'email' => 'solo-buyer@gmail.com', 'payment_amount' => 25.004, 'payment_method' => 'stripe', 'status' => 'unpaid',
        ], $this->createTicket($event, ['price' => 25.004, 'quantity' => 50]));

        // Inside the reconciliation tolerance, so this settles rather than being flagged - which is
        // exactly why the tolerance exists.
        //
        // For an ungrouped sale the gateway's figure is then written onto payment_amount, and that
        // stored value is what a later refund reads back. So the credit has to be the same number, or
        // the two never cancel and every refunded sale leaves residue on the event's revenue.
        $this->payOrder($sale, 25.00);

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertEqualsWithDelta(25.0, (float) $sale->payment_amount, 0.0005);
        $this->assertEqualsWithDelta(25.0, $this->revenueFor($event->id), 0.0005,
            'the credit must be the amount that ends up stored, not the one it replaced');

        $this->ownerAction($sale, 'refund');

        $this->assertSame('refunded', $sale->fresh()->status);
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($event->id), 0.001,
            'a refund must return the event to zero, leaving no residue');
    }
}
