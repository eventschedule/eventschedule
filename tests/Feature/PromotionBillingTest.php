<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Services\PromotionBillingService;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Budget accounting for on-network promotions.
 *
 * Spending is a compare-and-swap inside a single UPDATE rather than a read-then-write, so
 * two concurrent impressions cannot both see "budget available" and both spend it.
 *
 * PHPUnit runs in one process against one connection, so none of these tests can actually race
 * anything - a sequential drain produces identical numbers whether the predicate lives in the
 * UPDATE or in a separate SELECT. test_the_budget_predicate_lives_inside_the_update_statement()
 * therefore pins the structure directly, which is the only part of the design a single-process
 * suite can defend. The docblock here used to claim a concurrency test existed; it did not.
 */
class PromotionBillingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function campaign(array $attrs = []): BoostCampaign
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        return BoostCampaign::create(array_merge([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 10,
            'total_charged' => 10,
            'pricing_model' => 'cpm',
            // $2.00 per 1000 impressions => 2000 micros per impression.
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ], $attrs));
    }

    public function test_cpm_impressions_cost_exactly_a_thousandth_of_the_rate(): void
    {
        $campaign = $this->campaign();
        $billing = app(PromotionBillingService::class);

        for ($i = 0; $i < 1000; $i++) {
            $this->assertTrue($billing->chargeImpression($campaign));
        }

        // 1000 impressions at a $2.00 CPM is exactly $2.00 - no drift.
        $this->assertSame(PromotionBillingService::toMicros(2.00), (int) $campaign->fresh()->spent_micros);
        $this->assertSame(2.0, $campaign->fresh()->spentAmount());
    }

    public function test_spending_can_never_exceed_the_budget(): void
    {
        // $10 budget at $2 CPM = 2000 micros each => exactly 5000 impressions available.
        $campaign = $this->campaign();
        $billing = app(PromotionBillingService::class);

        $charged = 0;
        for ($i = 0; $i < 5200; $i++) {
            if ($billing->chargeImpression($campaign)) {
                $charged++;
            }
        }

        $this->assertSame(5000, $charged, 'The campaign must deliver exactly the impressions its budget buys.');

        $fresh = $campaign->fresh();
        $this->assertLessThanOrEqual((int) $fresh->budget_micros, (int) $fresh->spent_micros);
        $this->assertSame(0, $fresh->remainingMicros());
    }

    public function test_exhaustion_completes_the_campaign_exactly_once(): void
    {
        $campaign = $this->campaign(['budget_micros' => PromotionBillingService::toMicros(0.004)]);
        $billing = app(PromotionBillingService::class);

        $this->assertTrue($billing->chargeImpression($campaign));
        $this->assertTrue($billing->chargeImpression($campaign));

        // Budget gone: further impressions are refused and the campaign retires.
        $this->assertFalse($billing->chargeImpression($campaign));

        $fresh = $campaign->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertNotNull($fresh->exhausted_at);

        $firstExhaustedAt = $fresh->exhausted_at;

        // Repeated discovery of the exhaustion must not re-transition or move the timestamp.
        $billing->chargeImpression($campaign);
        $this->assertEquals($firstExhaustedAt, $campaign->fresh()->exhausted_at);
    }

    /**
     * exhausted_at must fire on the debit that actually empties the budget, not the one before.
     *
     * MySQL evaluates single-table SET assignments left to right, with later assignments seeing
     * values earlier ones just wrote. With spent_micros assigned first, the IF that sets
     * exhausted_at read the already-incremented value and retired the campaign one unit early -
     * stranding a unit of paid-for budget that the completion path would then never refund.
     */
    public function test_exhaustion_is_flagged_on_the_last_affordable_debit_not_the_one_before(): void
    {
        // Exactly three impressions' worth of budget.
        $campaign = $this->campaign(['budget_micros' => PromotionBillingService::toMicros(0.006)]);
        $billing = app(PromotionBillingService::class);

        $this->assertTrue($billing->chargeImpression($campaign));
        $this->assertNull($campaign->fresh()->exhausted_at, 'One of three: not exhausted.');

        $this->assertTrue($billing->chargeImpression($campaign));
        $this->assertNull($campaign->fresh()->exhausted_at, 'Two of three: still one impression left to sell.');

        $this->assertTrue($billing->chargeImpression($campaign));
        $this->assertNotNull($campaign->fresh()->exhausted_at, 'Three of three: now exhausted.');

        // And the whole budget was actually delivered, not left stranded.
        $this->assertSame(0, $campaign->fresh()->remainingMicros());
    }

    public function test_the_budget_predicate_lives_inside_the_update_statement(): void
    {
        // The overspend guarantee rests entirely on the budget check and the increment being
        // ONE statement, so InnoDB's row lock serialises them. A sequential test cannot tell
        // that apart from a SELECT-then-UPDATE, so capture the SQL and assert the shape.
        $campaign = $this->campaign();

        $statements = [];
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$statements) {
            $statements[] = $query->sql;
        });

        app(PromotionBillingService::class)->chargeImpression($campaign);

        $debit = collect($statements)->first(fn ($sql) => str_contains($sql, 'spent_micros = spent_micros +'));

        $this->assertNotNull($debit, 'The debit must be an UPDATE that increments in place.');
        $this->assertStringContainsString('spent_micros + ? <= budget_micros', $debit,
            'The budget predicate must be in the same statement as the increment, not a prior SELECT.');
        $this->assertStringStartsWith('UPDATE', ltrim($debit));
    }

    public function test_a_budget_that_is_not_a_whole_multiple_of_the_unit_cost_still_exhausts(): void
    {
        // A remainder smaller than one impression used to leave exhausted_at null forever: the
        // campaign stayed in the candidate pool, and every time it won the weighted roll the
        // charge failed and flushed the shared candidate cache for the entire install.
        $campaign = $this->campaign([
            'unit_rate_micros' => 2_370_000,          // $2.37 CPM -> 2370 micros per impression
            'budget_micros' => 5_000_000,             // not a multiple of 2370
        ]);

        $billing = app(PromotionBillingService::class);

        while ($billing->chargeImpression($campaign->fresh())) {
            // drain
        }

        $fresh = $campaign->fresh();

        $this->assertNotNull($fresh->exhausted_at, 'A sub-unit remainder must still retire the campaign.');
        $this->assertLessThan(2370, $fresh->budget_micros - $fresh->spent_micros, 'Remainder is under one impression.');

        // Setting exhausted_at was only half of it. The two consumers have to act on it, or the
        // campaign stays 'active', stays in the candidate pool, and flushes the shared snapshot
        // on every roll it wins and cannot pay for. This is what the first version missed.
        $this->assertSame('completed', $fresh->status, 'An exhausted campaign must retire.');

        $this->assertEmpty(
            collect(app(PromotionService::class)->candidates())->firstWhere('id', $campaign->id),
            'An exhausted campaign must leave the candidate pool.'
        );
    }

    public function test_cpc_campaigns_are_not_billed_for_impressions(): void
    {
        $campaign = $this->campaign([
            'pricing_model' => 'cpc',
            'unit_rate_micros' => PromotionBillingService::toMicros(0.25),
        ]);
        $billing = app(PromotionBillingService::class);

        $this->assertTrue($billing->chargeImpression($campaign));
        $this->assertTrue($billing->chargeImpression($campaign));

        // Nothing spent - which is also what keeps the render path free of writes for CPC.
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);

        $this->assertTrue($billing->chargeClick($campaign));
        $this->assertSame(PromotionBillingService::toMicros(0.25), (int) $campaign->fresh()->spent_micros);
    }

    public function test_cpm_clicks_are_free_because_the_impression_already_paid(): void
    {
        $campaign = $this->campaign();
        $billing = app(PromotionBillingService::class);

        $this->assertTrue($billing->chargeClick($campaign));
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_a_paused_campaign_cannot_be_charged(): void
    {
        $campaign = $this->campaign(['status' => 'paused']);

        $this->assertFalse(app(PromotionBillingService::class)->chargeImpression($campaign));
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_a_meta_campaign_is_never_charged_through_this_path(): void
    {
        // The CAS is scoped to channel='network', so a Meta campaign cannot be debited even
        // if something managed to route one here.
        $campaign = $this->campaign(['channel' => 'meta']);

        $this->assertFalse(app(PromotionBillingService::class)->chargeImpression($campaign));
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_micros_conversion_round_trips(): void
    {
        $this->assertSame(2_000_000, PromotionBillingService::toMicros(2.00));
        $this->assertSame(250_000, PromotionBillingService::toMicros(0.25));
        $this->assertSame(2.0, PromotionBillingService::fromMicros(2_000_000));
        $this->assertSame(0.25, PromotionBillingService::fromMicros(250_000));
    }
}
