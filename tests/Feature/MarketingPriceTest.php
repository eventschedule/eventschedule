<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Keeps every plan price the marketing site quotes tied to config.
 *
 * These used to be hardcoded in ~114 places across 48 pages, so changing
 * STRIPE_PRICE_MONTHLY_AMOUNT moved /pricing and left the rest of the site advertising the
 * old number - including the JSON-LD offers Google reads. AppServiceProvider's composer
 * shares $proMonthly/$proYearly/$entMonthly/$entYearly with every marketing.* view; these
 * tests fail if a bare literal creeps back in.
 */
class MarketingPriceTest extends TestCase
{
    /** Words that mark a number as a PLAN price rather than an example ticket price. */
    private const PLAN_WORDS = '\bPro\b|Enterprise|\bEnt\b|\bplan\b|a month|per month|a year|per year|/mo\b|monthly|yearly';

    /**
     * Example prices that are legitimately hardcoded: gift-card denominations, boost budgets,
     * sample ticket prices, the SaaS calculator's slider range.
     *
     * Keyed file => distinctive snippets of the exempt line. This used to be keyed by line
     * number, which silently drifted onto an innocent line the moment anything was inserted
     * above it - exempting a line nobody chose while re-arming the one that was checked.
     * test_the_allow_list_has_no_stale_entries() fails if a snippet stops matching, so an
     * exemption cannot outlive the copy it was written for.
     */
    private const ALLOWED = [
        'boost.blade.php' => [
            "'Three landed', '\$50'",
            'es-launch-ink font-bold">$15.00',
        ],
        'docs/boost.blade.php' => [
            'The budget starts at the site minimum, $5 by default',
            '<td>$50</td>',
            'dark:text-white">$15.00</span>',
        ],
        'for-comedians.blade.php' => ['Front Row + Meet & Greet'],
        'for-musicians.blade.php' => ['<span>GA TICKET x2</span>'],
        'for-nightclubs.blade.php' => ["['Before 11pm', '\$10'"],
        'for-talent.blade.php' => ['<span class="font-bold">$15.00</span>'],
        'gift-cards.blade.php' => ["['\$50', false]", "['\$150', false]"],
        // /saas sells running your OWN platform on Event Schedule, so its Free/$29/$99 tier
        // mockups and "+$29/mo after trial" badges are the READER's pricing, not ours. They
        // stay hardcoded even though $29 collides with the Enterprise price.
        'saas.blade.php' => [
            "['Free', '\$0'], ['Pro', '\$29'], ['Ent', '\$99']",
            '<span>$5</span><span>$199</span>',
            "'acme upgraded to Pro', '+\$29/mo'",
            "'Headliner Showcase'",
            'the $22/$15 ticket',
            "['Free', '\$0', 0], ['Pro', '\$29', 1]",
            '$29/mo after trial',
        ],
    ];

    /** Price literals that are ours, across every generation. Retired ones must not come back. */
    private const PLAN_AMOUNTS = '5|9|12|15|19|29|50|90|150|290';

    private function marketingViews(): array
    {
        return File::allFiles(resource_path('views/marketing'));
    }

    private function isAllowed(string $relative, string $line): bool
    {
        foreach (self::ALLOWED[$relative] ?? [] as $snippet) {
            if (str_contains($line, $snippet)) {
                return true;
            }
        }

        return false;
    }

