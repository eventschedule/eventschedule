<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Selling PAID tickets is Pro/Enterprise, and so is every surface that exists to move money for
 * one. This is the inverse of the guard that stood here from 2026-07-30 to 2026-09-20, when the
 * free plan sold 25 paid tickets a calendar month, and of the guard that stood here until
 * 2026-09-22, when payment gateways and refunds were advertised as free on every tier.
 *
 * The claim is not a token, it is a sentence, and every page writes it in its own voice: "the free
 * plan sells 25 paid tickets a month", "no gateway is plan-gated", "Refunds work on every plan",
 * "all four are open on every plan". A token grep finds about three quarters of them. So the
 * shapes are pinned here instead.
 *
 * The rule this encodes:
 *
 *   - Selling tickets with a PRICE is Pro/Enterprise.
 *   - The payment gateways (Stripe, PayPal, Payfast, Invoice Ninja, a payment link, cash) and
 *     refunds are presented as Pro, because Event::canSellPaidTickets() already makes charging
 *     for a ticket Pro-only: on Free a gateway has nothing to settle and a refund has nothing to
 *     reverse, so listing them as free features misleads.
 *   - Free registration, RSVP and $0 ticket rows are unlimited on every tier.
 *   - Scanning a ticket at the door is free; the live check-in DASHBOARD is Pro.
 *   - There is no platform fee on any tier, which is a FEE claim, not an availability claim, and
 *     it stays true. Every gateway pattern below is anchored gateway-before-plan so that
 *     "Zero platform fees on every plan, whether traders pay through Stripe..." keeps passing.
 *
 * This guards the COPY only. The code is deliberately ungated and stays that way:
 * `grep -rn "isPro()" app/Services/Payments/` is 0 and SaleRefundService has no plan check,
 * because a schedule that has been downgraded must still be able to refund money it already
 * took, and grandfathered events and the demo schedule still sell on Free.
 *
 * A page is free to say any of that in its own words. What it may not do is assert that paid
 * selling, a gateway or a refund is free or ungated, or quote the retired monthly allowance.
 */
