<?php

namespace Tests\Unit;

use App\Services\GrowthSummary;
use PHPUnit\Framework\TestCase;

/**
 * The headline table app:pull-growth prints. Pure over decoded payloads, so it is tested on
 * hand-built ones - including an older schema missing newer fields.
 */
class GrowthSummaryTest extends TestCase
{
    private function pull(array $overrides = []): array
    {
        return array_replace_recursive([
            'meta' => [
                'schema_version' => 8,
                'generated_at' => '2026-10-15T09:00:00+00:00',
                'partial_month' => ['month' => '2026-10', 'days_elapsed' => 15, 'days_in_month' => 31],
                'notes' => ['note a'],
            ],
            'traffic' => [
                ['month' => '2026-09', 'visitors' => 6400, 'verified_signups' => 124],
                ['month' => '2026-10', 'visitors' => 3000, 'verified_signups' => 60],
            ],
            'funnel' => [
                'cohort_size' => 90,
                'stages' => [
                    ['key' => 'saved_schedule', 'count' => 40],
                    ['key' => 'saved_event', 'count' => 25],
                    ['key' => 'saved_paid_ticket', 'count' => 2],
                    ['key' => 'subscribed', 'count' => 1],
                ],
                'biggest_drop' => ['from_key' => 'saved_event', 'to_key' => 'saved_ticket', 'drop_pct' => 90.9],
            ],
            'monetization' => [
                'billing_subscriptions' => 9,
                'mrr' => 62.5,
                'by_plan_source' => ['admin' => 350],
                'ticket_trials' => ['started' => 4, 'converted' => 1],
                'gmv_by_currency' => [
                    ['currency' => 'USD', 'month' => '2026-09', 'amount' => 3365.0, 'sales' => 40],
                    ['currency' => 'RON', 'month' => '2026-09', 'amount' => 6095.0, 'sales' => 12],
                    ['currency' => 'USD', 'month' => '2026-10', 'amount' => 900.5, 'sales' => 10],
                ],
            ],
            'churn' => ['cancelled' => 3],
            'schedules' => [
                'columns' => ['sid', 'paid_tickets_90d'],
                'rows' => [['s:1', 5], ['s:2', 0], ['s:3', 12]],
            ],
        ], $overrides);
    }

    private function value(array $kpis, string $label)
    {
        foreach ($kpis as $kpi) {
            if ($kpi['label'] === $label) {
                return $kpi['value'];
            }
        }

        $this->fail("no KPI labelled {$label}");
    }

    public function test_it_reads_the_headline_numbers(): void
    {
        $kpis = GrowthSummary::kpis($this->pull());

        $this->assertSame(60, $this->value($kpis, 'Verified signups, 2026-10 (to date)'));
        $this->assertSame(124, $this->value($kpis, 'Verified signups, 2026-09'));
        $this->assertSame(6400, $this->value($kpis, 'Site visitors, 2026-09'));
        $this->assertSame(2, $this->value($kpis, '  made a paid ticket type'));
        $this->assertSame(62.5, $this->value($kpis, 'MRR'));
        $this->assertSame(2, $this->value($kpis, 'Schedules selling (paid ticket in 90 days)'));
        $this->assertSame('4 / 1', $this->value($kpis, 'Selling trials started / converted'));
        $this->assertSame('saved_event -> saved_ticket (90.9% lost)', $this->value($kpis, 'Biggest funnel drop'));
        $this->assertSame(900.5, $this->value($kpis, 'Ticket GMV USD, 2026-10 (to date)'));
        $this->assertSame(6095.0, $this->value($kpis, 'Ticket GMV RON, 2026-09'));
    }

    /** An older pull lacks newer fields: they read as unknown, never as a zero. */
    public function test_a_field_an_older_schema_lacks_is_unknown_not_zero(): void
    {
        $old = $this->pull();
        $old['schedules'] = ['columns' => ['sid'], 'rows' => [['s:1']]];
        unset($old['monetization']['ticket_trials']);

        $kpis = GrowthSummary::kpis($old);

        $this->assertNull($this->value($kpis, 'Schedules selling (paid ticket in 90 days)'));
        $this->assertNull($this->value($kpis, 'Selling trials started / converted'));
    }

    /**
     * The previous pull is read for the CURRENT pull's months, so "this month to date" compares the
     * same month at two moments instead of two different months.
     */
    public function test_compare_lines_up_the_same_months_across_pulls(): void
    {
        $previous = $this->pull([
            'meta' => ['generated_at' => '2026-10-08T09:00:00+00:00', 'partial_month' => ['month' => '2026-10']],
            'traffic' => [1 => ['verified_signups' => 31]],
            'monetization' => ['mrr' => 57.5],
        ]);

        $rows = collect(GrowthSummary::compare($this->pull(), $previous))->keyBy(0);

        $this->assertSame(['Verified signups, 2026-10 (to date)', '60', '31', '+29'], $rows['Verified signups, 2026-10 (to date)']);
        $this->assertSame(['MRR', '62.50', '57.50', '+5.00'], $rows['MRR']);
        $this->assertSame('0', $rows['Billing subscriptions'][3]);
    }

    public function test_compare_without_a_previous_pull_leaves_the_columns_blank(): void
    {
        $rows = collect(GrowthSummary::compare($this->pull(), null))->keyBy(0);

        $this->assertSame(['MRR', '62.50', '-', ''], $rows['MRR']);
    }

    public function test_new_notes_are_the_ones_the_previous_pull_did_not_carry(): void
    {
        $current = $this->pull(['meta' => ['notes' => ['note a', 'note b']]]);

        $this->assertSame(['note b'], GrowthSummary::newNotes($current, $this->pull()));
        $this->assertSame(['note a', 'note b'], GrowthSummary::newNotes($current, null));
    }
}
