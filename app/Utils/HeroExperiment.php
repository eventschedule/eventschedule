<?php

namespace App\Utils;

use App\Models\MarketingExperimentStat;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The self-running A/B test on the homepage headline and subtitle.
 *
 * WHY THE BROWSER PICKS: anonymous marketing HTML is edge-cached on the URL alone
 * (CacheableMarketingResponse, docs/CACHING.md), so a variant chosen on the server would be
 * served to every visitor for ten minutes, and a response that set a variant cookie would stop
 * being cacheable at all. The server therefore renders the DEFAULT copy plus every variant and
 * the current traffic weights; an inline script on the homepage picks one, swaps the text,
 * remembers the pick in the es_attribution cookie (which reaches sign-up), and beacons a `view`
 * on the first pick and a `click` on the first sign-up link click.
 *
 * HOW IT DECIDES: probability matching (Thompson sampling). Each variant gets traffic in
 * proportion to the chance that it has the best rate. Signups are the real goal but are rare,
 * so while there are few of them the weights follow sign-up CLICKS, handing over to signups
 * linearly as they accumulate (SIGNUP_HANDOFF). The winner is decided on signups ONLY, and has
 * to hold for WIN_HOLD_DAYS before it is locked, so a lucky week cannot end the test.
 *
 * CHANGING THE COPY: give a rewritten variant a NEW key. Numbers are stored per key, so
 * editing a variant's text in place keeps crediting the old copy's history to the new text.
 * Adding, removing or editing any variant changes setHash(), which discards a stored candidate
 * or winner, so the test resumes on its own.
 */
final class HeroExperiment
{
    public const EXPERIMENT = 'home_hero';

    /** Rendered by the server while the test runs, and to visitors without JS. */
    public const DEFAULT = 'plan';

    /**
     * line1/line2 must each fit the 24-character .es-mask budget (see the h1 in
     * marketing/index.blade.php), and every variant must pass MarketingHeroClaimTest's rules:
     * say "event calendar", say it takes bookings, name no paid plan. HeroExperimentTest
     * checks each one.
     *
     * Round two (from 2026-09-30): 'plan' is the control, and each challenger changes ONE thing
     * about it, so a win says which change earned it.
     */
    public const VARIANTS = [
        // Picked by hand as the winner of round one (against 'booked' and 'sells'), before the
        // test's own lock rules were met. The fold before 2026-09-19, with "take bookings" added
        // to its subtitle so it keeps the booking claim every variant has to make.
        'plan' => [
            'line1' => 'Plan, promote, and share',
            'line2' => 'your event calendar',
            'subtitle' => 'One page for your events and your open hours. Sell tickets and take bookings with zero platform fees, and scan people in at the door.',
        ],
        // Headline only: "sell" in the H1 instead of "share", since selling is what turns
        // sign-ups into paying customers. Same subtitle as 'plan'.
        'plan_sell' => [
            'line1' => 'Plan, promote, and sell',
            'line2' => 'from your event calendar',
            'subtitle' => 'One page for your events and your open hours. Sell tickets and take bookings with zero platform fees, and scan people in at the door.',
        ],
        // Subtitle only: opens with the zero-fee point instead of "one page". Same headline as 'plan'.
        'plan_fees' => [
            'line1' => 'Plan, promote, and share',
            'line2' => 'your event calendar',
            'subtitle' => 'Sell tickets and take bookings with zero platform fees. Put your events and open hours on one page, and scan people in at the door.',
        ],
        // Retired 2026-09-30 when 'plan' was picked as the winner of round one: 'booked'
        // ("Everything you have on. Booked solid.") and 'sells' ("The event calendar that sells
        // the tickets."). Removed earlier the same day, two days in at about 12 visitors each and
        // only to cut the number of arms: 'sellout' ("Pack the room. Sell out every date.") and
        // 'crowd' ("Your events, one link. Your crowd, coming back."). Their counts stay in
        // marketing_experiment_stats and users.hero_variant under their keys; stats() reads only
        // the keys listed here, so re-adding one under the SAME key would resume its history.
    ];

