<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which Stripe account a ticket-sale webhook may come from.
 *
 * The sale is found from the payload's own metadata, and the Connect secret only proves the event
 * came from SOME connected account. Without the account match, anyone with a connected account
 * could pay themselves with a victim's sale_id in the metadata and have the victim's sale marked
 * paid - a valid ticket for money the organizer never received. Gift cards and installments already
 * refused this; the sale path did not.
 *
 * The reverse direction is pinned too: a selfhost owner who has a stripe_account_id still checks
 * out on the platform rail (StripeGateway picks Connect only when hosted), so their
 * platform-signed checkout.session.completed must keep settling.
 */
class StripeSaleWebhookAccountTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CONNECT_SECRET = 'whsec_test_sale_account_connect';

    private const PLATFORM_SECRET = 'whsec_test_sale_account_platform';

    private const MERCHANT = 'acct_sale_merchant';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.webhook_secret' => self::CONNECT_SECRET,
            'services.stripe_platform.webhook_secret' => self::PLATFORM_SECRET,
        ]);
    }

    private function unpaidSale(?string $merchantAccount = self::MERCHANT, float $price = 25): Sale
    {
        $owner = $this->createOwner();
        $owner->stripe_account_id = $merchantAccount;
        $owner->save();

        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD']);

        return $this->createSale($event, $role, [
            'payment_amount' => $price, 'payment_method' => 'stripe', 'status' => 'unpaid',
        ], $this->createTicket($event, ['price' => $price, 'quantity' => 50]));
    }

    private function postWebhook(array $event, string $secret): void
    {
        $payload = json_encode(array_merge(['id' => 'evt_test', 'object' => 'event'], $event));
        $timestamp = time();

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();
    }

    private function paymentIntentSucceeded(Sale $sale, ?string $account, string $currency = 'usd', float $amount = 25): void
    {
        $this->postWebhook([
            'type' => 'payment_intent.succeeded',
            'account' => $account,
            'data' => ['object' => [
                'id' => 'pi_'.$sale->id,
                'object' => 'payment_intent',
                'amount' => (int) round($amount * 100),
                'currency' => $currency,
                'metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)],
            ]],
        ], self::CONNECT_SECRET);
    }

    private function checkoutSessionCompleted(Sale $sale, string $secret, ?string $account = null, string $currency = 'usd', float $amount = 25): void
    {
        $this->postWebhook([
            'type' => 'checkout.session.completed',
            'account' => $account,
            'data' => ['object' => [
                'id' => 'cs_'.$sale->id,
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_'.$sale->id,
                'amount_total' => (int) round($amount * 100),
                'currency' => $currency,
                'metadata' => ['sale_id' => UrlUtils::encodeId($sale->id)],
            ]],
        ], $secret);
    }

    public function test_a_payment_from_the_merchants_own_account_settles_the_sale(): void
    {
        $sale = $this->unpaidSale();

        $this->paymentIntentSucceeded($sale, self::MERCHANT);

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_payment_from_another_connected_account_does_not_settle_the_sale(): void
    {
        $sale = $this->unpaidSale();

        $this->paymentIntentSucceeded($sale, 'acct_attacker');

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_connect_payment_for_an_owner_with_no_account_does_not_settle_the_sale(): void
    {
        $sale = $this->unpaidSale(merchantAccount: null);

        $this->paymentIntentSucceeded($sale, 'acct_attacker');

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_payment_in_another_currency_does_not_settle_the_sale(): void
    {
        $sale = $this->unpaidSale();

        // 2500 of the smallest unit either way - only the currency differs.
        $this->paymentIntentSucceeded($sale, self::MERCHANT, 'jpy');

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_connect_signed_checkout_session_for_a_platform_sale_does_not_settle_it(): void
    {
        $sale = $this->unpaidSale(merchantAccount: null);

        $this->checkoutSessionCompleted($sale, self::CONNECT_SECRET, 'acct_attacker');

        $this->assertSame('unpaid', $sale->fresh()->status);
    }

    public function test_a_platform_signed_checkout_session_still_settles_a_platform_sale(): void
    {
        $sale = $this->unpaidSale(merchantAccount: null);

        $this->checkoutSessionCompleted($sale, self::PLATFORM_SECRET);

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_platform_signed_checkout_session_settles_even_when_the_owner_has_an_account_id(): void
    {
        // Selfhost: StripeGateway uses Connect only when hosted, so this owner paid on the
        // platform rail despite having an account id, and the platform-signed event is genuine.
        $sale = $this->unpaidSale();

        $this->checkoutSessionCompleted($sale, self::PLATFORM_SECRET);

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_a_platform_signed_checkout_session_in_another_currency_does_not_settle_the_sale(): void
    {
        $sale = $this->unpaidSale(merchantAccount: null);

        $this->checkoutSessionCompleted($sale, self::PLATFORM_SECRET, currency: 'eur');

        $this->assertSame('unpaid', $sale->fresh()->status);
    }
}
