<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The free plan SELLS. Role::ticketSaleLimit() allows 25 paid tickets a calendar
 * month per schedule, and TicketController::scan() has no plan check at all, so
 * "ticketing is a Pro feature" has been wrong since that shipped.
 *
 * It was corrected on 21 pages in one sweep and it came BACK on twelve more,
 * because a token grep cannot see it: the claim is not a token, it is a sentence,
 * and every page writes it in its own voice. "Ticketing is on the Pro plan",
 * "Selling covers is on the Pro plan", "Ticketing with QR check-in ... are on the
 * Pro plan", "Ticketing and newsletters are available on the Pro and Enterprise
 * plans". Each of those was found by reading, one page at a time, twice.
 *
 * So the shapes are pinned here instead. The rule this encodes:
 *
 *   - Selling TICKETS is free to a monthly ceiling; Pro removes the ceiling.
 *   - Scanning a ticket at the door is free. The live check-in DASHBOARD is Pro.
 *   - Selling GIFT CARDS, passes, add-ons and promo codes really is Pro, so those
 *     sentences must keep working.
 *
 * A page is free to say any of that in its own words. What it may not do is assert
 * the flat negation, and that is all this test looks for.
 */
class MarketingTicketingTierTest extends TestCase
{
    /** Sentences that assert selling tickets is behind a paid plan. */
    private const FORBIDDEN = [
        // "Ticketing is on the Pro plan", "Ticketing ... are on the Pro plan",
        // "Ticketing is available on the Pro plan", "included in the Pro plan".
        '/\bTicketing\b[^.]{0,140}?\b(?:is|are)\s+(?:only\s+)?(?:on|available on|included (?:in|on))\s+the\s+Pro\b/i',

        // "Selling tickets is on the Pro plan", "Selling covers is on the Pro plan".
        // Deliberately anchored on what is being sold: gift cards and passes are Pro
        // and must not match.
        '/\bSelling\b[^.]{0,30}\b(?:tickets?|covers|seats|admission)\b[^.]{0,160}?\b(?:is|are)\s+(?:only\s+)?on\s+the\s+Pro\b/i',

        // "sell tickets ... requires Pro" / "only on the Pro plan".
        '/\bsell(?:ing)?\s+(?:paid\s+)?tickets?\b[^.]{0,120}?\b(?:requires?\s+(?:the\s+)?Pro|only\s+on\s+(?:the\s+)?Pro)\b/i',

        // "Event Schedule is ... $5/month for ticketing" - the price-first shape,
        // which says the same thing without naming a plan. Two guards: it has to
        // be about US (a competitor's own "$16/month for a basic site, plus extra
        // for third-party ticketing" is a fact about them), and "for UNLIMITED
        // ticketing" is true and has to keep working.
        '/Event\s+Schedule\b[^.]{0,160}\bmonth\b[^.]{0,40}\bfor\s+(?!unlimited\b)(?:[\w-]+\s+){0,2}ticketing\b/i',

        // "$5 a month to start selling tickets".
        '/Event\s+Schedule\b[^.]{0,160}\bmonth\b[^.]{0,40}\bto\s+(?:start\s+)?sell(?:ing)?\s+(?:paid\s+)?tickets\b/i',

        // Scanning at the door has no plan check. The DASHBOARD does, so the word
        // "dashboard" anywhere in the sentence exempts it.
        '/\b(?:QR\s+check-in|scan(?:ning)?\s+(?:the\s+)?(?:QR|tickets?))\b(?:(?!dashboard)[^.]){0,120}?\b(?:is|are)\s+(?:only\s+)?on\s+the\s+Pro\b/i',
    ];

