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

        $since = $month !== '' ? date('Y-m', strtotime($month.'-01 -3 months')) : '';

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
            ['label' => "Organizers with 5+ events, signed up since {$since} (of those with a schedule)", 'value' => self::organizersWithFullCalendar($data, $since)],
            ['label' => 'Events imported, last 30 days', 'value' => self::dailySum($data, 'events_imported', 30)],
            ['label' => 'Schedules selling (paid ticket in 90 days)', 'value' => self::sellers($data)],
            ['label' => '  of them, nothing on sale in the next '.GrowthExportService::UPCOMING_DAYS.' days', 'value' => self::sellersWithNothingOnSale($data)],
            ['label' => 'Schedules with upcoming paid events their plan cannot sell', 'value' => self::countRows($data, 'upcoming_paid_blocked', fn ($v) => $v > 0)],
            ['label' => 'Owners linking events to a self-serve ticketing platform (any / 2+ events)', 'value' => self::linkingToSelfServe($data)],
            ['label' => 'Admin-granted plans', 'value' => $money['by_plan_source']['admin'] ?? null],
            ['label' => 'Selling trials started / converted', 'value' => isset($money['ticket_trials'])
                ? ($money['ticket_trials']['started'] ?? 0).' / '.($money['ticket_trials']['converted'] ?? 0)
                : null],
            ['label' => 'Cancellations (all time)', 'value' => $data['churn']['cancelled'] ?? null],
            ['label' => '  cancelled, still paid to period end', 'value' => self::pendingCancellations($data)],
            ['label' => 'Stripe trials ending within 7 days (schedules / owners)', 'value' => self::trialsEndingSoon($data)],
            ['label' => 'Schedules whose Google sync made 100+ events in one day from a primary calendar', 'value' => self::bulkPrimarySyncs($data)],
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

        // The same five states the /admin/growth card shows, in the pull's own English.
        $status = match ($test['phase'] ?? null) {
            'baseline' => 'One headline, nothing being tested: its numbers are the baseline for the next round.',
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
                // One headline races nobody: its "chance best" is 100% by definition, so it is not shown.
                ($test['phase'] ?? null) === 'baseline' ? '-' : self::percent($row['p_best'] ?? null),
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

    /**
     * The schedule rows as column => value maps, or null when the pull lacks any of $needed
     * (schema 21 added most of what the rows below ask).
     *
     * @return ?list<array<string, mixed>>
     */
    private static function scheduleRows(array $data, array $needed): ?array
    {
        $columns = $data['schedules']['columns'] ?? [];
        if (array_diff($needed, $columns) !== []) {
            return null;
        }

        return array_map(fn ($row) => array_combine($columns, $row), $data['schedules']['rows'] ?? []);
    }

    /** How many schedule rows pass $test($value, $row) for one column; null when it is missing. */
    private static function countRows(array $data, string $column, callable $test): ?int
    {
        $rows = self::scheduleRows($data, [$column]);

        return $rows === null ? null : count(array_filter($rows, fn ($row) => $test($row[$column] ?? null, $row)));
    }

    /**
     * Sellers (a paid ticket in 90 days) with no ticketed event in the upcoming window, "n / of N".
     * A seller between events and a seller who stopped both read as zero sales this month; this is
     * the first half of telling them apart.
     */
    private static function sellersWithNothingOnSale(array $data): ?string
    {
        $rows = self::scheduleRows($data, ['paid_tickets_90d', 'upcoming_ticketed_events']);
        if ($rows === null) {
            return null;
        }

        $sellers = array_filter($rows, fn ($row) => ($row['paid_tickets_90d'] ?? 0) > 0);

        return count(array_filter($sellers, fn ($row) => ($row['upcoming_ticketed_events'] ?? 0) === 0)).' / '.count($sellers);
    }

    /**
     * Subscriptions cancelled but paid to the end of their period, which billing and MRR still
     * count: "n" followed by the reasons given, e.g. "2 (season_over 2)".
     */
    private static function pendingCancellations(array $data): ?string
    {
        $rows = self::scheduleRows($data, ['sub_status', 'cancel_pending', 'cancel_reason']);
        if ($rows === null) {
            return null;
        }

        // A cancelled trial is pending too, and pays nothing.
        $reasons = array_count_values(array_map(fn ($row) => (string) ($row['cancel_reason'] ?? 'none'),
            array_filter($rows, fn ($row) => ($row['cancel_pending'] ?? false) === true
                && in_array($row['sub_status'] ?? null, ['active', 'past_due'], true))));
        arsort($reasons);

        $total = array_sum($reasons);
        if ($total === 0) {
            return '0';
        }

        return $total.' ('.implode(', ', array_map(fn ($reason, $n) => "{$reason} {$n}", array_keys($reasons), $reasons)).')';
    }

    /**
     * Schedules whose busiest day was 100+ events pulled by a Google sync that reads the token
     * account's primary calendar: the shape of a sync that read somebody else's calendar. A day
     * of hand or ICS imports, or a sync that only pushes (`to`), is not that.
     */
    private static function bulkPrimarySyncs(array $data): ?int
    {
        $rows = self::scheduleRows($data, ['max_events_one_day_90d', 'max_day_source', 'gsync_direction', 'gsync_calendar']);
        if ($rows === null) {
            return null;
        }

        return count(array_filter($rows, fn ($row) => ($row['max_events_one_day_90d'] ?? 0) >= 100
            && ($row['max_day_source'] ?? null) === 'google'
            && in_array($row['gsync_direction'] ?? null, ['from', 'both'], true)
            && ($row['gsync_calendar'] ?? null) === 'primary'));
    }

    /** Stripe trials ending within a week and not cancelled, as "schedules / owners": one owner can trial several. */
    private static function trialsEndingSoon(array $data): ?string
    {
        $rows = self::scheduleRows($data, ['uid', 'trial_ends_in', 'cancel_pending']);
        if ($rows === null) {
            return null;
        }

        // A trial already cancelled will not convert, so it is not one to watch.
        $soon = array_filter($rows, fn ($row) => in_array($row['trial_ends_in'] ?? null, ['0-3', '4-7'], true)
            && ($row['cancel_pending'] ?? false) !== true);

        return count($soon).' / '.count(array_unique(array_column($soon, 'uid')));
    }

    /**
     * Organizers whose fullest schedule holds five or more events, out of the organizers who
     * signed up from $since on and have a schedule at all: "n / N".
     *
     * The question is whether a new organizer ends up with a calendar worth showing anyone. It is
     * asked per owner, of their fullest schedule, because a calendar pull creates a venue schedule
     * for each location under the same account: a per-schedule share would fall as importing
     * works. Accounts made by submitting an event to someone else's schedule are left out, as in
     * linkingToSelfServe(). Null when the pull lacks a column it needs.
     */
    private static function organizersWithFullCalendar(array $data, string $since): ?string
    {
        $schedules = $data['schedules']['columns'] ?? [];
        $uid = array_search('uid', $schedules, true);
        $events = array_search('events_total', $schedules, true);

        $signups = $data['signups']['columns'] ?? [];
        $signupUid = array_search('uid', $signups, true);
        $intent = array_search('signup_intent', $signups, true);
        $created = array_search('created_month', $signups, true);

        if (in_array(false, [$uid, $events, $signupUid, $intent, $created], true) || $since === '') {
            return null;
        }

        $organizers = [];
        foreach ($data['signups']['rows'] ?? [] as $row) {
            if (in_array($row[$intent] ?? null, [null, 'organizer'], true) && ($row[$created] ?? '') >= $since) {
                $organizers[$row[$signupUid]] = true;
            }
        }

        $fullest = [];
        foreach ($data['schedules']['rows'] ?? [] as $row) {
            if (isset($organizers[$row[$uid]])) {
                $fullest[$row[$uid]] = max($fullest[$row[$uid]] ?? 0, (int) ($row[$events] ?? 0));
            }
        }

        return count(array_filter($fullest, fn ($n) => $n >= 5)).' / '.count($fullest);
    }

    /**
     * Owners with an event that links to a self-serve ticketing platform
     * (external_tickets_90d.self_serve, schema 10+), as "any / with two or more such events".
     *
     * A ceiling on the sellers who could sell here instead, not a count of them: the link says
     * where tickets are sold, not who sells them. Owners rather than schedules, because one person
     * can hold a dozen. And not the owners whose account was made by submitting an event to
     * someone else's schedule (signups.signup_intent "request"): that flow mints them a schedule
     * they never chose to list with. Null on a pull without the column, so the row reads as not
     * measured rather than as zero.
     */
    private static function linkingToSelfServe(array $data): ?string
    {
        $columns = $data['schedules']['columns'] ?? [];
        $i = array_search('external_tickets_90d', $columns, true);
        $uid = array_search('uid', $columns, true);
        if ($i === false || $uid === false) {
            return null;
        }

        $signups = $data['signups']['columns'] ?? [];
        $signupUid = array_search('uid', $signups, true);
        $intent = array_search('signup_intent', $signups, true);
        $submitters = [];
        if ($signupUid !== false && $intent !== false) {
            foreach ($data['signups']['rows'] ?? [] as $row) {
                if (($row[$intent] ?? null) === 'request') {
                    $submitters[$row[$signupUid]] = true;
                }
            }
        }

        $events = [];
        foreach ($data['schedules']['rows'] ?? [] as $row) {
            $count = $row[$i]['self_serve'] ?? 0;
            if ($count > 0 && ! isset($submitters[$row[$uid]])) {
                $events[$row[$uid]] = ($events[$row[$uid]] ?? 0) + $count;
            }
        }

        return count($events).' / '.count(array_filter($events, fn ($n) => $n >= 2));
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
