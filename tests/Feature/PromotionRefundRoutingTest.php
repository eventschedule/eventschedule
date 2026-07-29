<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use App\Services\PromotionBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regression lock on the over-refund bug.
 *
 * The choice between a full and a partial refund was written out by hand at EIGHT call
 * sites (BoostController::cancel, the Event and Role deleting hooks, EventController,
 * ProfileController, RoleController, and the two API delete paths). Every one of them read
 * actual_spend - which network campaigns never populate, since their delivered spend lives
 * in spent_micros - so every one of them would have issued a FULL refund to an advertiser
 * whose impressions had already been served.
 *
 * The decision now lives once, in BoostBillingService::refundOnCancellation(), so these
 * tests cover all eight sites by covering the one thing they now all call.
 */
class PromotionRefundRoutingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * A network campaign as PromotionController::store() actually creates one.
     *
     * markup_rate matters: the column defaults to 0.2000 for the Meta channel, and store()
     * overwrites it with 0 outside $fillable because a network promotion has no external ad
     * spend to mark up. A fixture that skipped that step would compute every refund 20% high.
     */
    private function networkCampaign(array $attrs = []): BoostCampaign
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create(array_merge([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 100,
            'total_charged' => 100,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(100),
        ], $attrs));

        $campaign->markup_rate = 0;
        $campaign->save();

        return $campaign->fresh();
    }

    public function test_delivered_spend_is_mirrored_onto_actual_spend(): void
    {
        $campaign = $this->networkCampaign();

        // 30 dollars' worth of impressions delivered.
        $campaign->forceFill(['spent_micros' => PromotionBillingService::toMicros(30)])->save();

        app(BoostBillingService::class)->syncNetworkSpend($campaign);

        $this->assertSame(30.0, (float) $campaign->fresh()->actual_spend);
    }

    public function test_a_network_campaign_with_delivered_spend_takes_the_partial_refund_path(): void
    {
        // Stripe-funded: credit-funded campaigns take refundCreditRemainder() instead, which is
        // covered separately below.
        $campaign = $this->networkCampaign(['stripe_payment_intent_id' => 'pi_test_partial']);
        $campaign->forceFill(['spent_micros' => PromotionBillingService::toMicros(30)])->save();

        // Prove which branch is chosen without reaching Stripe: refundUnspent() bails early
        // when there is no payment intent, refundFull() does the same, so assert on the
        // observable precondition instead - actual_spend being populated is exactly what
        // makes the ternary pick the partial path.
        $spy = new class extends BoostBillingService
        {
            public ?string $chose = null;

            public function refundUnspent($campaign): bool
            {
                $this->chose = 'unspent';

                return true;
            }

            public function refundFull($campaign): bool
            {
                $this->chose = 'full';

                return true;
            }
        };

        $spy->refundOnCancellation($campaign);

        $this->assertSame('unspent', $spy->chose, 'An advertiser whose impressions were served must not be refunded in full.');
    }

    public function test_a_network_campaign_with_no_delivery_is_refunded_in_full(): void
    {
        // Stripe-funded, spent_micros defaults to 0.
        $campaign = $this->networkCampaign(['stripe_payment_intent_id' => 'pi_test_full']);

        $spy = new class extends BoostBillingService
        {
            public ?string $chose = null;

            public function refundUnspent($campaign): bool
            {
                $this->chose = 'unspent';

                return true;
            }

            public function refundFull($campaign): bool
            {
                $this->chose = 'full';

                return true;
            }
        };

        $spy->refundOnCancellation($campaign);

        $this->assertSame('full', $spy->chose);
    }

    /**
     * A wallet purchase must get back only what was never delivered.
     *
     * settlePayment() nulls stripe_payment_intent_id for credit purchases, so every
     * credit-funded campaign took BoostController::cancel()'s credit branch - which returned
     * total_charged in full without subtracting spend. Run a campaign to near-exhaustion,
     * cancel, and the whole budget came back.
     */
    public function test_cancelling_a_credit_paid_campaign_refunds_only_the_undelivered_portion(): void
    {
        $campaign = $this->networkCampaign([
            'stripe_payment_intent_id' => null,
            'billing_status' => 'charged',
            'total_charged' => 100,
            'user_budget' => 100,
        ]);

        $campaign->role->forceFill(['boost_credit' => 0])->save();
        $campaign->forceFill(['spent_micros' => PromotionBillingService::toMicros(90)])->save();

        app(BoostBillingService::class)->refundOnCancellation($campaign);

        $this->assertSame(10.0, (float) $campaign->role->fresh()->boost_credit, 'Only the undelivered $10 may return.');
        $this->assertSame('partially_refunded', $campaign->fresh()->billing_status);
    }

    public function test_cancelling_an_undelivered_credit_campaign_refunds_everything(): void
    {
        $campaign = $this->networkCampaign([
            'stripe_payment_intent_id' => null,
            'billing_status' => 'charged',
            'total_charged' => 100,
            'user_budget' => 100,
        ]);

        $campaign->role->forceFill(['boost_credit' => 0])->save();

        app(BoostBillingService::class)->refundOnCancellation($campaign);

        $this->assertSame(100.0, (float) $campaign->role->fresh()->boost_credit);
        $this->assertSame('refunded', $campaign->fresh()->billing_status);
    }

    public function test_a_credit_refund_cannot_be_claimed_twice(): void
    {
        $campaign = $this->networkCampaign([
            'stripe_payment_intent_id' => null,
            'billing_status' => 'charged',
            'total_charged' => 100,
            'user_budget' => 100,
        ]);

        $campaign->role->forceFill(['boost_credit' => 0])->save();

        app(BoostBillingService::class)->refundOnCancellation($campaign);
        app(BoostBillingService::class)->refundOnCancellation($campaign->fresh());

        $this->assertSame(100.0, (float) $campaign->role->fresh()->boost_credit, 'A second call must be a no-op.');
    }

    public function test_meta_campaigns_are_unaffected_by_the_normalization(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'billing_status' => 'charged',
            'user_budget' => 100,
            'total_charged' => 120,
            'actual_spend' => 40,
        ]);

        // syncNetworkSpend must be a no-op here: Meta owns actual_spend for this channel.
        app(BoostBillingService::class)->syncNetworkSpend($campaign);

        $this->assertSame(40.0, (float) $campaign->fresh()->actual_spend);
    }

    public function test_a_credit_funded_meta_campaign_refunds_only_the_undelivered_part(): void
    {
        // Meta campaigns reach the credit branch too: BoostController pays from boost_credit
        // and clears the Stripe intent, so this is NOT a network-only path. It used to refund
        // total_charged in FULL after delivery; it now returns the unspent remainder.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->forceFill(['boost_credit' => 0])->save();
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'billing_status' => 'charged',
            'user_budget' => 100,
            // What a credit purchase writes: user_budget * (1 + markup_rate).
            'total_charged' => 120,
            'actual_spend' => 30,
            'stripe_payment_intent_id' => null,
        ]);

        // markup_rate is NOT fillable, so passing it to create() is silently dropped. Set it
        // explicitly rather than relying on the migration default happening to be 0.2 - the
        // assertion below is arithmetic on this value, so it must be the value under test.
        $campaign->markup_rate = 0.2;
        $campaign->save();

        app(BoostBillingService::class)->refundOnCancellation($campaign);

        // (100 - 30) * 1.2 = 84, not the full 120.
        $this->assertSame(84.0, (float) $role->fresh()->boost_credit);
        $this->assertSame('partially_refunded', $campaign->fresh()->billing_status);
    }

    public function test_the_card_refund_amount_is_the_undelivered_budget_plus_its_markup(): void
    {
        // Until this test existed nothing in the suite ever executed refundUnspent()'s
        // arithmetic: both refund-routing tests replace the method with a spy that records a
        // label, and the real one news up a Stripe client, so a regression that refunded
        // total_charged instead of the remainder - or dropped the markup - was invisible.
        $campaign = $this->networkCampaign(['user_budget' => 100, 'actual_spend' => 30]);

        $campaign->markup_rate = 0.2;
        $campaign->save();

        // (100 - 30) * 1.2
        $this->assertSame(84.0, BoostBillingService::unspentRefundAmount($campaign->fresh()));

        $campaign->markup_rate = 0;
        $campaign->save();

        $this->assertSame(70.0, BoostBillingService::unspentRefundAmount($campaign->fresh()));
    }

    public function test_a_fully_delivered_card_campaign_refunds_nothing_and_is_settled(): void
    {
        $campaign = $this->networkCampaign(['user_budget' => 100, 'actual_spend' => 100]);

        $this->assertSame(0.0, BoostBillingService::unspentRefundAmount($campaign->fresh()));

        // Overspend must not produce a negative refund.
        $campaign->forceFill(['actual_spend' => 130])->save();

        $this->assertSame(0.0, BoostBillingService::unspentRefundAmount($campaign->fresh()));
    }

    public function test_the_cents_conversion_does_not_lose_a_penny_to_float_truncation(): void
    {
        // (int) (0.29 * 100) is 28 in PHP. Stripe takes minor units, so a bare cast here would
        // short every affected refund by a cent.
        $this->assertSame(29, BoostBillingService::toCents(0.29));
        $this->assertSame(8400, BoostBillingService::toCents(84.0));
        $this->assertSame(1, BoostBillingService::toCents(0.01));
    }

    public function test_deleting_a_schedule_refunds_a_campaign_still_awaiting_review(): void
    {
        // Every advertiser's FIRST campaigns land in 'pending_review'. That status was in none
        // of the deletion-refund lists, so deleting the schedule stranded the money: the row
        // survived with role_id NULL, could never serve (candidates() inner-joins roles) and
        // could never complete (no exhausted_at, no scheduled_end, no event).
        //
        // Role::deleting gates its refund on hosted && ! is_testing, so the test has to run in
        // the mode a real hosted install runs in. No Stripe call is reached: the campaign is
        // credit-funded, so refundOnCancellation takes the wallet branch.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->forceFill(['boost_credit' => 0])->save();
        $event = $this->createEvent($role);

        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Awaiting review',
            'status' => 'pending_review',
            'moderation_status' => 'pending',
            'billing_status' => 'charged',
            'user_budget' => 40,
            'total_charged' => 40,
        ]);

        $role->delete();

        // The refund now fires at all - previously 'pending_review' matched no deletion list,
        // so billing_status stayed 'charged' and nothing was ever settled or returned.
        //
        // Note: for a CREDIT-funded campaign the returned balance lands on the role row that
        // is about to be deleted, so the advertiser only truly gets money back on the
        // card-funded path. That is tracked separately; it is still strictly better than the
        // previous behaviour, where neither the refund nor the settlement happened.
        $this->assertSame('refunded', BoostCampaign::first()->billing_status);
    }

    public function test_a_fully_delivered_campaign_settles_instead_of_reconciling_forever(): void
    {
        // With nothing left to refund, the old inline block did nothing at all - billing_status
        // stayed 'charged' while the job touched updated_at, so the campaign kept matching
        // SyncPromotions::completeFinishedCampaigns() every 24h and re-sent the completion
        // email and push indefinitely.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Fully delivered',
            'status' => 'completed',
            'billing_status' => 'charged',
            'user_budget' => 20,
            'total_charged' => 20,
        ]);

        // spent_micros is the source of truth for a network campaign - the job's
        // syncNetworkSpend() mirrors it onto actual_spend - and it is not fillable.
        $campaign->forceFill(['spent_micros' => PromotionBillingService::toMicros(20)])->save();

        (new \App\Jobs\ReconcileBoostCampaign($campaign))->handle();

        $this->assertSame('refunded', $campaign->fresh()->billing_status,
            'A settled campaign must leave the charged state so it stops being re-selected.');
        $this->assertSame(0.0, (float) $role->fresh()->boost_credit, 'Nothing was unspent, so nothing is returned.');
    }

    public function test_deleting_an_event_settles_a_completed_campaign_before_the_row_cascades(): void
    {
        // completeFinishedCampaigns() marks a campaign 'completed' immediately but only
        // dispatches its refund 24 hours later, and boost_campaigns.event_id is cascadeOnDelete
        // (as are boost_billing_records). 'completed' was in none of the deletion lists, so
        // deleting the event inside that window erased the campaign, its unspent budget and the
        // whole ledger trail with no refund and no trace.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $campaign = $this->networkCampaign([
            'status' => 'completed',
            'stripe_payment_intent_id' => null,
            'user_budget' => 60,
            'total_charged' => 60,
        ]);

        $campaign->role->forceFill(['boost_credit' => 0])->save();
        $campaign->forceFill(['spent_micros' => PromotionBillingService::toMicros(20)])->save();

        $role = $campaign->role;
        $campaign->event->delete();

        $this->assertSame(40.0, (float) $role->fresh()->boost_credit, 'The undelivered $40 must come back.');
    }

    public function test_a_refund_with_no_schedule_left_to_credit_is_still_settled(): void
    {
        // role_id is nullOnDelete. Bailing without touching billing_status left the campaign
        // 'charged' forever, so both completion queries kept selecting it and the advertiser got
        // a "campaign completed" email every 24 hours indefinitely.
        $campaign = $this->networkCampaign([
            'status' => 'completed',
            'stripe_payment_intent_id' => null,
            'user_budget' => 50,
            'total_charged' => 50,
        ]);

        $campaign->forceFill(['role_id' => null])->save();

        app(BoostBillingService::class)->refundOnCancellation($campaign->fresh());

        $this->assertSame('refunded', $campaign->fresh()->billing_status,
            'A campaign with no wallet to credit must still leave the charged state.');
    }

    public function test_a_cancelled_campaign_leaves_the_review_queue(): void
    {
        // Cancel writes only `status`, never `moderation_status`. Unscoped, the admin queue
        // kept showing it - and approving it would set status back to 'active' on a campaign
        // whose money had already been returned.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Cancelled while pending',
            'status' => 'pending_review',
            'moderation_status' => 'pending',
            'billing_status' => 'charged',
            'user_budget' => 40,
            'total_charged' => 40,
        ]);

        $this->assertSame(1, BoostCampaign::awaitingReview()->count());

        $campaign->update(['status' => 'cancelled']);

        $this->assertSame(0, BoostCampaign::awaitingReview()->count());
    }

    public function test_a_selfhost_cancellation_cannot_mint_credit(): void
    {
        // Selfhost records total_charged = 0 while user_budget keeps the requested amount, so
        // without a clamp a create-then-cancel loop would print boost_credit nobody paid for.
        config(['app.hosted' => false]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->forceFill(['boost_credit' => 0])->save();
        $event = $this->createEvent($role);

        $campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'billing_status' => 'charged',
            'user_budget' => 100,
            'total_charged' => 0,
            'stripe_payment_intent_id' => null,
        ]);

        app(BoostBillingService::class)->refundOnCancellation($campaign);

        $this->assertSame(0.0, (float) $role->fresh()->boost_credit, 'Nothing was paid, so nothing may be returned.');
    }

    public function test_active_boost_campaign_relation_excludes_network_promotions(): void
    {
        // This relation gates the Meta Pixel and StripeController::sendMetaConversion(),
        // which POSTs the buyer's hashed email to Meta. A promotion running on THIS platform
        // must never make either of those fire.
        $campaign = $this->networkCampaign();
        $event = $campaign->event;

        $this->assertNull($event->fresh()->activeBoostCampaign, 'A network promotion must not look like a Meta campaign.');
        $this->assertNotNull($event->fresh()->activeNetworkPromotion);
    }

    public function test_active_boost_campaign_relation_still_finds_meta_campaigns(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'user_budget' => 50,
            'meta_campaign_id' => '123',
        ]);

        $this->assertNotNull($event->fresh()->activeBoostCampaign);
    }
}
