<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Blog\BlogFacts;
use App\Services\Blog\BlogGuide;
use App\Services\Blog\BlogLinks;
use App\Utils\PlatformPricing;
use Tests\TestCase;

/**
 * What the blog's writer is handed about the product (config/blog_facts.php) and where it may
 * link. These are read by a model and then by the public, so a fact that points at a page that
 * is gone, or a guide that was renamed, fails here and not in a published post.
 */
class BlogFactsTest extends TestCase
{
    public function test_every_fact_has_a_plan_and_a_sentence(): void
    {
        $facts = BlogFacts::all();

        $this->assertGreaterThan(40, count($facts));

        foreach ($facts as $id => $fact) {
            $this->assertMatchesRegularExpression('~^[a-z][a-z-]*$~', $id, 'a fact id is cited by the model, so it stays plain');
            $this->assertContains($fact['tier'] ?? null, ['about', 'free', 'pro', 'enterprise', 'not'], $id);
            $this->assertNotSame('', trim((string) ($fact['says'] ?? '')), $id);
            $this->assertDoesNotMatchRegularExpression('~[$€£]\s?\d~', $fact['says'], $id.' types a price: prices come from PlatformPricing');
        }

        // The things live posts claimed and the product does not do are named as such.
        foreach (['no-volunteers', 'no-gating', 'no-auto-price'] as $id) {
            $this->assertSame('not', $facts[$id]['tier'] ?? null, $id);
        }
    }

    public function test_a_facts_page_is_a_marketing_page_and_its_guide_is_a_guide(): void
    {
        $pages = array_keys((array) config('marketing_keywords'));

        foreach (BlogFacts::all() as $id => $fact) {
            if (isset($fact['url'])) {
                $this->assertContains($fact['url'], $pages, $id.' points at a page with no entry in config/marketing_keywords.php');
            }

            if (isset($fact['guide'])) {
                $this->assertTrue(BlogGuide::exists($fact['guide']), $id.' names a guide page that is not in resources/views/marketing/docs');
                $this->assertNotEmpty($fact['match'] ?? [], $id.' has a guide page and no words to find its sections by');
                $this->assertNotEmpty(BlogGuide::sections($fact['guide']), $fact['guide'].' has no h2 sections to quote');
            }
        }
    }

    public function test_the_prompt_carries_this_installs_own_prices_under_each_plans_heading(): void
    {
        $prompt = BlogFacts::forPrompt();

        $this->assertStringContainsString('Pro ('.plan_price(PlatformPricing::proMonthly()).' a month or '.plan_price(PlatformPricing::proYearly()).' a year)', $prompt);
        $this->assertStringContainsString('Enterprise ('.plan_price(PlatformPricing::enterpriseMonthly()).' a month', $prompt);
        $this->assertDoesNotMatchRegularExpression('~:(?:free|pro_|enterprise_)~', $prompt, 'a price placeholder reached the prompt');

        // The plan a feature needs is the heading it stands under, which the prompts refer to.
        $pro = strpos($prompt, 'On the Pro plan and above (not on Free)');
        $enterprise = strpos($prompt, 'On the Enterprise plan only');
        $this->assertTrue($pro < strpos($prompt, '[paid-tickets]') && strpos($prompt, '[paid-tickets]') < $enterprise, 'paid tickets are Pro (docs/FEATURES.md)');
        $this->assertTrue(strpos($prompt, '[rsvp]') < $pro, 'free registration is on every plan');
        $this->assertTrue($enterprise < strpos($prompt, '[seating]'), 'reserved seating is Enterprise');
    }

    public function test_every_feature_label_on_an_audience_card_means_a_fact(): void
    {
        $facts = BlogFacts::ids();

        foreach ((array) config('sub_audiences') as $audience) {
            foreach ($audience['sub_audiences'] as $sub) {
                foreach ($sub['features'] ?? [] as $label) {
                    $this->assertArrayHasKey($label, BlogFacts::LABELS, 'the card label "'.$label.'" ('.$sub['slug'].') has no fact behind it');
                    $this->assertContains(BlogFacts::LABELS[$label], $facts, $label);
                }
            }
        }

        $this->assertSame(['paid-tickets', 'newsletter'], BlogFacts::forLabels(['Ticket sales', 'Fan newsletters', 'Multiple ticket types']));
    }

    public function test_the_guides_own_words_are_what_a_post_may_quote(): void
    {
        $excerpts = BlogGuide::excerpts(['passes']);

        $this->assertStringContainsString('from the guide page '.BlogLinks::base().'/docs/subscriptions', $excerpts);
        $this->assertStringContainsString('Tickets tab', $excerpts);
        $this->assertStringNotContainsString('{{', $excerpts, 'Blade reached the prompt');
        $this->assertStringNotContainsString('<', $excerpts, 'markup reached the prompt');
        $this->assertLessThanOrEqual(BlogGuide::MAX_WORDS + 50, str_word_count($excerpts));

        $this->assertSame('(no guide text applies to this topic)', BlogGuide::excerpts(['fees', 'not-a-fact']));
    }

    public function test_a_post_may_link_product_pages_and_guides_at_the_apex(): void
    {
        $targets = BlogLinks::targets();
        $base = BlogLinks::base();

        foreach (['/features/ticketing', '/pricing', '/docs/tickets', '/eventbrite-alternative', '/for-museums'] as $path) {
            $this->assertArrayHasKey($base.$path, $targets, $path);
        }
        $this->assertArrayHasKey($base, $targets, 'the home page');

        foreach (array_keys($targets) as $url) {
            $this->assertMatchesRegularExpression('~^https?://(?!www\.)[^/\s]+(/[a-z0-9/-]+)?$~', $url, 'www. is a redirect hop, and an address is plain');
            $this->assertFalse(str_ends_with($url, '-replacement'), $url);
        }

        $this->assertTrue(BlogLinks::isHome($base.'/'));
        $this->assertSame(BlogLinks::normalise('https://WWW.Example.com/Pricing/'), 'https://example.com/Pricing');
    }

    public function test_the_sections_a_post_can_be_filed_under(): void
    {
        $this->assertSame('by-event', array_key_last(BlogPost::CATEGORIES));
        $this->assertSame('selling-tickets', BlogPost::guessCategory('sell-tickets-and-accept-payments', 'Sell Tickets and Accept Payments', []));
        $this->assertSame('on-the-day', BlogPost::guessCategory('x', '5 Ways to Recruit & Retain Event Volunteers', []));
        $this->assertSame('by-event', BlogPost::guessCategory('for-solo-artists', 'Solo Artists', []));
        $this->assertSame('planning', BlogPost::guessCategory('x', 'A Title About Nothing Listed', []));
    }
}