    /** A variant with fewer visitors than this gets at least an even share of traffic. */
    public const BURN_IN_VISITORS = 300;

    /** Total signups at which the weights stop listening to clicks entirely. */
    public const SIGNUP_HANDOFF = 60;

    /** Minimum share per variant until a winner is locked, so every variant keeps learning. */
    public const FLOOR = 0.05;

    public const WIN_PROBABILITY = 0.95;

    /** Below this the current candidate is dropped (hysteresis, so noise at 0.95 cannot reset it). */
    public const CANDIDATE_DROP_PROBABILITY = 0.90;

    public const WIN_MIN_SIGNUPS = 25;

    /**
     * Visitors the LEADER needs before it can become the candidate. Every other variant needs only
     * BURN_IN_VISITORS: once burn-in ends a trailing variant is held at FLOOR, which at this
     * traffic is a couple of visitors a day, so requiring 800 of every variant meant the test could
     * never lock at all. A thinly sampled variant is not waved through - its wide posterior keeps
     * the leader's probability of being best under WIN_PROBABILITY until the data says otherwise.
     */
    public const WIN_MIN_VISITORS = 800;

    public const WIN_HOLD_DAYS = 7;

    public const DRAWS = 4000;

    /** How many visitors' worth of weight the pooled-rate prior carries (see probabilityBest()). */
    public const PRIOR_STRENGTH = 50;

    /** Matches the edge TTL, so the weights baked into a cached page are at most ~20 minutes old. */
    public const CACHE_TTL = 600;

    public const CANDIDATE_SETTING = 'hero_experiment_candidate';

    public const WINNER_SETTING = 'hero_experiment_winner';

    public const EVENTS = ['view' => 'visitors', 'click' => 'clicks'];

    public static function isVariant(mixed $key): bool
    {
        return is_string($key) && array_key_exists($key, self::VARIANTS);
    }

    /**
     * Fingerprint of the variant set. A stored candidate or winner is only honoured while it
     * matches, so any edit to VARIANTS restarts the decision.
     */
    public static function setHash(): string
    {
        return substr(md5(json_encode(self::VARIANTS)), 0, 12);
    }

    /**
     * What the homepage needs: the copy to render server-side, and, while the test runs, every
     * variant and its current share for the browser to pick from.
     *
     * @return array{running: bool, default: array{line1: string, line2: string, subtitle: string}, variants: array, weights: array<string, float>}
     */
    public static function forPage(): array
    {
        $idle = [
            'running' => false,
            'default' => self::VARIANTS[self::DEFAULT],
            'variants' => [],
            'weights' => [],
        ];

        if (! config('app.is_nexus')) {
            return $idle;
        }

        $state = self::state();

        if ($state === null) {
            return $idle;
        }

        if ($state['winner'] !== null) {
            return array_merge($idle, ['default' => self::VARIANTS[$state['winner']['key']]]);
        }

        return [
            'running' => true,
            'default' => self::VARIANTS[self::DEFAULT],
            'variants' => self::VARIANTS,
            'weights' => $state['weights'],
        ];
    }

    /**
     * Count a beaconed `view` (a visitor's first assignment) or `click` (their first click on
     * a sign-up link) against a variant for today (UTC).
     */
    public static function recordEvent(string $variant, string $event): void
    {
        if (! self::isVariant($variant) || ! isset(self::EVENTS[$event])) {
            return;
        }

        $column = self::EVENTS[$event];

        CounterUtils::statement(
            "INSERT INTO marketing_experiment_stats (experiment, variant, date, {$column})
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [self::EXPERIMENT, $variant, now()->toDateString()]
        );
    }

