<?php

namespace Tests\Feature;

use App\Utils\PlatformPricing;
use App\Utils\TicketFees;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Every fee calculator on the marketing site works one sum, App\Utils\TicketFees.
 *
 * They used to work three. /compare charged Eventbrite its 2.9% payment processing fee; /pricing and
 * /for-talent left it out, and /for-talent told visitors processing was "bundled in", so for the
 * same 200 tickets at 25 dollars two pages quoted Eventbrite at 543.00 and the third worked out
 * 688.00. /compare's script meanwhile retyped every rate while its comment said it read them from
 * the server, and worked Luma out differently from the first paint.
 *
 * So the rates are pinned to the published figures, each page's rendered totals are held to
 * TicketFees for the inputs that page says it is showing, the pages that recompute in the browser
 * are held to handing their script the same rates, the browser's formula is run in Node against
 * the PHP one, and no calculator view may do fee arithmetic with a number of its own.
 */
class TicketFeesTest extends TestCase
{
    use RefreshDatabase;

    /** The pages that draw a calculator. */
    private const PAGES = ['/compare', '/pricing', '/for-talent', '/ticket-fee-calculator'];

    /** The pages whose calculator recomputes in the browser. */
    private const INTERACTIVE = ['/compare', '/pricing', '/ticket-fee-calculator'];

    /** Every file a calculator is drawn or computed in. */
    private const SOURCES = [
        'resources/views/marketing/compare.blade.php',
        'resources/views/marketing/pricing.blade.php',
        'resources/views/marketing/for-talent.blade.php',
        'resources/views/marketing/ticket-fee-calculator.blade.php',
        'resources/views/components/marketing/fee-calculator.blade.php',
        'resources/views/marketing/partials/ticket-fee-math.blade.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        PlatformPricing::flush();
    }

