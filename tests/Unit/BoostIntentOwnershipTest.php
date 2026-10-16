<?php

namespace Tests\Unit;

use App\Services\BoostBillingService;
use PHPUnit\Framework\TestCase;

/**
 * A refused boost or promotion gives back the card payment its form made
 * (BoostBillingService::abandonPaymentIntent()). The intent id comes from the request, so only an
 * intent that form made for that event is ever given back.
 */
class BoostIntentOwnershipTest extends TestCase
{
    public function test_only_the_forms_own_intent_for_this_event_is_given_back(): void
    {
        $this->assertTrue(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '42'], 'boost', 42));
        $this->assertTrue(BoostBillingService::intentIsFor(['type' => 'promo', 'event_id' => 42], 'promo', 42));

        // Another advertiser's boost of another event.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'boost', 'event_id' => '43'], 'boost', 42));
        // A boost partner's monthly fee, which names no event: never refunded from a boost form.
        $this->assertFalse(BoostBillingService::intentIsFor(['boost_statement_id' => '9'], 'boost', 42));
        // The other form's intent for the same event.
        $this->assertFalse(BoostBillingService::intentIsFor(['type' => 'promo', 'event_id' => '42'], 'boost', 42));
        $this->assertFalse(BoostBillingService::intentIsFor([], 'boost', 42));
    }
}
