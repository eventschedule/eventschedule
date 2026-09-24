<?php

namespace Tests\Support;

/**
 * Finds the Free plan's part of a markdown file, for the tests that keep the files we write for
 * AI crawlers (public/llms.txt, public/llms-full.txt) true to docs/FEATURES.md.
 *
 * A plan list is a run of noun phrases - "Every payment method", "Full and partial refunds" - with
 * no sentence around them, so the claim that one of them is free lives in the HEADING above it.
 * The sentence patterns in MarketingTicketingTierTest cannot see that, which is how both of those
 * bullets sat in llms-full.txt's Free list after the copy everywhere else had moved them to Pro.
 *
 * Lives in tests/Support so PHPUnit never collects it as a test class.
 */
final class PlanSections
{
    /** A way to take money, or a refund of it: Pro and above (docs/FEATURES.md). */
    public const PAYMENT = '/\b(?:Stripe|PayPal|Payfast|Invoice Ninja|payment (?:methods?|gateways?|links?)|gateways?|cash|refunds?|refunded)\b/i';

    /**
     * Every part of $markdown that describes the Free plan: the body under a heading naming it
     * ("### Free Plan"), down to the next heading of the same or a higher level, and any list item
     * whose bold label names it ("- **Free plan**: ...").
     *
     * @return array<int, string>
     */
    public static function free(string $markdown): array
    {
        $sections = [];
        $current = null;
        $level = 0;

        foreach (preg_split('/\R/', $markdown) as $line) {
            if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $heading)) {
                if ($current !== null && strlen($heading[1]) <= $level) {
                    $sections[] = implode("\n", $current);
                    $current = null;
                }

                if ($current === null && preg_match('/^Free(?:\s+(?:plan|tier))?\s*$/i', trim($heading[2]))) {
                    $current = [];
                    $level = strlen($heading[1]);

                    continue;
                }
            }

            if ($current !== null) {
                $current[] = $line;
            }

            if (preg_match('/^\s*[-*]\s+\*\*Free(?:\s+(?:plan|tier))?\*\*/i', $line)) {
                $sections[] = $line;
            }
        }

        if ($current !== null) {
            $sections[] = implode("\n", $current);
        }

        return $sections;
    }
}
