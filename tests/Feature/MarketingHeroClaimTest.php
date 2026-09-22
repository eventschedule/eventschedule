<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage fold is the only copy on the site most visitors ever read, and it is the copy most
 * likely to go stale: it sat unchanged for a year while appointments, reserved seating, gift cards
 * and passes all shipped into the sections below it.
 *
 * Two things have to survive an edit here.
 *
 * The first is that the fold says the product takes bookings at all. That is the whole reason it
 * was reworked, and it is four words of one sentence: "Put the date up on your event calendar.
 * People buy a ticket or book a time, and the money lands in your own Stripe or PayPal." A rewrite
 * that tightens the subhead can drop "or book a time" without anyone noticing, and a visitor who
 * does not scroll then never learns the product does bookings.
 *
 * The second is that the fold makes no tier claim. Appointment booking is free with one type and
 * selling tickets is free to 25 paid tickets a month, so a plan name appearing up here is either
 * wrong or is a paid feature being advertised beside a badge reading "Free event calendar. No credit card."
 * and a button reading "Start for free". An earlier version of this fold carried a chip row that
 * named Pro and Enterprise; it was removed deliberately, and this is what stops it drifting back in
 * unqualified.
 *
 * Scoped to the hero section, not the page, because "Pro" and "Enterprise" appear legitimately
 * further down (the pricing band, the feature grid, the FAQ) and a page-wide match would be noise.
 */
class MarketingHeroClaimTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The hero's rendered text: everything from <section id="top"> to the poster wall that closes it.
     *
     * Stops at the wall rather than the section end so the event names on the poster cards, which
     * are live database rows and could contain anything, cannot satisfy or trip an assertion.
     */
    private function heroText(): string
    {
        $body = $this->get('/')->assertOk()->getContent();

        $start = strpos($body, 'id="top"');
        $this->assertNotFalse($start, 'the hero section is gone from the homepage');

        $end = strpos($body, 'es-wall absolute', $start);
        $this->assertNotFalse($end, 'the hero poster wall has moved; this test needs a new end anchor');

        $hero = substr($body, $start, $end - $start);

        return trim(preg_replace('/\s+/', ' ', strip_tags($hero)));
    }

    /**
     * Appointment booking is free on every plan and is the capability that changed what the product
     * is. If it falls out of the fold, the fold is back to describing last year's product.
     */
    public function test_the_fold_says_the_product_takes_bookings(): void
    {
        $hero = $this->heroText();

        $this->assertMatchesRegularExpression('/\bbook(ing|ings|ed)?\b/i', $hero,
            "the fold no longer mentions booking anywhere. Hero text was:\n".$hero);
    }

    /**
     * The headline and subhead both have to survive, because between them they carry the argument:
     * the H1 is the promise and the subhead is the only place the product category and the three
     * audiences are named.
     */
    public function test_the_fold_still_names_the_product_category(): void
    {
        $hero = $this->heroText();

        // The <title> says "Free Event Calendar". An H1 and subhead that never confirm the title's
        // subject is the usual trigger for Google rewriting the title in the SERP.
        $this->assertStringContainsStringIgnoringCase('event calendar', $hero,
            'the fold no longer says what the product is, so the title has nothing on the page to confirm it');
    }

    /**
     * No plan name in the fold.
     *
     * Everything the fold claims is free, so a tier word up here is either a mistake or a paid
     * feature being sold inside the free promise. Selling tickets and taking bookings are both free
     * (paid selling is Pro, but the fold does not claim it is free), which is why this is a flat
     * ban rather than a per-feature qualifier check.
     */
    public function test_the_fold_advertises_no_paid_plan(): void
    {
        $hero = $this->heroText();

        foreach (['Pro', 'Enterprise'] as $tier) {
            $this->assertDoesNotMatchRegularExpression('/\b'.$tier.'\b/', $hero,
                "the fold names the {$tier} plan. Everything above the fold is free, and a plan name ".
                'beside "Free forever" and "Start for free" reads as a catch. Put tier-gated '.
                "features in the sections below.\n\nHero text was:\n".$hero);
        }
    }
}
