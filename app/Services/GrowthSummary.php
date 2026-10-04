<?php

namespace App\Services;

/**
 * A handful of headline numbers read out of a growth payload, side by side with an earlier pull.
 *
 * What `app:pull-growth` prints after a download, so the first look at a new pull is a glance
 * rather than a jq session. Pure: it reads decoded payload arrays and nothing else, so it works on
 * any pull on disk, and every lookup is null-safe because older pulls (other schema versions) lack
 * newer fields - a missing field prints as "-", never as a misleading zero.
 *
 * Deliberately small. The payload's meaning lives in docs/GROWTH_DATA.md; anything subtler than
 * these belongs in an analysis, not in a summary that reads as authoritative.
 */
class GrowthSummary
{
    /**
     * @param  ?string  $month  The month to read month-keyed figures for (YYYY-MM). Defaults to the
     *                          pull's own month in progress; compare() passes the CURRENT pull's
     *                          month to an older pull, so "this month" means the same month in both.
     * @return list<array{label: string, value: int|float|string|null}>
     */
    public static function kpis(array $data, ?string $month = null): array
    {
        $month ??= self::monthOf($data);
        $lastMonth = $month !== '' ? date('Y-m', strtotime($month.'-01 -1 month')) : '';

        $traffic = collect($data['traffic'] ?? [])->keyBy('month');
        $stages = collect($data['funnel']['stages'] ?? [])->pluck('count', 'key');
        $money = $data['monetization'] ?? [];

        $kpis = [
            ['label' => "Verified signups, {$month} (to date)", 'value' => $traffic[$month]['verified_signups'] ?? null],
            ['label' => "Verified signups, {$lastMonth}", 'value' => $traffic[$lastMonth]['verified_signups'] ?? null],
            ['label' => "Site visitors, {$lastMonth}", 'value' => $traffic[$lastMonth]['visitors'] ?? null],
            ['label' => 'Organizer signups in range', 'value' => $data['funnel']['cohort_size'] ?? null],
            ['label' => '  saved a schedule', 'value' => $stages['saved_schedule'] ?? null],
            ['label' => '  saved an event', 'value' => $stages['saved_event'] ?? null],
            ['label' => '  made a paid ticket type', 'value' => $stages['saved_paid_ticket'] ?? null],
            ['label' => '  subscribed', 'value' => $stages['subscribed'] ?? null],
            ['label' => 'Biggest funnel drop', 'value' => self::biggestDrop($data['funnel']['biggest_drop'] ?? null)],
            ['label' => 'Billing subscriptions', 'value' => $money['billing_subscriptions'] ?? null],
            // Cast: an older pull (or any JSON without PRESERVE_ZERO_FRACTION) turns 60.0 into 60,
            // which would print as "60" beside "62.50".
            ['label' => 'MRR', 'value' => isset($money['mrr']) ? (float) $money['mrr'] : null],
            ['label' => 'Schedules selling (paid ticket in 90 days)', 'value' => self::sellers($data)],
            ['label' => 'Admin-granted plans', 'value' => $money['by_plan_source']['admin'] ?? null],
            ['label' => 'Selling trials started / converted', 'value' => isset($money['ticket_trials'])
                ? ($money['ticket_trials']['started'] ?? 0).' / '.($money['ticket_trials']['converted'] ?? 0)
                : null],
            ['label' => 'Cancellations (all time)', 'value' => $data['churn']['cancelled'] ?? null],
            // Rolling windows off daily[] and buyers[] (schema 9+), so they compare between any two
            // pulls without a calendar month having to close first.
            ['label' => 'Paid orders, last 30 days', 'value' => self::dailySum($data, 'paid_orders', 30)],
            ['label' => 'Stripe Connect completed, last 30 days', 'value' => self::dailySum($data, 'stripe_connected', 30)],
            ['label' => 'Attendees who became organizers, 12 months', 'value' => isset($data['buyers'])
                ? array_sum(array_column($data['buyers'], 'attendees_who_became_organizers'))
                : null],
        ];

        foreach ([$month => ' (to date)', $lastMonth => ''] as $m => $suffix) {
            foreach (self::gmvFor($data, $m) as $currency => $amount) {
                $kpis[] = ['label' => "Ticket GMV {$currency}, {$m}{$suffix}", 'value' => $amount];
            }
        }

        return $kpis;
    }

