<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * GHSA-fc2p-5626-5rf4: the Stripe success handlers must not hand out the secret-bearing ticket or
 * gift-card URL unless the Stripe session they were given belongs to that sale or card.
 *
 * The encoded id in a success_url is not secret - a legacy base64 id is enumerable without the app
 * key - and the secret in the landing URL is the only credential the ticket, QR code, wallet and
 * booking routes ask for. Both handlers used to redirect to it on every path: a session belonging
 * to another sale, and any session id at all that made the Stripe call throw (`?session_id=x`).
 *
 * Stripe is faked at its HTTP client, so the real retrieve() runs and nothing leaves the process.
 */
class CheckoutSuccessSecretTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** What the fake Stripe returns for a session retrieve: [body, status]. */
    private array $stripeResponse = ['{}', 200];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.key' => 'sk_test_checkout_success', 'services.stripe_platform.secret' => 'sk_test_checkout_success']);

        $test = $this;
        ApiRequestor::setHttpClient(new class($test) implements ClientInterface
        {
            public function __construct(private CheckoutSuccessSecretTest $test) {}

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
            {
                return [...$this->test->stripeResponse(), []];
            }
        });
    }

    protected function tearDown(): void
    {
        ApiRequestor::setHttpClient(null);

        parent::tearDown();
    }

    public function stripeResponse(): array
    {
        return $this->stripeResponse;
    }

    private function sessionFor(array $metadata): void
    {
        $this->stripeResponse = [json_encode([
            'id' => 'cs_test_1',
            'object' => 'checkout.session',
            'payment_intent' => 'pi_test_1',
            'metadata' => $metadata,
        ]), 200];
    }

    private function stripeRejectsTheSession(): void
    {
        $this->stripeResponse = [json_encode(['error' => [
            'type' => 'invalid_request_error',
            'message' => 'No such checkout.session: x',
        ]]), 404];
    }

    /** @return array{0: Role, 1: Sale} */
    private function unpaidSale(): array
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $sale = $this->createSale($event, $role, ['status' => 'unpaid', 'secret' => strtolower(Str::random(32))]);

        return [$role, $sale];
    }

    private function ticketSuccessUrl(Role $role, Sale $sale, string $sessionId): string
    {
        return route('checkout.success', [
            'subdomain' => $role->subdomain,
            'sale_id' => UrlUtils::encodeId($sale->id),
            'date' => $sale->event_date,
            'session_id' => $sessionId,
        ]);
    }

    private function giftCard(): GiftCard
    {
        $role = $this->createRole($this->createOwner());

        $card = new GiftCard;
        $card->role_id = $role->id;
        $card->code = GiftCard::generateCode();
        $card->secret = strtolower(Str::random(32));
        $card->amount = 50;
        $card->remaining_amount = 50;
        $card->currency_code = 'USD';
        $card->status = 'unpaid';
        $card->payment_method = 'stripe';
        $card->purchaser_name = 'Alice Buyer';
        $card->purchaser_email = 'alice@test.dev';
        $card->recipient_name = 'Bob Recipient';
        $card->recipient_email = 'bob@test.dev';
        $card->save();

        return $card->fresh('role');
    }

    private function giftCardSuccessUrl(GiftCard $card, string $sessionId): string
    {
        return route('gift_card.success', [
            'subdomain' => $card->role->subdomain,
            'gift_card_id' => UrlUtils::encodeId($card->id),
            'session_id' => $sessionId,
        ]);
    }

    public function test_a_session_stripe_rejects_does_not_reveal_the_ticket_secret(): void
    {
        [$role, $sale] = $this->unpaidSale();
        $this->stripeRejectsTheSession();

        $response = $this->get($this->ticketSuccessUrl($role, $sale, 'x'));

        $response->assertRedirect($sale->getEventUrl());
        $this->assertStringNotContainsString($sale->secret, (string) $response->headers->get('Location'));
    }

    public function test_a_session_belonging_to_another_sale_does_not_reveal_the_ticket_secret(): void
    {
        [$role, $sale] = $this->unpaidSale();
        $this->sessionFor(['sale_id' => UrlUtils::encodeId($sale->id + 1000)]);

        $response = $this->get($this->ticketSuccessUrl($role, $sale, 'cs_test_1'));

        $response->assertRedirect($sale->getEventUrl());
        $this->assertStringNotContainsString($sale->secret, (string) $response->headers->get('Location'));
        $this->assertNull($sale->fresh()->transaction_reference);
    }

    public function test_a_verified_session_still_lands_the_buyer_on_their_ticket(): void
    {
        [$role, $sale] = $this->unpaidSale();
        $this->sessionFor(['sale_id' => UrlUtils::encodeId($sale->id)]);

        $response = $this->get($this->ticketSuccessUrl($role, $sale, 'cs_test_1'));

        $response->assertRedirect(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ]));
        $this->assertSame('pi_test_1', $sale->fresh()->transaction_reference);
    }

    public function test_the_ticket_success_page_still_needs_a_session_id(): void
    {
        [$role, $sale] = $this->unpaidSale();

        $this->get(route('checkout.success', [
            'subdomain' => $role->subdomain,
            'sale_id' => UrlUtils::encodeId($sale->id),
            'date' => $sale->event_date,
        ]))->assertNotFound();
    }

    public function test_a_session_stripe_rejects_does_not_reveal_the_gift_card_secret(): void
    {
        $card = $this->giftCard();
        $this->stripeRejectsTheSession();

        $response = $this->get($this->giftCardSuccessUrl($card, 'x'));

        $response->assertRedirect(route('gift_card.purchase', ['subdomain' => $card->role->subdomain]));
        $this->assertStringNotContainsString($card->secret, (string) $response->headers->get('Location'));
    }

    public function test_a_session_belonging_to_another_card_does_not_reveal_the_gift_card_secret(): void
    {
        $card = $this->giftCard();
        $this->sessionFor(['gift_card_id' => UrlUtils::encodeId($card->id + 1000)]);

        $response = $this->get($this->giftCardSuccessUrl($card, 'cs_test_1'));

        $response->assertRedirect(route('gift_card.purchase', ['subdomain' => $card->role->subdomain]));
        $this->assertStringNotContainsString($card->secret, (string) $response->headers->get('Location'));
        $this->assertNull($card->fresh()->transaction_reference);
    }

    public function test_the_gift_card_route_ignores_the_subdomain_it_was_given(): void
    {
        $card = $this->giftCard();
        $other = $this->createRole($this->createOwner());
        $this->stripeRejectsTheSession();

        $url = route('gift_card.success', [
            'subdomain' => $other->subdomain,
            'gift_card_id' => UrlUtils::encodeId($card->id),
            'session_id' => 'x',
        ]);

        $this->get($url)->assertRedirect(route('gift_card.purchase', ['subdomain' => $card->role->subdomain]));
    }

    public function test_a_verified_session_still_lands_the_buyer_on_their_gift_card(): void
    {
        $card = $this->giftCard();
        $this->sessionFor(['gift_card_id' => UrlUtils::encodeId($card->id)]);

        $this->get($this->giftCardSuccessUrl($card, 'cs_test_1'))
            ->assertRedirect($card->getViewUrl());
        $this->assertSame('pi_test_1', $card->fresh()->transaction_reference);
    }
}