class MarketingTicketingTierTest extends TestCase
{
    /** Sentences that assert paid ticket selling is free, or quote the retired allowance. */
    private const FORBIDDEN = [
        // The allowance figure itself, in every phrasing the sweep found: "25 paid tickets a
        // month", "25 paid ones", "25 paid spots/places/covers/drop-ins", "twenty-five paid ...".
        // Anchored on "paid" so a genuine 25 (25 fan photos, 25 endpoints) is not a false hit.
        '/\b(?:25|twenty-five)\b[^.]{0,40}\bpaid\b[^.]{0,40}\b(?:tickets?|ones|spots|places|covers|drop-ins|sales)\b/i',
        // "selling your first 25 drop-ins a month" carried neither an adjacent `paid` nor the
        // noun `ticket`, so every pattern above missed it and it shipped for months. Anchor on
        // the selling verb instead of the noun.
        '/\bsell(?:ing)?\b[^.]{0,40}\b(?:25|twenty-five)\b[^.]{0,40}\b(?:a|per|each)\s+month\b/i',

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

        // ---------------------------------------------------------------------------------
        // The inverse guard, added 2026-09-22 when gateways and refunds moved Free -> Pro in
        // the copy. The CODE is unchanged on purpose: there is no isPro() in
        // app/Services/Payments/ and none in SaleRefundService, because a downgraded schedule
        // must still refund money it already took, and grandfathered events and the demo
        // schedule still sell on Free. The marketing claim moves because
        // Event::canSellPaidTickets() already makes a PRICED ticket Pro-only, so on Free a
        // gateway has nothing to settle and a refund has nothing to reverse.
        //
        // Every pattern below is anchored on a PAYMENT noun or on "refund" appearing BEFORE
        // the plan phrase. That ordering is what keeps the still-true claims legal:
        // "Zero platform fees on every plan, whether traders pay through Stripe, PayPal, ..."
        // names the plan first, so no gateway-anchored pattern can reach it.
        // ---------------------------------------------------------------------------------

        // "No payment method is plan-gated", "no gateway is plan-gated". The negation is IN
        // the pattern, so the new true claim ("putting a price on a ticket is plan-gated")
        // still passes. Both orders, because the negation can lead or trail. Deliberately not
        // a bare /plan-gated/: "nothing on this page is plan-gated on your own server"
        // (selfhost) and "Install-wide, and never plan-gated" (SMTP docs) are both true and
        // carry no payment noun.
        '/\b(?:no|none|not|never|neither|nothing)\b[^.;]{0,60}?\b(?:Stripe|PayPal|Payfast|Invoice Ninja|payment method|payment port|payment gateway|gateway|refund)\w*\b[^.;]{0,60}?\bplan[ -]gated\b/i',
        '/\b(?:Stripe|PayPal|Payfast|Invoice Ninja|payment method|payment port|payment gateway|gateway|refund)\w*\b[^.;]{0,60}?\b(?:not|never|no)\b[^.;]{0,25}?\bplan[ -]gated\b/i',

        // The same claim stated as an absence of code rather than an absence of a tier:
        // "There is no plan check anywhere in the payments code". True of the source and
        // false as a promise, which is exactly the sentence this move is about.
        '/\bno\s+plan\s+check\b[^.;]{0,60}\b(?:payment|gateway|checkout|refund)/i',
        '/\b(?:payment|gateway|checkout|refund)\w*\b[^.;]{0,60}?\bno\s+plan\s+check\b/i',

        // The remaining shapes - a payment noun sitting near an "on every plan" phrase - are
        // genuinely ambiguous, because the platform-fee claim is written in exactly the same
        // breath and stays true. They live in AMBIGUOUS below, resolved by STILL_FREE.
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

        // A payment noun sitting near an "on every plan" phrase, in either order.
        //
        // These cannot be unconditional, because the platform-fee claim is written the same way
        // and stays true on every tier: "Zero platform fees on every plan, whether traders pay
        // through Stripe, PayPal, a payment link or cash", "Refunds from the Sales page, and no
        // platform fee on any plan". So is "free on every plan, paid bookings on Pro by Stripe"
        // (appointments) and "check people in at the door on any plan, and take the money
        // through your own Stripe account". STILL_FREE settles which is which.
        //
        // The gap excludes quotes as well as . and ; so a match cannot bleed across two
        // neighbouring items of a PHP or JSON array - `'PayPal', 'Two-way sync ... free on
        // every plan'` is two unrelated bullets, not a claim. The ONE pattern that must cross a
        // quote is the FAQ refund shape, `['q' => 'Can I refund a ticket?', 'a' => 'Yes, on
        // every plan`, where the noun is in the question and the claim is in the answer; it
        // gets its own arm with a wider gap.
        '/\brefund(?:s|ed|ing)?\b[^.;"\']{0,60}?\bon\s+(?:every|any|all)\s+(?:plan|tier)\b/i' => self::STILL_FREE,
        '/\bon\s+(?:every|any|all)\s+(?:plan|tier)\b[^.;"\']{0,60}?\brefund(?:s|ed|ing)?\b/i' => self::STILL_FREE,
        '/(?:\brefund(?:s|ed|ing)?\b|\bStripe\b|\bPayPal\b|\bPayfast\b|\bInvoice Ninja\b|\bpayment method\b)[^.;]{0,40}?\?["\'],\s*["\']?(?:a|answer)["\']?\s*(?:=>|:)\s*["\'][^.;"\']{0,60}?\bon\s+(?:every|any|all)\s+(?:plan|tier)\b/i' => self::STILL_FREE,

        // "all four are open on every plan", "Invoice Ninja is free on any plan", "Stripe or
        // PayPal account is free on any plan". An availability word between the gateway and the
        // plan phrase is what separates this from the fee claim: "You keep the ticket price
        // minus what Stripe or PayPal charges to process the payment, on every plan" has none.
        '/\b(?:Stripe|PayPal|Payfast|Invoice Ninja|payment (?:method|port|gateway|option|link)s?|gateways?)\b[^.;"\']{0,80}?\b(?:is|are|work|works|open|available|included|supported|live|enabled|stays?|free|no (?:extra )?cost|nothing extra)\b[^.;"\']{0,40}?\bon\s+(?:every|any|all)\s+(?:plan|tier)\b/i' => self::STILL_FREE,

        // The plan phrase first.
        '/\bon\s+(?:every|any|all)\s+(?:plan|tier)\b[^.;"\']{0,60}?\b(?:Stripe|PayPal|Payfast|Invoice Ninja|payment (?:method|port|gateway|option)s?|gateways?)\b/i' => self::STILL_FREE,
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

    /**
     * Words that make an "on every plan" sentence about something that really IS on every plan.
     *
     * The platform fee is the big one: it is a fee claim, not an availability claim, and it is
     * still true on every tier. The rest are the capabilities the free plan genuinely keeps -
     * free registration, RSVP, $0 rows, door scanning - which are frequently named in the same
     * breath as a gateway ("money always goes to your own Stripe account").
     */
    private const STILL_FREE = '/platform fee|no fee|zero fee|0%|takes? nothing|no cut|never take a cut|commission|keep 100|registration|RSVP|scan|check.?in|QR|at the door|\$0|zero-price|free sign-up|sub-schedule|notify me|appointment|booking|calendar|sync/i';

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
                    // Window width differs by pattern.
                    //
                    // STILL_METERED reads back TWO sentences and forward into the NEXT one,
                    // because the thing being metered is often named in the sentence before the
                    // limit is described ("Twenty-five photos per schedule on the free plan. Pro
                    // takes the cap off").
                    //
                    // STILL_FREE reads back to the start of THIS sentence and forward only to the
                    // end of THIS sentence. Reading into the next one would let "Refunds work on
                    // every plan. Scanning is free too." excuse itself on the word "scan".
                    $meteredWindow = $allowed === self::STILL_METERED;
                    $lead = substr($body, 0, $offset);

                    $boundary = fn (string $text) => max((int) strrpos($text, '.'), (int) strrpos($text, ';'));
                    $from = $boundary($lead);

                    if ($meteredWindow && $from > 0) {
                        $from = $boundary(substr($lead, 0, $from));
                    }

                    $from = max($from, $offset - 600);
                    $sentence = substr($body, $from, ($offset - $from) + strlen($hit));

                    $rest = substr($body, $offset + strlen($hit), 500);

                    if ($meteredWindow) {
                        // ltrim first: a hit that ends a sentence is followed immediately by the
                        // boundary, so reading to the first '.' would return nothing and miss the
                        // qualifier sitting right after it ("...your remaining monthly allowance.
                        // A/B testing also needs enough recipients to mean anything.").
                        $rest = ltrim($rest, ".; \t\n\r");
                        $sentence .= substr($rest, 0, strcspn($rest, '.'));
                    } else {
                        $sentence .= substr($rest, 0, strcspn($rest, '.;'));
                    }

                    if (preg_match($allowed, $sentence)) {
                        continue;
                    }

                    $offences[] = $path.': "'.trim(preg_replace('/\s+/', ' ', $hit)).'"';
                }
            }
        }

        $this->assertSame([], $offences, implode("\n", array_merge(
            ['These say paid ticket selling, a payment gateway or a refund is free or ungated. '
                .'Event::canSellPaidTickets() is Pro/Enterprise only; free registration, RSVP, $0 '
                .'ticket rows, door scanning and the zero platform fee are what stay on every plan.'],
            $offences
        )));
    }
}