    /**
     * Each KPI with the same KPI from an earlier pull, and the difference when both are numbers.
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function compare(array $current, ?array $previous): array
    {
        $before = $previous
            ? collect(self::kpis($previous, self::monthOf($current)))->pluck('value', 'label')
            : collect();

        return array_map(function (array $kpi) use ($before) {
            $was = $before[$kpi['label']] ?? null;
            $delta = is_numeric($kpi['value']) && is_numeric($was)
                ? self::signed($kpi['value'] - $was)
                : '';

            return [$kpi['label'], self::show($kpi['value']), self::show($was), $delta];
        }, self::kpis($current));
    }

    /**
     * The homepage headline test the pull carries (hero_test, schema 9+): where it stands, and a
     * row per variant in the payload's own order, the largest current share first. Null when the
     * pull has no test in it - an older schema, or an install off the nexus - so the caller prints
     * nothing instead of an empty table.
     *
     * @return ?array{status: string, rows: list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}>}
     */
    public static function heroTest(array $data): ?array
    {
        $test = $data['hero_test'] ?? null;
        if (! is_array($test) || empty($test['rows'])) {
            return null;
        }

        // The same four sentences the /admin/growth card shows.
        $status = match ($test['phase'] ?? null) {
            'winner' => sprintf('Winner: %s, since %s.', $test['winner']['key'] ?? '?', $test['winner']['date'] ?? '?'),
            'candidate' => sprintf('%s is leading, and becomes the winner on %s if it keeps the lead.', $test['candidate']['key'] ?? '?', $test['lock_date'] ?? '?'),
            'clicks' => 'Learning: traffic follows sign-up clicks until enough signups come in.',
            default => 'Deciding on signups.',
        };

        if (! empty($test['reset_at'])) {
            $status .= " Counting since {$test['reset_at']}.";
        }

        return [
            'status' => $status,
            'rows' => array_map(fn (array $row) => [
                ($row['key'] ?? '?').(($row['is_default'] ?? false) ? ' (default)' : ''),
                self::percent($row['share'] ?? null),
                self::show($row['visitors'] ?? null),
                self::show($row['clicks'] ?? null),
                self::show($row['signups'] ?? null),
                self::percent($row['p_best'] ?? null),
            ], $test['rows']),
        ];
    }

    /**
     * meta.notes present in this pull and absent from the previous one - where a schema bump or a
     * new caveat announces itself.
     *
     * @return list<string>
     */
    public static function newNotes(array $current, ?array $previous): array
    {
        $old = $previous['meta']['notes'] ?? [];

        return array_values(array_diff($current['meta']['notes'] ?? [], $old));
    }

    private static function monthOf(array $data): string
    {
        return $data['meta']['partial_month']['month']
            ?? substr((string) ($data['meta']['generated_at'] ?? ''), 0, 7);
    }

    private static function dailySum(array $data, string $metric, int $days): ?int
    {
        $i = array_search($metric, $data['daily']['columns'] ?? [], true);
        if ($i === false) {
            return null;
        }

        return array_sum(array_column(array_slice($data['daily']['rows'] ?? [], -$days), $i));
    }

    private static function sellers(array $data): ?int
    {
        $columns = $data['schedules']['columns'] ?? [];
        $i = array_search('paid_tickets_90d', $columns, true);
        if ($i === false) {
            return null;
        }

        return count(array_filter($data['schedules']['rows'] ?? [], fn ($row) => ($row[$i] ?? 0) > 0));
    }

    /** @return array<string, float> */
    private static function gmvFor(array $data, string $month): array
    {
        $out = [];
        foreach ($data['monetization']['gmv_by_currency'] ?? [] as $line) {
            if (($line['month'] ?? null) === $month) {
                $currency = $line['currency'] ?: 'unknown';
                $out[$currency] = round(($out[$currency] ?? 0) + (float) $line['amount'], 2);
            }
        }
        ksort($out);

        return $out;
    }

    private static function biggestDrop(?array $drop): ?string
    {
        return $drop ? "{$drop['from_key']} -> {$drop['to_key']} ({$drop['drop_pct']}% lost)" : null;
    }

    private static function show(int|float|string|null $value): string
    {
        if ($value === null) {
            return '-';
        }

        return is_float($value) ? number_format($value, 2) : (is_int($value) ? number_format($value) : $value);
    }

    private static function percent(int|float|null $ratio): string
    {
        return $ratio === null ? '-' : number_format($ratio * 100, 1).'%';
    }

    private static function signed(int|float $n): string
    {
        if ($n == 0) {
            return '0';
        }

        $formatted = is_float($n) ? number_format(abs($n), 2) : number_format(abs($n));

        return ($n > 0 ? '+' : '-').$formatted;
    }
}
