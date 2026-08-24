<?php

namespace Tests\Feature;

use App\Utils\PlatformPricing;
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
    /**
     * PlatformPricing memoizes all four amounts for the process, and this class has no
     * RefreshDatabase, so the static outlives every test in the run. Without these two the
     * config([...]) overrides in the render tests below would be swallowed by a memo an earlier
     * test had already warmed, and the failure would depend on test order.
     */
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
        'for-talent.blade.php' => [
            'tickets at ${{ $feePrice }}',
            'number_format($feeEb, 2)',
            'number_format($feeEs, 2)',
            'number_format($feeKeep, 2)', '<span class="font-bold">$15.00</span>'],
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
        'compare.blade.php' => [
            'number_format($card[\'value\'], 2)',
            'data-odometer="${{ number_format($calcSaving, 0) }}"',
            '\'note\' => \'$\'.$rates[\'eventschedule\'][\'monthly\']',
        ],
        'for-breweries-and-wineries.blade.php' => [
            '${{ $wPrice }}',
        ],
        'for-comedy-clubs.blade.php' => [
            '${{ $headline[3] }}',
            '${{ $headline[4] }}',
            '${{ $nAdv }}',
        ],
        'for-restaurants.blade.php' => [
            '${{ $sitting[\'price\'] }}',
        ],
        'pricing.blade.php' => [
            '/month + Stripe, 0% platform fee',
            'number_format($calcEb, 2)',
            'number_format($calcEs, 2)',
            'number_format($calcSave, 2)',
        ],
    ];

    /** Price literals that are ours, across every generation. Retired ones must not come back. */
    private const PLAN_AMOUNTS = '5|9|12|15|19|29|50|90|150|290';

    private function marketingViews(): array
    {
        return File::allFiles(resource_path('views/marketing'));
    }

    /**
     * The admin-portal views that quote one of OUR prices. They are not under views/marketing,
     * so nothing guarded them - which is exactly how a hardcoded '$' survived the sweep in
     * show-admin-plan's wind-down banner and in two Chart.js axis callbacks.
     */
    private const AP_PRICE_VIEWS = [
        // admin/dashboard was the file the bug report named. It quotes ARR through plan_price()
        // and used to print the boost markup tile through a hardcoded Meta currency, and it was
        // the one admin money page this list did not cover.
        'admin/dashboard.blade.php',
        'admin/settings.blade.php',
        'role/show-admin-plan.blade.php',
        'subscription/show.blade.php',
        'referral/index.blade.php',
        'components/plan-gate.blade.php',
        'admin/growth.blade.php',
        'admin/revenue.blade.php',
        'admin/boost.blade.php',
    ];

    private function apPriceViews(): array
    {
        $out = [];

        foreach (self::AP_PRICE_VIEWS as $relative) {
            $out[$relative] = resource_path('views/'.$relative);
        }

        return $out;
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
     * The CURRENCY, not the amount. The amounts were centralised long before the symbol was, so
     * every plan price on the site was written "${{ $proMonthly }}" - config moved the number and
     * the dollar sign stayed welded on, which is what made a non-USD operator edit Blade files by
     * hand on every upgrade. plan_price() renders both together.
     */
    public function test_no_hardcoded_currency_symbol_on_a_rendered_price(): void
    {
        $offenders = [];

        foreach ($this->marketingViews() as $file) {
            $relative = str_replace(resource_path('views/marketing').'/', '', $file->getPathname());

            foreach (explode("\n", File::get($file->getPathname())) as $index => $line) {
                if ($this->isAllowed($relative, $line)) {
                    continue;
                }

                // "${{ ... }}" - a dollar glued to the front of any echoed value.
                if (str_contains($line, '${{')) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded currency symbol in front of a rendered price. Use plan_price($amount), '
            ."which renders the installation's own currency:\n".implode("\n", $offenders));
    }

    /**
     * The same thing one layer down: a '$' concatenated onto a price inside a PHP block, in a
     * view or in the controller that builds the comparison tables and FAQ arrays.
     */
    public function test_no_concatenated_currency_symbol_on_a_plan_price(): void
    {
        $offenders = [];

        $files = $this->marketingViews();
        $sources = [];
        foreach ($files as $file) {
            $sources[str_replace(resource_path('views/marketing').'/', '', $file->getPathname())] = $file->getPathname();
        }
        $sources['MarketingController.php'] = app_path('Http/Controllers/MarketingController.php');

        foreach ($sources as $relative => $path) {
            foreach (explode("\n", File::get($path)) as $index => $line) {
                if ($this->isAllowed($relative, $line)) {
                    continue;
                }

                // "$'.$proMonthly" / "$' . $proMonthly" / "$'.$this->planPrice()", and the
                // "'$'.$var" form. Anchored on our own price variables so a competitor's
                // published USD pricing stays untouched.
                if (preg_match('~\$\x27\s*\.\s*\$(proMonthly|proYearly|entMonthly|entYearly|this->planPrice)~', $line)
                    || preg_match('~\x27\$\x27\s*\.\s*\$(proMonthly|proYearly|entMonthly|entYearly|this->planPrice)~', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            "Currency symbol concatenated onto a plan price. Use plan_price(\$amount):\n"
            .implode("\n", $offenders));
    }

    /**
     * Structured data has to name the same currency the visible price is rendered in, or the
     * offer Google reads disagrees with the page.
     */
    public function test_structured_data_does_not_hardcode_the_currency(): void
    {
        $offenders = [];

        foreach ($this->marketingViews() as $file) {
            $relative = str_replace(resource_path('views/marketing').'/', '', $file->getPathname());

            foreach (explode("\n", File::get($file->getPathname())) as $index => $line) {
                if (preg_match('~"priceCurrency"\s*:\s*"[A-Z]{3}"~', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            "Hardcoded priceCurrency in JSON-LD. Use platform_currency():\n".implode("\n", $offenders));
    }

    /**
     * The admin portal quotes our prices too, and had no guard at all.
     */
    public function test_no_hardcoded_currency_symbol_in_the_admin_portal(): void
    {
        $offenders = [];

        foreach ($this->apPriceViews() as $relative => $path) {
            foreach (explode("\n", File::get($path)) as $index => $line) {
                // "${{ ... }}" in the markup, or "'$'." concatenated onto a value in a PHP block.
                if (str_contains($line, '${{') || preg_match('~\x27\$\x27\s*\.~', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded currency symbol on an admin-portal price. Use plan_price($amount) for our '
            ."own prices, or MoneyUtils::format(\$amount, \$row->currency_code) for a row's:\n"
            .implode("\n", $offenders));
    }

    /**
     * The same thing in JavaScript. Two Chart.js axis callbacks kept a literal '$' while the
     * cards above them were converted, so the axis and the figure disagreed.
     *
     * Scoped to the admin views on purpose: the marketing site has eight legitimate '$' + …
     * formatters in the Eventbrite fee calculators, and adding eight exemptions to silence a
     * check is worse than not having the check.
     */
    public function test_no_hardcoded_currency_symbol_in_admin_javascript(): void
    {
        $offenders = [];

        foreach ($this->apPriceViews() as $relative => $path) {
            foreach (explode("\n", File::get($path)) as $index => $line) {
                if (preg_match('~\x27\$\x27\s*\+~', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded currency symbol in admin JavaScript. Emit the symbol with @json(...) and '
            ."reference it, so the chart cannot disagree with the page:\n".implode("\n", $offenders));
    }

    /**
     * Every ALLOWED snippet must still match the copy it was written for.
     *
     * The class docblock has promised this test for a while but it was never written, so an
     * exemption could outlive its line: once the copy moves, the snippet stops matching, stops
     * protecting anything, and quietly widens the guard instead of narrowing it.
     */
    public function test_the_allow_list_has_no_stale_entries(): void
    {
        $stale = [];

        foreach (self::ALLOWED as $relative => $snippets) {
            $path = resource_path('views/marketing/'.$relative);

            if (! File::exists($path)) {
                $stale[] = "{$relative}: file no longer exists";

                continue;
            }

            $contents = File::get($path);

            foreach ($snippets as $snippet) {
                if (! str_contains($contents, $snippet)) {
                    $stale[] = "{$relative}: no line matches ".trim(mb_substr($snippet, 0, 80));
                }
            }
        }

        $this->assertSame([], $stale,
            'Stale ALLOWED entries. The copy they exempt has changed, so they now protect nothing '
            ."- delete them or update them to match:\n".implode("\n", $stale));
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
        // Deliberately still config-driven: with no Setting rows this is the FRESH-INSTALL path,
        // proving an operator who never opens /admin/settings still tracks .env. The
        // admin-set path is covered by PlatformPricingTest, which has a database.
        PlatformPricing::flush();

        $controller = app(\App\Http\Controllers\MarketingController::class);
        $method = new \ReflectionMethod($controller, 'planPrice');
        $method->setAccessible(true);

        // Both halves assert a literal. Comparing the enterprise half against
        // config() instead restates the implementation, so it passed even while
        // an empty STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT priced the plan at 0.
        // Floats: plan amounts are settable to two decimal places, so planPrice() stopped
        // casting to int - 14.50 used to be quoted as 14.
        $this->assertSame(77.0, $method->invoke($controller, false));
        $this->assertSame(88.0, $method->invoke($controller, true));
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
        PlatformPricing::flush();

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

    /**
     * Our own ZERO. The free tier's price and "we take $0" platform-fee figures are just as much
     * our own money as the plan amounts, and PLAN_AMOUNTS has no 0 in it, so every one of them
     * was invisible to every check above. Six survived the sweep and shipped, one of them
     * rendering "From $0/mo (Pro R9/mo)" - two currencies in eight words.
     *
     * Not solved by adding 0 to PLAN_AMOUNTS: that would fire on Stripe's $0.30, which is
     * genuinely their USD. Anchored on the wording that makes a zero OURS instead.
     */
    public function test_no_hardcoded_zero_on_our_own_price(): void
    {
        $offenders = [];

        $sources = [];
        foreach ($this->marketingViews() as $file) {
            $sources[str_replace(resource_path('views/marketing').'/', '', $file->getPathname())] = $file->getPathname();
        }
        $sources['MarketingController.php'] = app_path('Http/Controllers/MarketingController.php');

        // "platform fee", "we take", "0% platform fee", "From $0/mo" - the phrases that make a
        // zero a claim about what WE charge.
        $ourZero = '~(platform fee|we take|our fee|free forever|\bFrom\b)~i';

        foreach ($sources as $relative => $path) {
            foreach (explode("\n", File::get($path)) as $index => $line) {
                if ($this->isAllowed($relative, $line)) {
                    continue;
                }

                // A bare $0 or $0.00, but never $0.30 / $0.28 and friends - those are the payment
                // processors' own published fees.
                if (preg_match('~\$0(\.00)?\b(?!\.)~', $line) && preg_match($ourZero, $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded zero on one of our own figures. The free tier and the platform fee follow '
            ."the installation's currency like every other price we quote - use plan_price(0):\n"
            .implode("\n", $offenders));
    }

    /**
     * The currency SOURCE, not the glyph.
     *
     * The bug that prompted this test had no '$' anywhere near it: three admin tiles formatted
     * with config('services.meta.default_currency', 'USD'), a variable absent from .env.example
     * and therefore USD on every selfhost. So "Boost markup revenue" printed $0 on an install
     * set to ZAR, directly above a chart axis already rendering R. No $-anchored check can see
     * that - it has to look at where the code gets a currency from.
     */
    public function test_no_admin_view_takes_a_display_currency_from_an_ad_platform_config(): void
    {
        $offenders = [];

        // Deliberately not the whole admin tree: admin/boost's Meta-only spend tiles and its
        // per-campaign budget ceiling really are denominated in the Meta ad account's currency,
        // and that file declares $boostCurrency once at the top for them.
        $views = [
            'admin/dashboard.blade.php',
            'admin/revenue.blade.php',
            'admin/growth.blade.php',
            'home/panels/revenue.blade.php',
            'home/panels/boosts.blade.php',
        ];

        foreach ($views as $relative) {
            $path = resource_path('views/'.$relative);

            foreach (explode("\n", File::get($path)) as $index => $line) {
                if (preg_match("~config\(\s*['\"](services\.meta\.default_currency|ads\.native_currency)~", $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'A display currency taken from an ad-platform config. Those name the Meta ad account '
            .'and the promotions rail, default to USD and are unset on nearly every install, so '
            .'they print dollars on a platform that quotes something else. Resolve it from the '
            ."rows (BoostBillingService::markupCurrency()) or from PlatformCurrency:\n"
            .implode("\n", $offenders));
    }

    /**
     * JSON-LD is emitted from PHP too. Event::getStructuredDataOffers() hardcoded USD on the
     * free-event offer, which the views-only scan above could never reach.
     */
    public function test_no_hardcoded_price_currency_outside_the_views(): void
    {
        $offenders = [];

        $paths = [
            'app/Models/Event.php' => app_path('Models/Event.php'),
            'app/Http/Controllers/MarketingController.php' => app_path('Http/Controllers/MarketingController.php'),
            'app/Http/Controllers/RoleController.php' => app_path('Http/Controllers/RoleController.php'),
        ];

        foreach ($paths as $relative => $path) {
            if (! File::exists($path)) {
                continue;
            }

            foreach (explode("\n", File::get($path)) as $index => $line) {
                if (preg_match("~['\"]priceCurrency['\"]\s*=>\s*['\"][A-Z]{3}['\"]~", $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Hardcoded priceCurrency in PHP-built structured data. Use the row currency where '
            ."there is one, then platform_currency():\n".implode("\n", $offenders));
    }

    /**
     * The display/billing split, from the display side.
     *
     * Plan amounts are admin-settable through App\Utils\PlatformPricing. If any surface keeps
     * reading the raw config key, the admin panel moves /pricing and leaves /faq advertising the
     * old number - the exact desync the view composer was created to prevent.
     */
    public function test_nothing_outside_the_allow_list_reads_the_raw_amount_config(): void
    {
        // Each entry states WHY, so an exemption cannot be added without one.
        $allowed = [
            'config/services.php' => 'defines the keys',
            'app/Utils/PlatformPricing.php' => 'is the reader every other caller goes through',
            'app/Utils/PlanPriceUtils.php' => 'answers what Stripe charges, not what we advertise',
            'app/Services/GrowthExportService.php' => 'is revenue reporting, which must not follow a marketing change',
        ];

        $offenders = [];

        foreach ($this->allSourceFiles() as $relative => $path) {
            if (isset($allowed[$relative])) {
                continue;
            }

            foreach (explode("\n", File::get($path)) as $index => $line) {
                // The comments in PlanPriceUtils and GrowthExportService that explain the split
                // name the class, not the config key, so nothing here needs a comment skip.
                if (preg_match('~stripe_platform\.[a-z_]*price_[a-z_]*amount~', $line)) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }
        }

        $this->assertSame([], $offenders,
            'Raw plan-amount config read outside the allow list. Use App\Utils\PlatformPricing, '
            ."or the composer-provided \$proMonthly / \$proYearly / \$entMonthly / \$entYearly:\n"
            .implode("\n", $offenders));
    }

    /**
     * And from the billing side, which is the half that actually costs money if it drifts.
     *
     * amountFor() stands in for a Stripe API call and feeds ARR, MRR and renewal emails. Wiring
     * it to the admin-settable amounts would let someone running a promotion quote a customer a
     * renewal figure their card will never be charged, and restate revenue already booked.
     */
    public function test_the_billing_fact_readers_stay_on_config(): void
    {
        $mustNotUse = [
            'app/Utils/PlanPriceUtils.php' => app_path('Utils/PlanPriceUtils.php'),
            'app/Services/GrowthExportService.php' => app_path('Services/GrowthExportService.php'),
        ];

        foreach ($mustNotUse as $relative => $path) {
            $offenders = [];

            foreach (explode("\n", File::get($path)) as $index => $line) {
                // Comments are where the rule is written down, so they are allowed to name it.
                $trimmed = ltrim($line);

                if (str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                if (str_contains($line, 'PlatformPricing')) {
                    $offenders[] = "{$relative}:".($index + 1).': '.trim(mb_substr($line, 0, 140));
                }
            }

            $this->assertSame([], $offenders,
                "{$relative} answers what Stripe actually charges and must keep reading config, "
                ."not the admin-settable amounts:\n".implode("\n", $offenders));
        }
    }

    /**
     * Every PHP and Blade source under app/ and resources/views/, keyed by repo-relative path.
     */
    private function allSourceFiles(): array
    {
        $out = [];

        foreach ([app_path() => 'app', resource_path('views') => 'resources/views'] as $root => $prefix) {
            foreach (File::allFiles($root) as $file) {
                if (! in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $out[$prefix.'/'.str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname())] = $file->getPathname();
            }
        }

        $out['config/services.php'] = config_path('services.php');

        return $out;
    }
}
