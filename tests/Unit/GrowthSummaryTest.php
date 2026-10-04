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

    /** The rolling KPIs read the trailing 30 days of daily[], not all of it. */
    public function test_rolling_kpis_sum_the_trailing_window(): void
    {
        $rows = [];
        foreach (range(1, 40) as $n) {
            $rows[] = ['2026-09-'.$n, 1, $n === 40 ? 2 : 0];
        }
        $pull = $this->pull();
        $pull['daily'] = ['columns' => ['date', 'paid_orders', 'stripe_connected'], 'rows' => $rows];
        $pull['buyers'] = [['attendees_who_became_organizers' => 2], ['attendees_who_became_organizers' => 1]];

        $kpis = GrowthSummary::kpis($pull);

        $this->assertSame(30, $this->value($kpis, 'Paid orders, last 30 days'));
        $this->assertSame(2, $this->value($kpis, 'Stripe Connect completed, last 30 days'));
        $this->assertSame(3, $this->value($kpis, 'Attendees who became organizers, 12 months'));
        $this->assertNull($this->value(GrowthSummary::kpis($this->pull()), 'Paid orders, last 30 days'), 'absent before schema 9');
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

        // A real schema 9 pull: it has the schedule's owner and type, and not the column schema 10 added.
        $nine = $this->pull();
        $nine['schedules'] = ['columns' => ['sid', 'uid', 'type', 'paid_tickets_90d'], 'rows' => [['s:1', 'u:a', 'venue', 3]]];
        $this->assertNull($this->value(GrowthSummary::kpis($nine), 'Owners linking events to a self-serve ticketing platform (any / 2+ events)'));
    }

    /**
     * A ceiling on sellers who could sell here, so it must not count what plainly is not one: a
     * person twice, a link to a box office, or an account the submit-an-event flow minted.
     */
    public function test_linking_to_self_serve_counts_owners_not_schedules_box_offices_or_minted_accounts(): void
    {
        $cell = fn (int $selfServe, int $boxOffice = 0) => [
            'events' => $selfServe + $boxOffice, 'priced' => 0, 'self_serve' => $selfServe, 'box_office' => $boxOffice, 'platforms' => [],
        ];

        $pull = $this->pull();
        $pull['schedules'] = [
            'columns' => ['sid', 'uid', 'type', 'external_tickets_90d'],
            'rows' => [
                // One owner, two schedules, one event each: one owner, with two events.
                ['s:1', 'u:a', 'venue', $cell(1)],
                ['s:2', 'u:a', 'curator', $cell(1)],
                // One event only.
                ['s:3', 'u:b', 'talent', $cell(1)],
                // A box office link is somebody else's sale.
                ['s:4', 'u:c', 'venue', $cell(0, 6)],
                // Minted by submitting an event to someone else's schedule.
                ['s:5', 'u:d', 'talent', $cell(3)],
                ['s:6', 'u:e', 'venue', null],
            ],
        ];
        $pull['signups'] = [
            'columns' => ['uid', 'signup_intent'],
            'rows' => [['u:a', 'organizer'], ['u:b', null], ['u:c', 'organizer'], ['u:d', 'request'], ['u:e', 'organizer']],
        ];

        $this->assertSame('2 / 1', $this->value(GrowthSummary::kpis($pull), 'Owners linking events to a self-serve ticketing platform (any / 2+ events)'));
    }

    /**
     * Asked per owner, of their fullest schedule: a calendar pull gives one account a venue
     * schedule per location, and a per-schedule share would fall as importing works.
     */
    public function test_organizers_with_a_full_calendar_are_counted_per_owner(): void
    {
        $label = 'Organizers with 5+ events, signed up since 2026-07 (of those with a schedule)';

        $pull = $this->pull();
        $pull['schedules'] = [
            'columns' => ['sid', 'uid', 'events_total'],
            'rows' => [
                // One owner, a venue stub and a real calendar: counted once, on the fuller one.
                ['s:1', 'u:a', 1],
                ['s:2', 'u:a', 5],
                // Four is not five.
                ['s:3', 'u:b', 4],
                // Minted by submitting an event to someone else's schedule.
                ['s:4', 'u:c', 9],
                // Signed up before the window.
                ['s:5', 'u:d', 20],
            ],
        ];
        $pull['signups'] = [
            'columns' => ['uid', 'signup_intent', 'created_month'],
            'rows' => [
                ['u:a', 'organizer', '2026-09'],
                ['u:b', null, '2026-07'],
                ['u:c', 'request', '2026-09'],
                ['u:d', 'organizer', '2026-06'],
                // Never saved a schedule: not in the denominator.
                ['u:e', 'organizer', '2026-10'],
            ],
        ];

        $this->assertSame('1 / 2', $this->value(GrowthSummary::kpis($pull), $label));

        // A pull without the columns reads as not measured.
        $this->assertNull($this->value(GrowthSummary::kpis($this->pull()), $label));
    }

    public function test_events_imported_is_a_rolling_sum_and_unknown_before_schema_12(): void
    {
        $pull = $this->pull();
        $pull['daily'] = ['columns' => ['date', 'events_created', 'events_imported'], 'rows' => [
            ['2026-10-13', 9, 4], ['2026-10-14', 3, 0], ['2026-10-15', 7, 6],
        ]];

        $this->assertSame(10, $this->value(GrowthSummary::kpis($pull), 'Events imported, last 30 days'));

        $pull['daily'] = ['columns' => ['date', 'events_created'], 'rows' => [['2026-10-15', 7]]];
        $this->assertNull($this->value(GrowthSummary::kpis($pull), 'Events imported, last 30 days'));
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

    /** The headline test, when the pull carries one: where it stands, and a row per variant. */
    public function test_it_reads_the_headline_test(): void
    {
        $test = [
            'phase' => 'candidate',
            'candidate' => ['key' => 'plan_sell', 'date' => '2026-10-10'],
            'winner' => null,
            'lock_date' => '2026-10-17',
            'reset_at' => '2026-09-30',
            'rows' => [
                ['key' => 'plan_sell', 'is_default' => false, 'share' => 0.9, 'visitors' => 1200, 'clicks' => 90, 'signups' => 31, 'p_best' => 0.962],
                ['key' => 'plan', 'is_default' => true, 'share' => 0.1, 'visitors' => 800, 'clicks' => 40, 'signups' => 9, 'p_best' => 0.038],
            ],
        ];

        $hero = GrowthSummary::heroTest($this->pull(['hero_test' => $test]));

        $this->assertSame('plan_sell is leading, and becomes the winner on 2026-10-17 if it keeps the lead. Counting since 2026-09-30.', $hero['status']);
        $this->assertSame([
            ['plan_sell', '90.0%', '1,200', '90', '31', '96.2%'],
            ['plan (default)', '10.0%', '800', '40', '9', '3.8%'],
        ], $hero['rows']);

        $learning = GrowthSummary::heroTest(['hero_test' => ['phase' => 'clicks', 'reset_at' => null] + $test]);
        $this->assertSame('Learning: traffic follows sign-up clicks until enough signups come in.', $learning['status']);

        $won = GrowthSummary::heroTest(['hero_test' => ['phase' => 'winner', 'winner' => ['key' => 'plan_sell', 'date' => '2026-10-17'], 'reset_at' => null] + $test]);
        $this->assertSame('Winner: plan_sell, since 2026-10-17.', $won['status']);
    }

    /** No test in the pull - an older schema, or an install off the nexus - is nothing to print. */
    public function test_a_pull_without_a_headline_test_has_none_to_show(): void
    {
        $this->assertNull(GrowthSummary::heroTest($this->pull()));
        $this->assertNull(GrowthSummary::heroTest($this->pull(['hero_test' => null])));
    }

    public function test_new_notes_are_the_ones_the_previous_pull_did_not_carry(): void
    {
        $current = $this->pull(['meta' => ['notes' => ['note a', 'note b']]]);

        $this->assertSame(['note b'], GrowthSummary::newNotes($current, $this->pull()));
        $this->assertSame(['note a', 'note b'], GrowthSummary::newNotes($current, null));
    }
}
