<?php

namespace Tests\Unit;

use App\Models\SaleInstallment;
use App\Services\InstallmentService;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pure arithmetic coverage for the installment split. No DB.
 *
 * The class of bug this guards against is the one that already bit the gift-card Stripe line
 * items: a float division that does not re-sum to the total, so the buyer is charged a cent more
 * or less than they agreed to. Everything here works in the smallest currency unit for that
 * reason, and these tests assert the sum exactly rather than approximately.
 */
class InstallmentScheduleTest extends TestCase
{
    private function service(): InstallmentService
    {
        return new InstallmentService;
    }

    /**
     * MoneyUtils::decimalsFor() only ever returns 0 or 2, so those are the only two shapes that
     * exist in this app. There is deliberately no 3-decimal case.
     */
    public static function currencyProvider(): array
    {
        return [
            'two-decimal USD' => ['USD'],
            'two-decimal EUR' => ['EUR'],
            'zero-decimal JPY' => ['JPY'],
        ];
    }

    /**
     * @dataProvider currencyProvider
     */
    public function test_parts_always_resum_to_the_total(string $currency): void
    {
        $service = $this->service();
        $multiplier = MoneyUtils::getSmallestUnitMultiplier($currency);

        // Totals chosen to hit every remainder class for the counts below.
        $totals = [1000, 999.99, 100, 33.33, 0.10, 1234.56, 7];

        foreach ($totals as $total) {
            for ($count = 2; $count <= 12; $count++) {
                $schedule = $service->buildSchedule((float) $total, $count, $currency);

                $this->assertCount($count, $schedule);

                // Compare in smallest units. Summing floats and comparing to a float is exactly
                // the imprecision this method exists to avoid, so the assertion must not
                // reintroduce it.
                $sumUnits = array_sum(array_map(
                    fn ($part) => (int) round($part['amount'] * $multiplier),
                    $schedule
                ));

                $this->assertSame(
                    (int) round($total * $multiplier),
                    $sumUnits,
                    "{$total} {$currency} split {$count} ways did not re-sum"
                );
            }
        }
    }

    public function test_remainder_lands_on_the_first_installment(): void
    {
        // 1000.00 / 3 = 333.333..., so one extra cent has to go somewhere.
        $schedule = $this->service()->buildSchedule(1000.00, 3, 'USD');

        $this->assertSame(333.34, $schedule[0]['amount']);
        $this->assertSame(333.33, $schedule[1]['amount']);
        $this->assertSame(333.33, $schedule[2]['amount']);
    }

    public function test_zero_decimal_currency_never_produces_fractional_units(): void
    {
        $schedule = $this->service()->buildSchedule(100.0, 3, 'JPY');

        $this->assertSame(34.0, $schedule[0]['amount']);
        $this->assertSame(33.0, $schedule[1]['amount']);
        $this->assertSame(33.0, $schedule[2]['amount']);

        foreach ($schedule as $part) {
            $this->assertSame(
                (float) (int) $part['amount'],
                $part['amount'],
                'A zero-decimal currency must not produce a fractional amount'
            );
        }
    }

    /**
     * Carbon's plain addMonths() turns 31 January into 3 March, which would skip February and
     * bunch two payments into March. The schedule must clamp to the last day of the month
     * instead, and must not accumulate drift: month 2 is 31 March, not 28 March.
     */
    public function test_month_end_dates_clamp_without_drifting(): void
    {
        $schedule = $this->service()->buildSchedule(
            400.00,
            4,
            'USD',
            Carbon::createFromFormat('Y-m-d', '2026-01-31', 'UTC')->startOfDay()
        );

        $this->assertSame('2026-01-31', $schedule[0]['due_at']->toDateString());
        $this->assertSame('2026-02-28', $schedule[1]['due_at']->toDateString());
        $this->assertSame('2026-03-31', $schedule[2]['due_at']->toDateString());
        $this->assertSame('2026-04-30', $schedule[3]['due_at']->toDateString());
    }

    public function test_first_installment_is_due_immediately(): void
    {
        $start = Carbon::createFromFormat('Y-m-d', '2026-08-11', 'UTC')->startOfDay();
        $schedule = $this->service()->buildSchedule(1200.00, 4, 'EUR', $start);

        $this->assertSame('2026-08-11', $schedule[0]['due_at']->toDateString());
        $this->assertSame('2026-09-11', $schedule[1]['due_at']->toDateString());
        $this->assertSame('2026-10-11', $schedule[2]['due_at']->toDateString());
        $this->assertSame('2026-11-11', $schedule[3]['due_at']->toDateString());
    }

    public function test_sequences_are_one_based_and_contiguous(): void
    {
        $schedule = $this->service()->buildSchedule(600.00, 6, 'USD');

        $this->assertSame([1, 2, 3, 4, 5, 6], array_column($schedule, 'sequence'));
    }

    /**
     * The decline ladder, pinned by the DAY the buyer actually reaches each attempt.
     *
     * The user guide states a number, so the code and the copy have to be checkable against one
     * another. This previously read 3 attempts over 4 days while the guide promised three retries
     * over about a week, and RETRY_DAYS[2] was unreachable - the constants disagreed and nothing
     * noticed.
     */
    public function test_the_decline_ladder_spans_nine_days(): void
    {
        // The invariant a constant expression cannot enforce: one attempt, then one per backoff.
        $this->assertSame(
            1 + count(SaleInstallment::RETRY_DAYS),
            SaleInstallment::MAX_ATTEMPTS,
            'MAX_ATTEMPTS must stay in step with RETRY_DAYS'
        );

        $days = [];
        $day = 0;

        for ($attempt = 1; $attempt <= SaleInstallment::MAX_ATTEMPTS; $attempt++) {
            $days[] = $day;

            if ($attempt >= SaleInstallment::MAX_ATTEMPTS) {
                break;
            }

            $day += SaleInstallment::RETRY_DAYS[min($attempt, count(SaleInstallment::RETRY_DAYS)) - 1];
        }

        // Charged on the due date, then three retries: +1, +3, +5.
        $this->assertSame([0, 1, 4, 9], $days);
        $this->assertSame(9, end($days), 'The guide promises nine days');
    }

    /**
     * Mirrors the guard already in TicketController::priceSaleLeg(): Stripe refuses charges below
     * roughly 50 smallest units, so the floor is currency-relative, not a hardcoded 50 cents.
     */
    public function test_minimum_charge_is_currency_relative(): void
    {
        $service = $this->service();

        $this->assertSame(0.5, $service->minimumChargeAmount('USD'));
        $this->assertSame(0.5, $service->minimumChargeAmount('EUR'));
        $this->assertSame(50.0, $service->minimumChargeAmount('JPY'));
    }
}
