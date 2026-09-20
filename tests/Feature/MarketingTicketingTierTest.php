<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Selling PAID tickets is Pro/Enterprise. This is the inverse of the guard that stood here from
 * 2026-07-30 to 2026-09-20, when the free plan sold 25 paid tickets a calendar month.
 *
 * The claim is not a token, it is a sentence, and every page writes it in its own voice: "the free
 * plan sells 25 paid tickets a month", "selling starts free", "25 paid drop-ins", "Free to 25 a
 * month", "twenty-five paid covers". A token grep finds about three quarters of them. So the
 * shapes are pinned here instead.
 *
 * The rule this encodes:
 *
 *   - Selling tickets with a PRICE is Pro/Enterprise.
 *   - Free registration, RSVP and $0 ticket rows are unlimited on every tier.
 *   - Scanning a ticket at the door is free; the live check-in DASHBOARD is Pro.
 *   - There is no platform fee on any tier, which is a fee claim and stays true.
 *
 * A page is free to say any of that in its own words. What it may not do is assert that paid
 * selling is free, or quote the old monthly allowance, and that is all this test looks for.
 */
class MarketingTicketingTierTest extends TestCase
{
    /** Sentences that assert paid ticket selling is free, or quote the retired allowance. */
    private const FORBIDDEN = [
        // The allowance figure itself, in every phrasing the sweep found: "25 paid tickets a
        // month", "25 paid ones", "25 paid spots/places/covers/drop-ins", "twenty-five paid ...".
        // Anchored on "paid" so a genuine 25 (25 fan photos, 25 endpoints) is not a false hit.
        '/\b(?:25|twenty-five)\b[^.]{0,40}\bpaid\b[^.]{0,40}\b(?:tickets?|ones|spots|places|covers|drop-ins|sales)\b/i',

        // The same figure with the words the other way round: "paid tickets ... 25 a month".
        '/\bpaid\s+tickets?\b[^.]{0,60}\b(?:25|twenty-five)\b[^.]{0,20}\ba\s+month\b/i',

        // "the free plan sells", "a free plan that sells", "Free plan ... sells up to".
        '/\bfree\s+plan\b[^.]{0,60}\bsells?\b/i',

        // "selling is free", "selling starts free", "selling itself is not gated".
        '/\bselling\b[^.]{0,40}\b(?:is|starts|begins)\s+(?:itself\s+)?free\b/i',
        '/\bselling\s+(?:tickets\s+)?itself\b[^.]{0,40}\b(?:is\s+)?(?:free|not\s+gated)\b/i',

        // "sell tickets on every plan", "selling is included on every plan". Deliberately anchored
        // on selling: "scanning at the door is free on every plan" and "no platform fee on any
        // plan" are both true and must keep working.
        '/\bsell(?:ing|s)?\s+(?:paid\s+)?tickets?\b[^.]{0,80}\bon\s+(?:every|any|all)\s+(?:plan|tier)/i',
        '/\bselling\b[^.]{0,40}\b(?:is\s+)?included\s+on\s+every\s+plan\b/i',

        // "paid tickets on the free plan", "priced tickets ... Free plan".
        '/\b(?:paid|priced)\s+tickets?\b[^.]{0,60}\bon\s+the\s+free\s+plan\b/i',

        // The per-owner backstop, which was the other half of the retired allowance.
        '/\b(?:50|fifty)\b[^.]{0,40}\bpaid\s+tickets?\b[^.]{0,60}\bacross\b/i',

        // "25 tickets a month" with no "paid" token - which is what the retired pricing bullet
        // literally said ("Sell up to 25 tickets a month"), and the shape every pattern above
        // misses because they all require \bpaid\b.
        '/\b(?:25|twenty-five)\b[^.]{0,40}\btickets?\b[^.]{0,30}\b(?:a|per)\s+(?:calendar\s+)?month\b/i',

        // "paid" BEFORE the numeral: "the paid ones capped at 25 tickets a month". The pattern at
        // the top of this list is ordered number -> paid -> noun and cannot see this.
        '/\bpaid\s+(?:ones|rows|tiers|spots|places|seats)\b[^.]{0,60}\b(?:25|twenty-five)\b/i',

        // The retired allowance also survives as a NOUN: "your ticket allowance", "out of the same
        // monthly allowance". The newsletter allowance is real and still metered, so those uses are
        // exempted in ALLOWED_ALLOWANCE below rather than by narrowing the pattern - narrowing it
        // to an adjacent ticket noun is exactly how "the same monthly allowance" got through.
        '/\bticket\s+allowance\b/i',

        // Order-independent forms. Patterns 1, 2, 10 and 11 are all locked to
        // number -> ticket -> month; an adversarial pass defeated them with "ticket sales each
        // month ... twenty-five", "25-a-month ticket ceiling" and a table cell reading "Up to 25".
        '/\b(?:25|twenty-five)\b\s*-?\s*a\s*-?\s*month\b/i',
        '/\bticket\s+sales?\b[^.]{0,50}\b(?:25|twenty-five)\b/i',
        '/\b(?:25|twenty-five)\b[^.]{0,40}\bticket\s+sales?\b/i',
        '/\b(?:25|twenty-five)\b[^.]{0,40}\btickets?\b[^.]{0,30}\beach\s+month\b/i',

        // The claim without the verb "sell": charging, taking money, paid ticketing.
        '/\b(?:charging|charge)\s+for\s+(?:a\s+|the\s+)?tickets?\b(?:(?!Pro|Enterprise)[^.;]){0,60}\b(?:free|no cost|nothing|every plan|any plan)\b/i',
        '/\bstart\s+charging\b[^.]{0,60}\bfree(?:\s+forever)?\s+plan\b/i',
        '/\btake\s+money\s+for\s+tickets?\b[^.]{0,60}\b(?:free|without paying|no cost)\b/i',
        '/\bpaid\s+ticketing\b[^.]{0,50}\b(?:free|no cost|included at no|on the Free)\b/i',

        // "free tier" is the same claim as "free plan".
        '/\bfree\s+tier\b[^.;]{0,60}\b(?:sells?|selling)\s+(?:paid\s+|priced\s+)?tickets?\b/i',

        // Free registration is UNLIMITED; a cap on it is as wrong as a cap on selling.
        '/\bfree\s+registrations?\b[^.]{0,40}\b(?:capped|limited to|up to)\b[^.]{0,20}\d/i',

        // Door scanning is free on every tier. Named as an invariant in this class's docblock but
        // never actually guarded until now. "dashboard" exempts the genuinely-Pro sibling.
        '/\bscan(?:ning)?\s+(?:tickets?|the QR|at the door)\b(?:(?!dashboard)[^.]){0,70}?\b(?:is|are)\s+(?:only\s+)?(?:available\s+)?on\s+(?:the\s+)?(?:Pro|Enterprise)\b/i',

        '/\bPro\b[^.]{0,30}\bopens\b[^.]{0,40}\b(?:ports?|gateways?|Stripe|PayPal)\b/i',
    ];

