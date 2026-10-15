<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostSpendDay;
use App\Models\BoostStatement;
use App\Services\BoostStatementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The platform's fee on a partner's ACTUAL monthly Meta spend: summed per month in the ad
 * account's timezone, billed once the month has closed there, corrections carried as
 * adjustments, and small or negative amounts carried forward instead of charged.
 */
class BoostStatementTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private BoostPartner $partner;

    private BoostCampaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config(['app.hosted' => true, 'services.meta.access_token' => '', 'services.stripe_platform.secret' => 'sk_test_x']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $this->partner = new BoostPartner;
        $this->partner->forceFill([
            'user_id' => $owner->id, 'enabled_at' => now(), 'fee_rate' => 0.2,
            'ad_account_currency' => 'USD', 'ad_account_timezone' => 'America/New_York',
            'stripe_customer_id' => 'cus_1', 'stripe_payment_method_id' => 'pm_1',
        ])->save();

        $this->campaign = new BoostCampaign([
            'event_id' => $event->id, 'role_id' => $role->id, 'user_id' => $owner->id,
            'name' => 'Boost', 'status' => 'active', 'user_budget' => 100, 'currency_code' => 'USD', 'billing_status' => 'partner',
        ]);
        $this->campaign->forceFill(['boost_partner_id' => $this->partner->id])->save();
    }

    private function spend(string $day, float $amount): void
    {
        BoostSpendDay::updateOrCreate(['boost_campaign_id' => $this->campaign->id, 'day' => $day], ['spend' => $amount, 'currency' => 'USD']);
    }

    public function test_a_month_is_billed_only_once_it_has_closed_in_the_accounts_timezone(): void
    {
        $this->spend('2026-10-20', 30);
        $service = app(BoostStatementService::class);

        $this->assertSame([], $service->closedUnbilledPeriods($this->partner, Carbon::parse('2026-11-02 23:00', 'America/New_York')));
        // 2026-11-03 04:00 UTC is still Nov 2 in New York.
        $this->assertSame([], $service->closedUnbilledPeriods($this->partner, Carbon::parse('2026-11-03 04:00', 'UTC')));
        $this->assertSame([['2026-10', 'USD']], $service->closedUnbilledPeriods($this->partner, Carbon::parse('2026-11-03 06:00', 'UTC')));
    }

    public function test_a_campaign_spanning_two_months_is_billed_in_both_with_twenty_percent(): void
    {
        $this->spend('2026-10-30', 30);
        $this->spend('2026-10-31', 16);
        $this->spend('2026-11-01', 10);
        $service = app(BoostStatementService::class);

        $october = $service->build($this->partner, '2026-10');
        $this->assertSame(46.0, (float) $october->total_spend);
        $this->assertSame(9.2, (float) $october->total_fee);
        $this->assertSame('pending', $october->status);

        $november = $service->build($this->partner->fresh(), '2026-11');
        $this->assertSame(10.0, (float) $november->total_spend);
        $this->assertSame(2.0, (float) $november->total_fee);
    }

    public function test_a_correction_to_a_billed_month_is_an_adjustment_on_the_next(): void
    {
        $this->spend('2026-10-30', 50);
        $service = app(BoostStatementService::class);
        $service->build($this->partner, '2026-10');

        $this->spend('2026-10-30', 40); // Meta credited 10 of October's spend.
        $this->spend('2026-11-05', 100);
        $november = $service->build($this->partner->fresh(), '2026-11');

        $this->assertSame(-2.0, (float) $november->total_adjustments);
        $this->assertSame(18.0, $november->amountDue());
        $line = $november->lines()->where('period', '2026-10')->first();
        $this->assertSame(-10.0, (float) $line->spend);
    }

    public function test_an_amount_below_the_minimum_charge_is_carried_forward(): void
    {
        $this->spend('2026-10-30', 1); // a 0.20 fee
        $service = app(BoostStatementService::class);

        $october = $service->build($this->partner, '2026-10');
        $this->assertSame('carried', $october->status);
        $this->assertSame(0.2, (float) $this->partner->fresh()->carry_forward);

        $this->spend('2026-11-05', 100);
        $november = $service->build($this->partner->fresh(), '2026-11');
        $this->assertSame(0.2, (float) $november->carried_in);
        $this->assertSame(20.2, $november->amountDue());
        $this->assertSame(0.0, (float) $this->partner->fresh()->carry_forward);
    }

    public function test_an_off_session_decline_that_needs_authentication_waits_for_pay_now_and_keeps_its_intent(): void
    {
        $this->spend('2026-10-30', 50);
        // What Stripe really does off-session: it throws, with the intent it made attached.
        $service = new class extends BoostStatementService
        {
            protected function confirmIntent(BoostStatement $statement, BoostPartner $partner, bool $onSession): \Stripe\PaymentIntent
            {
                throw \Stripe\Exception\CardException::factory('Authentication required', 402, null, [
                    'error' => ['code' => 'authentication_required', 'decline_code' => 'authentication_required',
                        'payment_intent' => ['id' => 'pi_declined', 'object' => 'payment_intent', 'status' => 'requires_payment_method']],
                ], null, 'authentication_required', 'authentication_required');
            }
        };

        $statement = $service->build($this->partner, '2026-10');
        $this->assertSame('action_required', $service->charge($statement));

        $statement = $statement->fresh();
        $this->assertSame('pi_declined', $statement->stripe_payment_intent_id);
        $this->assertSame('skipped', $service->charge($statement));
    }

    public function test_a_statement_with_no_card_is_marked_unpaid_once(): void
    {
        $this->spend('2026-10-30', 50);
        $this->partner->forceFill(['stripe_payment_method_id' => null])->save();
        $service = app(BoostStatementService::class);

        $statement = $service->build($this->partner->fresh(), '2026-10');
        $this->assertSame('failed', $service->charge($statement->fresh()));
        $this->assertSame('no_card', $statement->fresh()->failure_code);
        $this->assertSame('skipped', $service->charge($statement->fresh()));
    }

    public function test_spend_in_two_currencies_is_billed_on_two_statements(): void
    {
        $this->spend('2026-10-10', 40);
        $eur = $this->campaign->replicate();
        $eur->forceFill(['currency_code' => 'EUR', 'boost_partner_id' => $this->partner->id])->save();
        BoostSpendDay::create(['boost_campaign_id' => $eur->id, 'day' => '2026-10-20', 'spend' => 60, 'currency' => 'EUR']);
        $service = app(BoostStatementService::class);

        $periods = $service->closedUnbilledPeriods($this->partner, Carbon::parse('2026-11-05', 'UTC'));
        $this->assertEqualsCanonicalizing([['2026-10', 'USD'], ['2026-10', 'EUR']], $periods);

        $usd = $service->build($this->partner, '2026-10', 'USD');
        $eurStatement = $service->build($this->partner->fresh(), '2026-10', 'EUR');
        $this->assertSame(40.0, (float) $usd->total_spend);
        $this->assertSame(60.0, (float) $eurStatement->total_spend);
    }

    public function test_a_card_that_needs_authentication_is_not_retried_automatically(): void
    {
        $this->spend('2026-10-30', 50);
        $service = new class extends BoostStatementService
        {
            public int $calls = 0;

            protected function confirmIntent(BoostStatement $statement, BoostPartner $partner, bool $onSession): \Stripe\PaymentIntent
            {
                $this->calls++;

                return \Stripe\PaymentIntent::constructFrom(['id' => 'pi_1', 'status' => $onSession ? 'succeeded' : 'requires_action']);
            }
        };

        $statement = $service->build($this->partner, '2026-10');
        $this->assertSame('action_required', $service->charge($statement));
        $this->assertSame('skipped', $service->charge($statement->fresh()));
        $this->assertSame(1, $service->calls);

        // "Pay now" is on-session and settles it.
        $this->assertSame('paid', $service->charge($statement->fresh(), true));
        $this->assertSame('paid', $statement->fresh()->status);
        $this->assertSame(10.0, (float) $statement->fresh()->charged_amount);
    }

    public function test_a_statement_is_never_built_twice_for_a_month(): void
    {
        $this->spend('2026-10-30', 50);
        $service = app(BoostStatementService::class);
        $service->build($this->partner, '2026-10');
        $service->build($this->partner, '2026-10');

        $this->assertSame(1, $this->partner->statements()->count());
    }

    public function test_each_currency_is_billed_only_over_its_own_months(): void
    {
        $this->spend('2026-01-10', 40);
        $this->spend('2026-04-10', 40);
        $eur = $this->campaign->replicate();
        $eur->forceFill(['currency_code' => 'EUR', 'boost_partner_id' => $this->partner->id])->save();
        BoostSpendDay::create(['boost_campaign_id' => $eur->id, 'day' => '2026-09-20', 'spend' => 60, 'currency' => 'EUR']);

        $periods = app(BoostStatementService::class)->closedUnbilledPeriods($this->partner, Carbon::parse('2026-10-05', 'UTC'));

        $eurMonths = collect($periods)->where(1, 'EUR')->pluck(0)->all();
        $usdMonths = collect($periods)->where(1, 'USD')->pluck(0)->all();
        $this->assertSame(['2026-09'], $eurMonths);
        // USD from its first spend to two months after its last, no further.
        $this->assertSame(['2026-01', '2026-02', '2026-03', '2026-04', '2026-05', '2026-06'], $usdMonths);
    }
}