    /** @return array<string, string> path => contents */
    private function marketingSources(): array
    {
        $paths = array_merge(
            File::glob(resource_path('views/marketing/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*/*.blade.php')),
            [
                lang_path('en/marketing.php'),
                app_path('Http/Controllers/MarketingController.php'),
                // The RENDERED comparison and replacement data. The controller
                // builds these sentences by concatenating plan_price() into the
                // middle of them, so a claim like "$5/month for ticketing" is
                // never a contiguous string in the source and a source-only scan
                // cannot see it. These fixtures hold the finished text.
                base_path('tests/fixtures/comparison_data.json'),
                base_path('tests/fixtures/replacement_data.json'),
            ]
        );

        $out = [];
        foreach ($paths as $p) {
            if (is_file($p)) {
                $out[str_replace(base_path().'/', '', $p)] = file_get_contents($p);
            }
        }

        return $out;
    }

    public function test_no_marketing_surface_says_selling_tickets_is_a_paid_feature(): void
    {
        $offences = [];

        foreach ($this->marketingSources() as $path => $body) {
            // Comments explaining the rule quote the wrong claim on purpose, so
            // strip them first. Block comments need /s (they span lines); the
            // line-comment arm must NOT have it, or `.*$` runs past every newline
            // to the end of the file and the first `//` in a view silently blanks
            // the rest of it - which is exactly what the first draft of this test
            // did, and it is why it passed with a reintroduced claim in front of it.
            $body = preg_replace('~\{\{--.*?--\}\}|/\*.*?\*/~s', ' ', $body);
            $body = preg_replace('~^[ \t]*//.*$~m', ' ', $body);

            foreach (self::FORBIDDEN as $pattern) {
                if (preg_match_all($pattern, $body, $m)) {
                    foreach ($m[0] as $hit) {
                        $offences[] = $path.': "'.trim(preg_replace('/\s+/', ' ', $hit)).'"';
                    }
                }
            }
        }

        $this->assertSame([], $offences, implode("\n", array_merge(
            ['These say selling tickets is behind a paid plan. Role::ticketSaleLimit() allows '
                .config('usage.ticket_sale_monthly_limit_free')
                .' paid tickets a month on the free plan, and scanning them is not gated at all.'],
            $offences
        )));
    }

    public function test_the_stated_free_allowance_matches_the_config(): void
    {
        $limit = (int) config('usage.ticket_sale_monthly_limit_free');
        $this->assertGreaterThan(0, $limit);

        $seen = 0;
        $wrong = [];

        foreach ($this->marketingSources() as $path => $body) {
            // "25 paid tickets a month", "25 paid ones a month", "up to 25 a month".
            // The per-OWNER backstop is a different number for a different thing
            // (config('usage.ticket_sale_user_monthly_limit_free')), so a figure
            // followed by "across" is checked against that one instead.
            preg_match_all(
                '/\b(\d{1,4})\s+(?:paid\s+(?:tickets?|ones)|tickets?)\s+(?:a|per)\s+(?:calendar\s+)?month\b(?!\s+across)/i',
                $body,
                $m
            );
            foreach ($m[1] as $stated) {
                $seen++;
                if ((int) $stated !== $limit) {
                    $wrong[] = "{$path}: says {$stated}";
                }
            }
        }

        $this->assertSame([], $wrong,
            "the free monthly paid-ticket allowance is {$limit}; these say otherwise:\n".implode("\n", $wrong));
        $this->assertGreaterThan(10, $seen,
            'almost nothing states the free selling allowance any more, so this test pins nothing');
    }

    public function test_the_stated_owner_backstop_matches_the_config(): void
    {
        $backstop = (int) config('usage.ticket_sale_user_monthly_limit_free');
        $wrong = [];
        $seen = 0;

        foreach ($this->marketingSources() as $path => $body) {
            preg_match_all(
                '/\b(\d{1,4})\s+paid\s+tickets?\s+(?:a|per)\s+(?:calendar\s+)?month\s+across\b/i',
                $body,
                $m
            );
            foreach ($m[1] as $stated) {
                $seen++;
                if ((int) $stated !== $backstop) {
                    $wrong[] = "{$path}: says {$stated}";
                }
            }
        }

        $this->assertSame([], $wrong,
            "the per-owner backstop is {$backstop}; these say otherwise:\n".implode("\n", $wrong));
        $this->assertGreaterThan(0, $seen, 'nothing documents the per-owner backstop any more');
    }
}
