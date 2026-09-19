<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage fold is the only copy on the site most visitors ever read, and it is the copy most
 * likely to go stale: it sat unchanged for a year while appointments, reserved seating, gift cards
 * and passes all shipped into the sections below it.
 *
 * The capability chip row is where the fold names tier-gated features, and the reason it can name
 * them at all is that each one carries its own tier. Drop the tier word and the row becomes four
 * capabilities sitting directly under a badge reading "Free forever. No credit card." and a button
 * reading "Start for free", which is the most expensive place on the site to imply that an
 * Enterprise feature is free.
 *
 * SeatingClaimTest guards the pages a seated buyer lands on; this guards the page everybody lands
 * on. It asserts the chips, not the whole page, because "Enterprise" appears elsewhere in the
 * document (the pricing band, the FAQ) and a page-wide match would pass on a chip whose tier had
 * been deleted.
 */
class MarketingHeroClaimTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The chip row's rendered text, one entry per chip.
     *
     * Keyed off the `es-fade-up` list inside the hero rather than a test-only hook, so a rewrite
     * that drops the row fails here rather than silently asserting over an empty array.
     *
     * @return array<int, string>
     */
    private function heroChips(): array
    {
        $body = $this->get('/')->assertOk()->getContent();

        $hero = strstr($body, 'id="top"');
        $this->assertNotFalse($hero, 'the hero section is gone from the homepage');

        preg_match('/<ul class="es-fade-up[^"]*"[^>]*>(.*?)<\/ul>/s', $hero, $list);
        $this->assertNotEmpty($list, 'the hero capability chip row is gone from the homepage');

        preg_match_all('/<li\b[^>]*>(.*?)<\/li>/s', $list[1], $items);

        return array_map(
            fn ($chip) => trim(preg_replace('/\s+/', ' ', strip_tags($chip))),
            $items[1]
        );
    }

    /**
     * Appointment booking is free on every plan and is the capability that changed what the
     * product is, which is the whole reason the fold was reworked. If it falls out of the fold
     * again, a visitor who does not scroll never learns the product takes bookings at all.
     */
    public function test_the_fold_says_the_product_takes_bookings(): void
    {
        $chips = $this->heroChips();
        $this->assertNotEmpty($chips, 'the hero chip row rendered no chips');

        $booking = array_filter($chips, fn ($c) => stripos($c, 'booking') !== false);

        $this->assertNotEmpty($booking,
            "the fold no longer mentions booking. Chips were:\n- ".implode("\n- ", $chips));
    }

    /**
     * Each tier-gated capability names its tier, in its own chip.
     *
     * Reserved seating is Enterprise and passes and gift cards are Pro (docs/FEATURES.md). A chip
     * that names one without its tier is the claim this test exists to stop.
     */
    public function test_every_tier_gated_chip_names_its_tier(): void
    {
        $chips = $this->heroChips();

        $gated = [
            'seating' => 'Enterprise',
            'gift card' => 'Pro',
            'passes' => 'Pro',
        ];

        foreach ($gated as $needle => $tier) {
            foreach ($chips as $chip) {
                if (stripos($chip, $needle) === false) {
                    continue;
                }

                $this->assertStringContainsStringIgnoringCase($tier, $chip,
                    "the hero chip \"{$chip}\" names a {$tier}-only feature without saying so. ".
                    'Beside "Free forever" and "Start for free", that reads as included.');
            }
        }
    }

    /**
     * Nothing free-tier in the fold may pick up a tier word it does not have.
     *
     * The mirror of the test above: appointment booking is free with one type, and selling tickets
     * is free to 25 paid tickets a month, so a chip that puts either behind a plan is as wrong as
     * an unqualified Enterprise claim, and is the shape MarketingTicketingTierTest cannot see here
     * because a chip is not a sentence.
     */
    public function test_no_free_capability_is_labelled_as_paid(): void
    {
        foreach ($this->heroChips() as $chip) {
            if (stripos($chip, 'booking') === false && stripos($chip, 'ticket') === false) {
                continue;
            }

            // "Gift cards and passes on Pro" is correct and must keep working, so only chips that
            // are ABOUT booking or ticketing are checked.
            if (stripos($chip, 'gift card') !== false || stripos($chip, 'passes') !== false) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression('/\b(on|with)\s+(Pro|Enterprise)\b/i', $chip,
                "the hero chip \"{$chip}\" puts a free capability behind a paid plan");
        }
    }
}