    /**
     * Patterns that are only wrong in a TICKETING sentence.
     *
     * The newsletter allowance is real and still metered (10 recipients a month free, 100 on Pro,
     * 1,000 on Enterprise), as are the AI-parse, fan-photo and appointment-type limits. So "monthly
     * allowance" and "the cap comes off" are legitimate prose in those contexts and forbidden in a
     * ticketing one. Matched against the surrounding sentence, then dropped if that sentence is
     * about one of the allowances that still exist.
     */
    private const AMBIGUOUS = [
        // "monthly allowance", "the cap comes off" - true of newsletters, AI parsing, fan photos
        // and appointment types, all of which are still metered. A determiner is required on the
        // cap/ceiling form so "cap the room" (a capacity verb) is not a hit.
        '/\b(?:monthly|paid[- ]ticket)\s+allowance\b/i' => self::STILL_METERED,
        '/\ballowance\b[^.]{0,50}\b(?:zero-price tickets?|paid tickets?)\b/i' => self::STILL_METERED,
        '/\b(?:paid tickets?|zero-price tickets?)\b[^.]{0,50}\ballowance\b/i' => self::STILL_METERED,
        '/\b(?:the|that|its)\s+(?:monthly\s+)?(?:ceiling|cap)\b[^.]{0,40}\b(?:off|reached|lifted|removed)\b/i' => self::STILL_METERED,

        // The OPPOSITE direction, unguarded until now and the reason two false claims shipped
        // green: copy that puts a FREE capability behind Pro. Payment gateways and refunds are
        // ungated - `grep -rn "isPro()" app/Services/Payments/` is 0 and SaleRefundService has no
        // plan check. Stops at ; as well as . because "sell through Stripe; a priced ticket needs
        // Pro" is two clauses and only the second one is about Pro.
        '/(?:Stripe|PayPal|Payfast|Invoice Ninja|payment method|payment port|refunds?|refunding)\b[^.;]{0,70}?\b(?:is|are|requires?|needs?)\s+(?:only\s+)?(?:what\s+)?(?:on|need|needs?|an\s+upgrade\s+to)?\s*(?:the\s+|a\s+)?(?:Pro|Enterprise)\b/i' => self::GENUINELY_PRO,

        // "unlock PayPal on Pro", "Pro-only", "a Pro capability" - shapes the on/needs form misses.
        '/\bunlock\b[^.;]{0,60}?\b(?:Stripe|PayPal|Payfast|Invoice Ninja|refunds?|checkout)\b/i' => self::GENUINELY_PRO,
        '/(?:Stripe|PayPal|Payfast|Invoice Ninja|refunds?|refunding)\b[^.;]{0,70}?\b(?:Pro|Enterprise)-only\b/i' => self::GENUINELY_PRO,
        '/(?:Stripe|PayPal|Payfast|Invoice Ninja|refunds?|refunding)\b[^.;]{0,70}?\ba\s+(?:Pro|Enterprise)\s+(?:feature|capability|perk)\b/i' => self::GENUINELY_PRO,

        // "Billing a ticket through Invoice Ninja is the Pro part" - the same claim without the
        // on/needs verb, which is how it reads on a gateway's own landing page.
        '/(?:Stripe|PayPal|Payfast|Invoice Ninja)\b[^.;]{0,50}?\bis\s+the\s+(?:Pro|Enterprise)\b/i' => self::GENUINELY_PRO,
    ];

