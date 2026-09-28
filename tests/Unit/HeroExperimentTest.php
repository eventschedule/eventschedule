<?php

namespace Tests\Unit;

use App\Utils\HeroExperiment;
use PHPUnit\Framework\TestCase;

/**
 * The pure half of the homepage headline test: the copy every variant must keep, and the maths
 * that turns per-variant totals into traffic shares. The half that touches the database (the
 * candidate and winner settings, the page, the beacons, signup) is Tests\Feature\HeroExperimentTest.
 */
class HeroExperimentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        mt_srand(20260928);
    }

    /**
     * The rules the fold held before it became a test, applied to every variant, because the
     * server only ever renders one of them and MarketingHeroClaimTest can only see that one.
     */
    public function test_every_variant_keeps_the_fold_rules(): void
    {
        $this->assertArrayHasKey(HeroExperiment::DEFAULT, HeroExperiment::VARIANTS);

        foreach (HeroExperiment::VARIANTS as $key => $copy) {
            $this->assertSame(['line1', 'line2', 'subtitle'], array_keys($copy), $key);
            $this->assertMatchesRegularExpression('/^[a-z0-9_]{1,32}$/', $key, 'keys are stored in varchar(32) columns');

            // .es-mask animates each line as one block, so a wrapped line rises as a slab.
            $this->assertLessThanOrEqual(24, mb_strlen($copy['line1']), "{$key} line 1 is over the 24-character mask budget");
            $this->assertLessThanOrEqual(24, mb_strlen($copy['line2']), "{$key} line 2 is over the 24-character mask budget");

            $fold = $copy['line1'].' '.$copy['line2'].' '.$copy['subtitle'];

            $this->assertStringContainsStringIgnoringCase('event calendar', $fold, "{$key} never says what the product is");
            $this->assertMatchesRegularExpression('/\bbook(ing|ings|ed)?\b/i', $fold, "{$key} never says the product takes bookings");
            $this->assertDoesNotMatchRegularExpression('/\b(Pro|Enterprise)\b/', $fold, "{$key} names a paid plan in the fold");
            $this->assertStringNotContainsString("\u{2014}", $fold, "{$key} contains an em-dash");
        }
    }

    public function test_the_beta_sampler_has_the_right_mean(): void
    {
        $draws = 20000;
        $sum = 0;
        for ($i = 0; $i < $draws; $i++) {
            $sum += HeroExperiment::sampleBeta(3, 7);
        }

        $this->assertEqualsWithDelta(0.3, $sum / $draws, 0.01);
    }

    public function test_with_no_data_every_variant_gets_an_even_share(): void
    {
        $stats = $this->stats();

        $weights = $this->weights($stats, 1.0);

        foreach ($weights as $weight) {
            $this->assertEqualsWithDelta(1 / count($stats), $weight, 1e-9);
        }
    }

    public function test_the_weights_sum_to_one_and_respect_the_floor(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100);
        $stats['crowd']['clicks'] = 300;

        $weights = $this->weights($stats, 1.0);

        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-9);
        $this->assertGreaterThan(0.7, $weights['crowd'], 'a clear click leader should take most of the traffic');

        foreach ($weights as $key => $weight) {
            $this->assertGreaterThanOrEqual(HeroExperiment::FLOOR - 1e-9, $weight, "{$key} fell under the floor");
        }
    }

    /**
     * A variant added later has no visitors, and must not be starved by the others' history:
     * it gets at least an even share until it has had BURN_IN_VISITORS.
     */
    public function test_a_variant_still_in_burn_in_gets_at_least_an_even_share(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100);
        $stats['crowd']['clicks'] = 300;
        $stats['plan'] = ['visitors' => 0, 'clicks' => 0, 'signups' => 0];

        $weights = $this->weights($stats, 1.0);

        $this->assertGreaterThanOrEqual(1 / count($stats) - 1e-9, $weights['plan']);

        // Nor much more: with a flat prior its wide posterior won most draws and it took ~0.72
        // of the traffic. The pooled-rate prior starts it near its fair share.
        $this->assertLessThan(0.5, $weights['plan'], 'a variant with no data should not take most of the traffic');
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-9);
    }

    /**
     * The minimum has to hold even for a new variant that the sampler rates badly, which is
     * the case the water-filling exists for. Driven through applyMinimums() directly so the
     * raw weight can be pinned near zero.
     */
    public function test_the_minimum_lifts_a_variant_the_raw_weights_would_starve(): void
    {
        $weights = HeroExperiment::applyMinimums(
            ['a' => 0.9, 'b' => 0.099, 'c' => 0.001],
            ['a' => 0.05, 'b' => 0.05, 'c' => 1 / 3]
        );

        $this->assertEqualsWithDelta(1 / 3, $weights['c'], 1e-9);
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 1e-9);
        $this->assertGreaterThan($weights['b'], $weights['a']);
    }

    /**
     * The blend is the point: the click leader and the signup leader are DIFFERENT variants
     * here, so the traffic has to move from one to the other as signups accumulate. With one
     * leader on both metrics this test could not tell the two phases apart.
     */
    public function test_traffic_moves_from_the_click_leader_to_the_signup_leader(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 10);
        $stats['crowd']['clicks'] = 300;   // leads on clicks
        $stats['plan']['signups'] = 40;    // leads on signups

        $early = $this->weights($stats, 1.0);
        $late = $this->weights($stats, 0.0);

        $this->assertGreaterThan($early['plan'], $early['crowd']);
        $this->assertGreaterThan($late['crowd'], $late['plan']);
    }

    public function test_more_successes_than_visitors_does_not_break_the_sampler(): void
    {
        $stats = $this->stats(visitors: 5, clicks: 50);

        $p = HeroExperiment::probabilityBest($stats, 'clicks', 200);

        $this->assertEqualsWithDelta(1.0, array_sum($p), 1e-9);
    }

    private function weights(array $stats, float $clickShare): array
    {
        return HeroExperiment::allocate(
            $stats,
            HeroExperiment::probabilityBest($stats, 'clicks'),
            HeroExperiment::probabilityBest($stats, 'signups'),
            $clickShare
        );
    }

    private function stats(int $visitors = 0, int $clicks = 0, int $signups = 0): array
    {
        return array_map(
            fn () => ['visitors' => $visitors, 'clicks' => $clicks, 'signups' => $signups],
            HeroExperiment::VARIANTS
        );
    }
}