    protected function tearDown(): void
    {
        PlatformPricing::flush();
        parent::tearDown();
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2);
    }

    /** Every platform with a rate, which is every key but Stripe's own. */
    private function platforms(): array
    {
        return array_values(array_diff(array_keys(TicketFees::rates()), ['stripe']));
    }

    public function test_eventbrite_pays_its_service_fee_and_its_processing_fee(): void
    {
        // 200 x 25: 3.7% + 1.79 on each ticket is 543.00, and 2.9% of the 5,000 order is 145.00.
        $this->assertSame('688.00', $this->money(TicketFees::cost('eventbrite', 200, 25)));
        // 100 x 10: 37.00 + 179.00 + 29.00.
        $this->assertSame('245.00', $this->money(TicketFees::cost('eventbrite', 100, 10)));
    }

    public function test_our_cost_is_the_pro_price_plus_stripe(): void
    {
        config(['services.stripe_platform.price_monthly_amount' => '7']);
        PlatformPricing::flush();

        // 7.00 + 2.9% of 5,000 (145.00) + 0.30 on each of 200 tickets (60.00).
        $this->assertSame('212.00', $this->money(TicketFees::cost('eventschedule', 200, 25)));
    }

    public function test_luma_is_charged_at_whichever_plan_is_cheaper(): void
    {
        // 100 x 10: the free plan's 5% (50.00) beats Plus (59.00); Stripe is 59.00 either way.
        $this->assertSame('109.00', $this->money(TicketFees::cost('luma', 100, 10)));
        // 200 x 25: Plus (59.00) beats 5% of 5,000 (250.00); Stripe is 205.00 either way.
        $this->assertSame('264.00', $this->money(TicketFees::cost('luma', 200, 25)));
    }

    public function test_ticket_tailor_is_its_midpoint_plus_stripe(): void
    {
        // 100 x 10: 0.44 on each ticket (44.00) plus Stripe (29.00 + 30.00).
        $this->assertSame('103.00', $this->money(TicketFees::cost('ticket-tailor', 100, 10)));
    }

    public function test_ticketleap_charges_its_own_processing_with_a_flat_fee_and_a_cap(): void
    {
        // 200 x 25: (1.00 + 2% of 25) on each ticket is 300.00, and 3% of the 5,000 order is 150.00.
        $this->assertSame('450.00', $this->money(TicketFees::cost('ticketleap', 200, 25)));
        // 100 x 5, at the threshold: a flat 0.49 a ticket (49.00) and 3% of 500 (15.00).
        $this->assertSame('64.00', $this->money(TicketFees::cost('ticketleap', 100, 5)));
        // 10 x 2,000: 1.00 + 40.00 is capped at 20.00 a ticket (200.00); the 3% is not (600.00).
        $this->assertSame('800.00', $this->money(TicketFees::cost('ticketleap', 10, 2000)));
    }

    public function test_universe_caps_its_service_fee_but_not_its_processing(): void
    {
        // 200 x 25: (0.79 + 2% of 25) on each ticket is 258.00, and 3% of 5,000 is 150.00.
        $this->assertSame('408.00', $this->money(TicketFees::cost('universe', 200, 25)));
        // 10 x 1,000: 0.79 + 20.00 is capped at 19.95 a ticket (199.50); the 3% is not (300.00).
        $this->assertSame('499.50', $this->money(TicketFees::cost('universe', 10, 1000)));
    }

    public function test_allevents_and_hi_events_charge_a_fee_with_stripe_on_top(): void
    {
        // 200 x 25: 1.00 a ticket (200.00) plus Stripe (145.00 + 60.00).
        $this->assertSame('405.00', $this->money(TicketFees::cost('allevents', 200, 25)));
        // 200 x 25: (1.25% of 25 + 0.60) on each ticket is 182.50, plus Stripe's 205.00.
        $this->assertSame('387.50', $this->money(TicketFees::cost('hi-events', 200, 25)));
    }

    public function test_nothing_sold_or_nothing_charged_costs_nothing(): void
    {
        foreach ($this->platforms() as $platform) {
            $this->assertSame(0.0, TicketFees::cost($platform, 200, 0), "{$platform} charges for a free ticket");
            $this->assertSame(0.0, TicketFees::cost($platform, 0, 25), "{$platform} charges for an empty room");
        }
    }

    /**
     * Each page prints, for the tickets and price it says it is showing, exactly what TicketFees
     * works out for Eventbrite and for us.
     */
    public function test_every_calculator_renders_the_ticket_fees_totals(): void
    {
        foreach (self::PAGES as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match('/data-fee-tickets="(\d+)"/', $html, $tickets), "{$path} names no ticket count");
            $this->assertSame(1, preg_match('/data-fee-price="(\d+(?:\.\d+)?)"/', $html, $price), "{$path} names no ticket price");

            preg_match_all('/data-fee-total="([a-z-]+)"[^>]*>\$([\d,]+\.\d{2})</', $html, $totals, PREG_SET_ORDER);
            $shown = array_column($totals, 2, 1);

            // Every page sets Eventbrite against us, and every total it prints is TicketFees'.
            $this->assertArrayHasKey('eventbrite', $shown, "{$path} renders no Eventbrite total");
            $this->assertArrayHasKey('eventschedule', $shown, "{$path} renders no total of ours");

            foreach ($shown as $platform => $amount) {
                $this->assertSame(
                    $this->money(TicketFees::cost($platform, (int) $tickets[1], (float) $price[1])),
                    $amount,
                    "{$path} prints a {$platform} total TicketFees does not"
                );
            }
        }
    }

    /** /pricing and /for-talent open on the same example event, so they must print the same sum. */
    public function test_the_example_event_costs_the_same_on_pricing_and_for_talent(): void
    {
        $totals = [];

        foreach (['/pricing', '/for-talent'] as $path) {
            preg_match('/data-fee-total="eventbrite"[^>]*>\$([\d,]+\.\d{2})</', $this->get($path)->getContent(), $total);
            $totals[$path] = $total[1] ?? null;
        }

        $this->assertSame(
            $this->money(TicketFees::cost('eventbrite', TicketFees::EXAMPLE_TICKETS, TicketFees::EXAMPLE_PRICE)),
            $totals['/pricing']
        );
        $this->assertSame($totals['/pricing'], $totals['/for-talent']);
    }

    /** A script that recomputes as the visitor types starts from the rates the server used. */
    public function test_every_interactive_calculator_hands_its_script_the_ticket_fees_rates(): void
    {
        foreach (self::INTERACTIVE as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match('/data-rates="([^"]*)"/', $html, $attribute), "{$path} hands its script no rates");

            $rates = json_decode(html_entity_decode($attribute[1], ENT_QUOTES | ENT_HTML5), true);

            $this->assertIsArray($rates, "{$path}: data-rates is not JSON");
            $this->assertArrayHasKey('stripe', $rates);
            // Through JSON on both sides: a whole-number float such as the plan price comes back
            // as an int, which is the same number to the script and not a difference worth failing.
            $expected = json_decode(json_encode(TicketFees::forScript(array_values(array_diff(array_keys($rates), ['stripe'])))), true);

            $this->assertSame($expected, $rates, "{$path} hands its script rates that are not TicketFees'");
            $this->assertStringContainsString('window.esTicketFeeCost', $html, "{$path} is missing the formula its script runs");
        }
    }

    /**
     * The browser's formula IS the PHP one: the partial as it renders, run in Node over every
     * platform and a spread of inputs, must land on the same doubles TicketFees::cost() does.
     * Exact equality, not a tolerance - the two are written in the same order of operations, and
     * the moment they are not, a keystroke can move a total by a cent the first paint never showed.
     */
    public function test_the_browser_formula_matches_ticket_fees(): void
    {
        $partial = view('marketing.partials.ticket-fee-math')->render();
        $script = preg_replace('~</?script\b[^>]*>~', '', $partial);

        $cases = [];
        foreach ($this->platforms() as $platform) {
            foreach ([1, 37, 100, 200, 1234] as $tickets) {
                foreach ([0.5, 1, 4.99, 5, 5.01, 10, 12.5, 25, 99.99, 250, 1000] as $price) {
                    $cases[] = [$platform, $tickets, $price];
                }
            }
        }

        $harness = <<<JS
        global.window = {};
        {$script}
        var input = JSON.parse(require('fs').readFileSync(process.argv[2], 'utf8'));
        process.stdout.write(JSON.stringify(input.cases.map(function (c) {
            return window.esTicketFeeCost(input.rates[c[0]], input.rates.stripe, c[1], c[2]);
        })));
        JS;

        // tempnam() creates its file; the suffixed paths are two more, so all four are removed.
        $harnessBase = tempnam(sys_get_temp_dir(), 'ticketfees');
        $inputBase = tempnam(sys_get_temp_dir(), 'ticketfees');
        $harnessFile = $harnessBase.'.js';
        $inputFile = $inputBase.'.json';

        try {
            file_put_contents($harnessFile, $harness);
            file_put_contents($inputFile, json_encode([
                'rates' => TicketFees::forScript($this->platforms()),
                'cases' => $cases,
            ]));

            $process = new Process(['node', $harnessFile, $inputFile]);
            $process->run();

            // Not skipped when node is missing, for the reason SentryJsFilterTest gives: a skip
            // would let this pin nothing on CI without anyone finding out.
            $this->assertTrue(
                $process->isSuccessful(),
                "The fee formula did not run in Node. If node is missing, install it.\n".$process->getErrorOutput()
            );

            $fromNode = json_decode($process->getOutput(), true);
        } finally {
            @unlink($harnessFile);
            @unlink($inputFile);
            @unlink($harnessBase);
            @unlink($inputBase);
        }

        $this->assertCount(count($cases), $fromNode);

        foreach ($cases as $i => [$platform, $tickets, $price]) {
            $this->assertSame(
                TicketFees::cost($platform, $tickets, $price),
                (float) $fromNode[$i],
                "{$platform} at {$tickets} x {$price}: the browser works out a different total"
            );
        }
    }

    /**
     * No calculator view does fee arithmetic with a number of its own. A decimal literal beside a
     * multiplication, in an @php block or a script, is a rate that did not come from TicketFees -
     * which is exactly how /pricing and /for-talent came to leave a fee out.
     */
    /**
     * The calculator lays its cards out four to a row. A platform list of any other length used to
     * switch to three columns or leave a ragged last row; it is refused instead, so a new list has to
     * be chosen to fill the grid (CLAUDE.md: complete grids).
     */
    public function test_the_calculator_refuses_a_platform_list_that_would_leave_a_ragged_row(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-marketing.fee-calculator :platforms="$p" />', [
            'p' => array_slice(TicketFees::CALCULATOR_PLATFORMS, 0, 4),
        ]);
        $this->assertStringContainsString('lg:grid-cols-4', $html);

        $this->expectException(\Illuminate\View\ViewException::class);
        \Illuminate\Support\Facades\Blade::render('<x-marketing.fee-calculator :platforms="$p" />', [
            'p' => array_slice(TicketFees::CALCULATOR_PLATFORMS, 0, 3),
        ]);
    }

    public function test_no_calculator_view_multiplies_by_a_rate_of_its_own(): void
    {
        $offenders = [];

        foreach (self::SOURCES as $source) {
            $code = File::get(base_path($source));

            preg_match_all('~@php\b(.*?)@endphp|<script\b[^>]*>(.*?)</script>~s', $code, $blocks, PREG_SET_ORDER);

            foreach ($blocks as $block) {
                $body = ($block[1] ?? '').($block[2] ?? '');

                if (preg_match_all('~\*\s*\(?\s*\d*\.\d+|\d*\.\d+\s*\)?\s*\*~', $body, $hits)) {
                    $offenders[] = $source.': '.implode(', ', array_map('trim', $hits[0]));
                }
            }
        }

        $this->assertSame([], $offenders, 'Read these rates from App\Utils\TicketFees instead');
    }
}