    public function test_no_hardcoded_plan_price_in_marketing_prose(): void
    {
        $offenders = [];

        foreach ($this->marketingViews() as $file) {
            $relative = str_replace(resource_path('views/marketing').'/', '', $file->getPathname());
            $lines = explode("\n", File::get($file->getPathname()));

            foreach ($lines as $index => $line) {
                $number = $index + 1;

                if ($this->isAllowed($relative, $line)) {
                    continue;
                }

                // A price literal is only a problem when it sits next to plan wording -
                // "$18 on the door" in a sample ticket table is fine and must stay.
                // '~' delimiter, not '/': PLAN_WORDS contains "/mo", which would close a
                // '/'-delimited pattern and turn the rest into invalid modifiers.
                if (preg_match('~\$('.self::PLAN_AMOUNTS.')\b~', $line)
                    && preg_match('~'.self::PLAN_WORDS.'~i', $line)) {
                    $offenders[] = "{$relative}:{$number}: ".trim(mb_substr($line, 0, 120));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded plan price in marketing copy. Use the composer-provided $proMonthly / '
            ."\$proYearly / \$entMonthly / \$entYearly instead:\n".implode("\n", $offenders));
    }

    /**
     * Prices spelled out in words. These are invisible to any $-anchored search, which is
     * exactly how ~38 of them survived the first pass of the sweep.
     */
    public function test_no_plan_price_spelled_out_in_words(): void
    {
        $offenders = [];

        foreach ($this->marketingViews() as $file) {
            $relative = str_replace(resource_path('views/marketing').'/', '', $file->getPathname());

            foreach (explode("\n", File::get($file->getPathname())) as $index => $line) {
                // "dollars" is required, so "capped at five" team members, "twenty-five paid
                // tickets" and "Fifteen minutes" stay legal. The two trailing alternatives are
                // the phrasings that named a price without the word "dollars".
                if (preg_match('~\b(five|nine|twelve|fifteen|nineteen|fifty|ninety)\s+dollars\b'
                    .'|\bEnterprise is (fifteen|twenty-nine)\b|\bor fifteen for\b~i', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 120));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Plan price written out in words. Use the composer-provided variables so a config '
            ."change moves this copy too:\n".implode("\n", $offenders));
    }

    /**
     * The comparison and replacement pages build their tables, FAQs and JSON-LD in PHP arrays
     * inside MarketingController, which this test only scanned views for - so ~94 quotes of the
     * old $5/mo survived a price change and shipped on the same documents whose offers block
     * already emitted the new one. The composer does not reach the controller, so it reads
     * config directly there; either way a literal must not come back.
     */
    public function test_no_hardcoded_plan_price_in_the_marketing_controller(): void
    {
        $path = app_path('Http/Controllers/MarketingController.php');
        $offenders = [];

        // Competitors' prices are quoted on purpose and must stay literal. They are stripped by
        // exact phrase rather than inferred from marker words: an earlier version of this test
        // required a word like "Event Schedule" or "flat" on the same line, which 39 of the 94
        // literals it exists to catch would have failed - including every comparison-table row,
        // where the price sits alone in an array of strings with no prose around it.
        $competitorPrices = [
            '$50', '$59', '$99', '$1.79', '$5,999', '$0.28',
            'Canva Pro costs $15/month', 'From $15/mo (Pro)',
            '$5 to $15/month', '$5 to $15/mo',
            // Linktree Pro and AddEvent happen to sit exactly on our Pro and Enterprise prices,
            // so these three read as ours to any bare pattern. Exempted by full phrase, not by
            // the number, so an actual hardcoded $9 or $29 of our own still fails.
            'Linktree Pro costs $9/month', 'From $9/mo (Pro)',
            'calendar buttons at $29/mo',
        ];

        foreach (explode("\n", File::get($path)) as $index => $line) {
            $number = $index + 1;
            $trimmed = ltrim($line);

            // Comments explain the prices; they do not render.
            if (str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            // A line can legitimately carry a competitor number AND one of ours, so strip the
            // known competitor phrases before looking for a literal of our own.
            $stripped = str_replace($competitorPrices, '', $line);

            // Both generations. The retired 5/15/50/150 must not return, and the current
            // 9/29/90/290 must not be written down either - that is the literal the NEXT price
            // change would leave behind, and the old regex could not see it.
            if (preg_match('~\$('.self::PLAN_AMOUNTS.')\b~', $stripped)) {
                $offenders[] = "MarketingController.php:{$number}: ".trim(mb_substr($line, 0, 120));
            }
        }

        $this->assertSame([], $offenders,
            "Hardcoded plan price in MarketingController. Use \$this->planPrice():\n"
            .implode("\n", $offenders));
    }

    /** And it has to actually move when the config does. */
    public function test_the_comparison_pages_quote_the_configured_price(): void
    {
        config([
            'services.stripe_platform.price_monthly_amount' => '77',
            'services.stripe_platform.enterprise_price_monthly_amount' => '88',
        ]);

        $controller = app(\App\Http\Controllers\MarketingController::class);
        $method = new \ReflectionMethod($controller, 'planPrice');
        $method->setAccessible(true);

        // Both halves assert a literal. Comparing the enterprise half against
        // config() instead restates the implementation, so it passed even while
        // an empty STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT priced the plan at 0.
        $this->assertSame(77, $method->invoke($controller, false));
        $this->assertSame(88, $method->invoke($controller, true));
    }

    public function test_no_hardcoded_plan_price_in_structured_data(): void
    {
        $offenders = [];

        foreach ($this->marketingViews() as $file) {
            $relative = str_replace(resource_path('views/marketing').'/', '', $file->getPathname());
            $lines = explode("\n", File::get($file->getPathname()));

            foreach ($lines as $index => $line) {
                // Only the JSON-LD shape: "price" alone on its line with a quoted value.
                // Inline example payloads in the API docs use "price": 25 and are not matched.
                if (preg_match('/^\s*"price"\s*:\s*"([\d.]+)"/', $line, $m) && (float) $m[1] !== 0.0) {
                    $offenders[] = "{$relative}:".($index + 1).": \"price\": \"{$m[1]}\"";
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded price in JSON-LD structured data - this is what Google shows in search '
            .'results. Use "{{ $proMonthly }}" (or number_format(...) for a 2dp value); '
            ."a free-tier offer should be \"0\":\n".implode("\n", $offenders));
    }

    /**
     * The point of the sweep: a config change must actually move the rendered pages.
     *
     * Rendered through the view factory rather than over HTTP. Marketing routes are gated on
     * IS_HOSTED, which phpunit.xml does not set, so a GET would only ever see a redirect -
     * and route registration happens at boot, before a test could config() its way past it.
     * These four views take no controller data, so rendering them directly is equivalent and
     * still exercises the AppServiceProvider composer.
     */
    public function test_marketing_views_render_the_configured_price(): void
    {
        config([
            'services.stripe_platform.price_monthly_amount' => '77',
            'services.stripe_platform.enterprise_price_monthly_amount' => '88',
        ]);

        // One view per context the sweep had to handle: HTML prose, an @php FAQ array,
        // JSON-LD structured data, and an Enterprise-priced page.
        $expectations = [
            'marketing.pricing' => '77',
            'marketing.gift-cards' => '77',
            'marketing.white-label' => '77',
            'marketing.custom-domain' => '88',
        ];

        foreach ($expectations as $view => $expected) {
            $html = view($view)->render();

            $this->assertStringContainsString($expected, $html,
                "{$view} does not render the configured price, so it is still hardcoded.");
            $this->assertDoesNotMatchRegularExpression(
                '~(Pro|Enterprise)[^<>]{0,40}\$(5|15)\b~', $html,
                "{$view} still renders a hardcoded plan price."
            );
        }
    }
}
