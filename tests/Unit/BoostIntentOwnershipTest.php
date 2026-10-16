<?php

namespace Tests\Unit;

use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use Stripe\PaymentIntent;
use Tests\TestCase;

/**
 * The boost and promotion forms take a card payment before the request that makes the campaign,
 * and that request names the PaymentIntent. The id proves nothing about whose payment it is, so
 * only an intent that form made for that event, for that person, is ever given back or cancelled
 * from a request (abandonPaymentIntent()), only a campaign's own is taken or refunded through it
 * (intentBelongsTo()), and money already given back never pays for anything (chargeWasRefunded()).
 */
class BoostIntentOwnershipTest extends TestCase
{
    public function test_only_the_forms_own_intent_for_this_event_and_person_is_given_back(): void
    {
        $this->assertTrue(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '42', 'user_id' => '7'], 'boost', 42, 7));
        $this->assertTrue(BoostBillingService::intentIsFor(['type' => 'promo', 'event_id' => 42, 'user_id' => 7], 'promo', 42, 7));

        // Somebody else's checkout for the same event.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '42', 'user_id' => '8'], 'boost', 42, 7));
        // Made before the forms said who made it: nobody can have it given back from a request.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '42'], 'boost', 42, 7));
        // Another event's.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '43', 'user_id' => '7'], 'boost', 42, 7));
        // A boost partner's monthly fee, which names no event.
        $this->assertFalse(BoostBillingService::intentIsFor(['boost_statement_id' => '9'], 'boost', 42, 7));
        // The other form's.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'promo', 'event_id' => '42', 'user_id' => '7'], 'boost', 42, 7));
        $this->assertFalse(BoostBillingService::intentIsFor([], 'boost', 42, 7));
    }

    public function test_a_campaign_takes_only_its_own_kind_of_intent_for_its_event_and_person(): void
    {
        $promotion = (new BoostCampaign)->forceFill(['channel' => 'network', 'event_id' => 42, 'user_id' => 7]);
        $boost = (new BoostCampaign)->forceFill(['channel' => 'meta', 'event_id' => 42, 'user_id' => 7]);

        $this->assertTrue(BoostBillingService::intentBelongsTo(['type' => 'promo', 'event_id' => '42', 'user_id' => '7'], $promotion));
        $this->assertTrue(BoostBillingService::intentBelongsTo(['type' => 'boost', 'event_id' => '42', 'user_id' => '7'], $boost));
        // Made before the forms said who made it: still the campaign's.
        $this->assertTrue(BoostBillingService::intentBelongsTo(['type' => 'promo', 'event_id' => '42'], $promotion));

        // Somebody else's intent posted with this campaign's submit: never taken, refunded or cancelled.
        $this->assertFalse(BoostBillingService::intentBelongsTo(['type' => 'promo', 'event_id' => '42', 'user_id' => '8'], $promotion));
        $this->assertFalse(BoostBillingService::intentBelongsTo(['type' => 'promo', 'event_id' => '43', 'user_id' => '7'], $promotion));
        // A boost partner's fee, or the other form's payment for the same event.
        $this->assertFalse(BoostBillingService::intentBelongsTo(['boost_statement_id' => '9'], $promotion));
        $this->assertFalse(BoostBillingService::intentBelongsTo(['type' => 'boost', 'event_id' => '42', 'user_id' => '7'], $promotion));
    }

    public function test_a_payment_given_back_in_any_part_is_seen_on_its_charge(): void
    {
        $intent = fn (array $charge) => PaymentIntent::constructFrom([
            'id' => 'pi_1', 'object' => 'payment_intent', 'status' => 'succeeded',
            'latest_charge' => ['id' => 'ch_1', 'object' => 'charge'] + $charge,
        ]);

        $this->assertFalse(BoostBillingService::chargeWasRefunded($intent(['amount_refunded' => 0, 'refunded' => false])));
        $this->assertTrue(BoostBillingService::chargeWasRefunded($intent(['amount_refunded' => 6000, 'refunded' => true])));
        $this->assertTrue(BoostBillingService::chargeWasRefunded($intent(['amount_refunded' => 100, 'refunded' => false])));
    }
}