    /**
     * The evaluated test, cached for CACHE_TTL. Null when it cannot be computed (for example a
     * deploy that has not run its migrations yet), in which case the homepage renders the
     * default and runs no test rather than failing.
     */
    public static function state(): ?array
    {
        try {
            return Cache::remember('hero_experiment:'.self::setHash(), self::CACHE_TTL, fn () => self::evaluate(self::stats()));
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * All-time totals per variant. Visitors and clicks come from the beacon counters; signups
     * are the accounts that carried the variant in es_attribution when they were created.
     *
     * @return array<string, array{visitors: int, clicks: int, signups: int}>
     */
    public static function stats(): array
    {
        $keys = array_keys(self::VARIANTS);

        $beacons = MarketingExperimentStat::query()
            ->where('experiment', self::EXPERIMENT)
            ->whereIn('variant', $keys)
            ->groupBy('variant')
            ->select('variant', DB::raw('SUM(visitors) AS visitors'), DB::raw('SUM(clicks) AS clicks'))
            ->get()
            ->keyBy('variant');

        $signups = User::query()
            ->whereIn('hero_variant', $keys)
            ->groupBy('hero_variant')
            ->select('hero_variant', DB::raw('COUNT(*) AS signups'))
            ->pluck('signups', 'hero_variant');

        $stats = [];
        foreach ($keys as $key) {
            $stats[$key] = [
                'visitors' => (int) ($beacons[$key]->visitors ?? 0),
                'clicks' => (int) ($beacons[$key]->clicks ?? 0),
                'signups' => (int) ($signups[$key] ?? 0),
            ];
        }

        return $stats;
    }

    /**
     * Turn the totals into weights, and advance the candidate/winner settings.
     *
     * @param  array<string, array{visitors: int, clicks: int, signups: int}>  $stats
     */
    public static function evaluate(array $stats): array
    {
        $hash = self::setHash();
        $winner = self::readSetting(self::WINNER_SETTING, $hash);

        $pClicks = self::probabilityBest($stats, 'clicks');
        $pSignups = self::probabilityBest($stats, 'signups');

        $totalSignups = array_sum(array_column($stats, 'signups'));
        $clickShare = (float) max(0, 1 - $totalSignups / self::SIGNUP_HANDOFF);

        $candidate = $winner === null ? self::advanceCandidate($stats, $pSignups, $hash) : null;

        if ($candidate !== null && $candidate['locked']) {
            $winner = ['key' => $candidate['key'], 'date' => now()->toDateString()];
            $candidate = null;
        }

        if ($winner !== null) {
            $weights = array_map(fn ($key) => $key === $winner['key'] ? 1.0 : 0.0, array_combine(array_keys($stats), array_keys($stats)));
        } else {
            $weights = self::allocate($stats, $pClicks, $pSignups, $clickShare);
        }

        return [
            'stats' => $stats,
            'weights' => $weights,
            'p_clicks' => $pClicks,
            'p_signups' => $pSignups,
            'click_share' => $clickShare,
            'candidate' => $candidate === null ? null : ['key' => $candidate['key'], 'date' => $candidate['date']],
            'winner' => $winner,
        ];
    }

    /**
     * Blend the two probability vectors, then lift every variant to its minimum share.
     *
     * @return array<string, float>
     */
    public static function allocate(array $stats, array $pClicks, array $pSignups, float $clickShare): array
    {
        $raw = [];
        $minimums = [];
        $even = 1 / count($stats);

        foreach ($stats as $key => $row) {
            $raw[$key] = $clickShare * $pClicks[$key] + (1 - $clickShare) * $pSignups[$key];
            $minimums[$key] = $row['visitors'] < self::BURN_IN_VISITORS ? max($even, self::FLOOR) : self::FLOOR;
        }

        return self::applyMinimums($raw, $minimums);
    }

    /**
     * Water-filling: pin every variant below its minimum AT its minimum, and share what is left
     * among the rest in proportion to their raw weight. Repeats because lowering the rest can
     * push another one under.
     *
     * @param  array<string, float>  $raw
     * @param  array<string, float>  $minimums
     * @return array<string, float>
     */
    public static function applyMinimums(array $raw, array $minimums): array
    {
        if (array_sum($minimums) >= 1) {
            $total = array_sum($minimums);

            return array_map(fn ($min) => $min / $total, $minimums);
        }

        $pinned = [];

        while (true) {
            $free = array_diff_key($raw, $pinned);
            $mass = 1 - array_sum($pinned);
            $freeTotal = array_sum($free);

            $weights = $pinned;
            foreach ($free as $key => $value) {
                $weights[$key] = $freeTotal > 0 ? $value / $freeTotal * $mass : $mass / count($free);
            }

            $newlyPinned = false;
            foreach ($free as $key => $value) {
                if ($weights[$key] < $minimums[$key]) {
                    $pinned[$key] = $minimums[$key];
                    $newlyPinned = true;
                }
            }

            if (! $newlyPinned) {
                return array_merge(array_fill_keys(array_keys($raw), 0.0), $weights);
            }
        }
    }

    /**
     * The chance each variant has the highest true rate, by Monte Carlo over Beta posteriors.
     *
     * The prior is centred on the POOLED rate across all variants, worth PRIOR_STRENGTH
     * visitors. A flat Beta(1, 1) prior makes a variant with no data look like it could be
     * anywhere from 0% to 100%, so against variants converting at a few percent it wins most
     * draws: a challenger added mid-test measured 0.72, i.e. most of the homepage for a day or
     * more. Shrinking toward the pooled rate starts it at roughly its fair share instead.
     *
     * @return array<string, float>
     */
    public static function probabilityBest(array $stats, string $metric, int $draws = self::DRAWS): array
    {
        $wins = array_fill_keys(array_keys($stats), 0);

        // A lost `view` beacon can leave more clicks or signups than visitors; clamp so the
        // failure count can never go negative.
        $successes = array_map(fn ($row) => min($row[$metric], $row['visitors']), $stats);
        $totalVisitors = array_sum(array_column($stats, 'visitors'));
        $pooled = $totalVisitors > 0 ? array_sum($successes) / $totalVisitors : 0.0;
        $priorA = 1 + $pooled * self::PRIOR_STRENGTH;
        $priorB = 1 + (1 - $pooled) * self::PRIOR_STRENGTH;

        for ($i = 0; $i < $draws; $i++) {
            $bestKey = null;
            $bestValue = -1.0;

            foreach ($stats as $key => $row) {
                $value = self::sampleBeta($priorA + $successes[$key], $priorB + $row['visitors'] - $successes[$key]);

                if ($value > $bestValue) {
                    $bestValue = $value;
                    $bestKey = $key;
                }
            }

            $wins[$bestKey]++;
        }

        return array_map(fn ($n) => $n / $draws, $wins);
    }

    public static function sampleBeta(float $a, float $b): float
    {
        $x = self::sampleGamma($a);
        $y = self::sampleGamma($b);

        return $x / ($x + $y);
    }

    /**
     * Marsaglia and Tsang's method. Every shape here is at least 1 (a count plus the prior),
     * which is the range the method needs without the boosting step.
     */
    public static function sampleGamma(float $shape): float
    {
        $d = $shape - 1 / 3;
        $c = 1 / sqrt(9 * $d);

        while (true) {
            do {
                $x = self::sampleNormal();
                $v = 1 + $c * $x;
            } while ($v <= 0);

            $v = $v * $v * $v;
            $u = self::uniform();

            if ($u < 1 - 0.0331 * $x ** 4 || log($u) < 0.5 * $x * $x + $d * (1 - $v + log($v))) {
                return $d * $v;
            }
        }
    }

    private static function sampleNormal(): float
    {
        return sqrt(-2 * log(self::uniform())) * cos(2 * M_PI * self::uniform());
    }

    /** Uniform on (0, 1), never 0, so log() is always defined. Seedable with mt_srand(). */
    private static function uniform(): float
    {
        return (mt_rand() + 1) / (mt_getrandmax() + 2);
    }

    /**
     * Move the candidate along: set it when a variant first qualifies, drop it when it stops
     * leading, and report it as locked once it has held for WIN_HOLD_DAYS.
     *
     * @return array{key: string, date: string, locked: bool}|null
     */
    private static function advanceCandidate(array $stats, array $pSignups, string $hash): ?array
    {
        $stored = self::readSetting(self::CANDIDATE_SETTING, $hash);

        arsort($pSignups);
        $leader = array_key_first($pSignups);

        $qualifies = $pSignups[$leader] >= self::WIN_PROBABILITY
            && $stats[$leader]['signups'] >= self::WIN_MIN_SIGNUPS
            && $stats[$leader]['visitors'] >= self::WIN_MIN_VISITORS
            && min(array_column($stats, 'visitors')) >= self::BURN_IN_VISITORS;

        if ($stored !== null) {
            $holding = $stored['key'] === $leader && $pSignups[$leader] >= self::CANDIDATE_DROP_PROBABILITY;

            if (! $holding) {
                Setting::set(self::CANDIDATE_SETTING, null);
                $stored = null;
            } elseif (now()->subDays(self::WIN_HOLD_DAYS)->toDateString() >= $stored['date']) {
                Setting::set(self::WINNER_SETTING, $hash.'|'.$stored['key'].'|'.now()->toDateString());
                Setting::set(self::CANDIDATE_SETTING, null);

                return $stored + ['locked' => true];
            }
        }

        if ($stored === null && $qualifies) {
            $stored = ['key' => $leader, 'date' => now()->toDateString()];
            Setting::set(self::CANDIDATE_SETTING, $hash.'|'.$leader.'|'.$stored['date']);
        }

        return $stored === null ? null : $stored + ['locked' => false];
    }

    /**
     * A stored "hash|key|date" value, or null when absent, malformed, or written for a
     * different variant set.
     *
     * @return array{key: string, date: string}|null
     */
    private static function readSetting(string $name, string $hash): ?array
    {
        $parts = explode('|', (string) Setting::get($name, ''));

        if (count($parts) !== 3 || $parts[0] !== $hash || ! self::isVariant($parts[1])) {
            return null;
        }

        return ['key' => $parts[1], 'date' => $parts[2]];
    }

    /**
     * Rows for the admin card, ordered by current share.
     */
    public static function report(): ?array
    {
        $state = self::state();

        if ($state === null) {
            return null;
        }

        $rows = [];
        foreach ($state['stats'] as $key => $row) {
            $rows[] = [
                'key' => $key,
                'headline' => self::VARIANTS[$key]['line1'].' '.self::VARIANTS[$key]['line2'],
                'subtitle' => self::VARIANTS[$key]['subtitle'],
                'is_default' => $key === self::DEFAULT,
                'share' => $state['weights'][$key],
                'visitors' => $row['visitors'],
                'clicks' => $row['clicks'],
                'signups' => $row['signups'],
                'click_rate' => $row['visitors'] > 0 ? $row['clicks'] / $row['visitors'] : null,
                'signup_rate' => $row['visitors'] > 0 ? $row['signups'] / $row['visitors'] : null,
                'p_best' => $state['p_signups'][$key],
                'p_best_clicks' => $state['p_clicks'][$key],
            ];
        }

        usort($rows, fn ($a, $b) => $b['share'] <=> $a['share']);

        if ($state['winner'] !== null) {
            $phase = 'winner';
        } elseif ($state['candidate'] !== null) {
            $phase = 'candidate';
        } else {
            $phase = $state['click_share'] > 0 ? 'clicks' : 'signups';
        }

        return [
            'phase' => $phase,
            'rows' => $rows,
            'click_share' => $state['click_share'],
            'candidate' => $state['candidate'],
            'winner' => $state['winner'],
            'lock_date' => $state['candidate'] !== null
                ? Carbon::parse($state['candidate']['date'])->addDays(self::WIN_HOLD_DAYS)->toDateString()
                : null,
        ];
    }
}