    /** Words that make an "allowance" or "cap" sentence about something still metered. */
    private const STILL_METERED = '/newsletter|recipient|subscriber|e-?mail|photo|appointment|AI |parse|import|SMTP|mail server/i';

    /**
     * Pages whose SUBJECT is a still-metered allowance, where a bare "monthly allowance" is the
     * correct words for the correct thing on nearly every line.
     *
     * Deliberately explicit rather than another turn of the context heuristic: two files where the
     * phrase is always right is easier to audit than a window rule that has to be re-tuned every
     * time someone writes a table. The ticket-specific patterns still apply to these files, so a
     * genuine "ticket allowance" claim here would still fail.
     */
    private const ALLOWANCE_PAGES = [
        'resources/views/marketing/newsletters.blade.php',
        'resources/views/marketing/docs/newsletters.blade.php',
    ];

    /** Things that ARE Pro or Enterprise and legitimately appear beside a gateway name. */
    private const GENUINELY_PRO = '/installment|dashboard|CSV|export|waitlist|gift card|add-on|promo|seating|API|webhook/i';

    /** @return array<string, string> path => contents */
    private function marketingSources(): array
    {
        $paths = array_merge(
            File::glob(resource_path('views/marketing/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*/*.blade.php')),
            // ALL twelve locales, not just en. The English-only scan is exactly why eleven
            // translated home_description / pricing_description claims survived three sweeps.
            File::glob(lang_path('*/marketing.php')),
            [
                app_path('Http/Controllers/MarketingController.php'),
                // pricing.blade.php puts __('messages.feature_boost') straight into $proFeatures,
                // so a tier claim can live here and never be seen by a views-only scan.
                lang_path('en/messages.php'),
                config_path('marketing_related.php'),
                // The RENDERED comparison and replacement data. The controller builds these
                // sentences by concatenating plan_price() into the middle of them, so a claim is
                // never a contiguous string in the source and a source-only scan cannot see it.
                base_path('tests/fixtures/comparison_data.json'),
                base_path('tests/fixtures/replacement_data.json'),
                // Hand-maintained, served to AI crawlers, and missed by every views/ glob.
                public_path('llms.txt'),
                public_path('llms-full.txt'),
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

    /**
     * The retired figure, in any language.
     *
     * The previous version of this test carried a vocabulary alternation that required the numeral
     * to sit immediately beside the price adjective - true in German, Dutch, Russian and Estonian,
     * and false in every Romance language ("25 billets payants", "25 entradas de pago") and in
     * Arabic and Hebrew. Exactly the four matching locales got fixed; the other seven shipped the
     * claim with a green build.
     *
     * So this does not try to know the languages. Across all twelve locale files the only numbers
     * that appear are 12 ("translation into 12 languages") and 25 (the retired allowance), which
     * makes "no bare 25 here" both exact and translation-proof.
     */
    public function test_no_locale_meta_quotes_the_retired_allowance(): void
    {
        $offences = [];

        foreach (File::glob(lang_path('*/marketing.php')) as $path) {
            if (preg_match_all('/\b25\b/', file_get_contents($path), $m)) {
                $offences[] = str_replace(base_path().'/', '', $path).': '.count($m[0]).' occurrence(s) of "25"';
            }
        }

        $this->assertSame([], $offences, implode("\n", array_merge(
            ['These locale metas still quote the retired 25-ticket allowance. Paid selling is '
                .'Pro/Enterprise; free registration is what is unlimited.'],
            $offences
        )));
    }

    public function test_no_marketing_surface_says_paid_selling_is_free(): void
    {
        $offences = [];

        foreach ($this->marketingSources() as $path => $body) {
            // Comments explaining the rule quote the wrong claim on purpose, so strip them first.
            // Block comments need /s (they span lines); the line-comment arm must NOT have it, or
            // `.*$` runs past every newline to the end of the file and the first `//` in a view
            // silently blanks the rest of it - which is what the first draft of the previous
            // version of this test did, and it is why it passed with a live claim in front of it.
            $body = preg_replace('~\{\{--.*?--\}\}|/\*.*?\*/~s', ' ', $body);
            $body = preg_replace('~^[ \t]*//.*$~m', ' ', $body);

            foreach (self::FORBIDDEN as $pattern) {
                if (preg_match_all($pattern, $body, $m)) {
                    foreach ($m[0] as $hit) {
                        $offences[] = $path.': "'.trim(preg_replace('/\s+/', ' ', $hit)).'"';
                    }
                }
            }

            foreach (self::AMBIGUOUS as $pattern => $allowed) {
                if ($allowed === self::STILL_METERED && in_array($path, self::ALLOWANCE_PAGES, true)) {
                    continue;
                }

                if (! preg_match_all($pattern, $body, $m, PREG_OFFSET_CAPTURE)) {
                    continue;
                }

                foreach ($m[0] as [$hit, $offset]) {
                    // Window direction matters and differs by pattern.
                    //
                    // STILL_METERED is SYMMETRIC: "monthly allowance" sits inside prose and tables
                    // where the word "newsletter" may be on either side of it.
                    //
                    // GENUINELY_PRO is BACKWARD only: an allow word qualifies the SUBJECT of the
                    // claim, and a subject comes first. Reading forward as well would let a
                    // trailing "which also adds the live check-in dashboard" excuse a sentence
                    // whose actual claim is that Stripe needs Pro.
                    // Scoped to the SENTENCE the hit sits in, not a raw character window: the FAQ
                    // answer that welds gateways to Pro has an unrelated "waitlist" one sentence
                    // earlier, and a fixed window silently excused it.
                    $backward = $allowed === self::GENUINELY_PRO;
                    $lead = substr($body, 0, $offset);

                    // GENUINELY_PRO reads back to the start of THIS sentence only: the FAQ answer
                    // that welds gateways to Pro has an unrelated "waitlist" one sentence earlier,
                    // and a wider window silently excused it.
                    //
                    // STILL_METERED reads back TWO sentences and forward one, because the thing
                    // being metered is often named in the sentence before the limit is described
                    // ("Twenty-five photos per schedule on the free plan. Pro takes the cap off").
                    $boundary = fn (string $text) => max((int) strrpos($text, '.'), (int) strrpos($text, ';'));
                    $from = $boundary($lead);

                    if (! $backward && $from > 0) {
                        $from = $boundary(substr($lead, 0, $from));
                    }

                    $from = max($from, $offset - 600);
                    $sentence = substr($body, $from, ($offset - $from) + strlen($hit));

                    if (! $backward) {
                        // Forward to the end of the NEXT sentence. ltrim first: a hit that ends a
                        // sentence is followed immediately by the boundary, so reading to the first
                        // '.' would return nothing and miss the qualifier sitting right after it
                        // ("...your remaining monthly allowance. A/B testing also needs enough
                        // recipients to mean anything.").
                        $rest = ltrim(substr($body, $offset + strlen($hit), 500), ".; \t\n\r");
                        $sentence .= substr($rest, 0, strcspn($rest, '.'));
                    }

                    if (preg_match($allowed, $sentence)) {
                        continue;
                    }

                    $offences[] = $path.': "'.trim(preg_replace('/\s+/', ' ', $hit)).'"';
                }
            }
        }

        $this->assertSame([], $offences, implode("\n", array_merge(
            ['These say paid ticket selling is free. Event::canSellPaidTickets() is Pro/Enterprise '
                .'only; free registration, RSVP and $0 ticket rows are what stay unlimited.'],
            $offences
        )));
    }
}
