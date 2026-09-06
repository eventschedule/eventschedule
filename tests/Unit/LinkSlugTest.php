<?php

namespace Tests\Unit;

use App\Utils\UrlUtils;
use Tests\TestCase;

/**
 * The helpers behind a social link's short URL.
 *
 * linkSlug() is the single read helper: four guest templates and the admin portal used to each
 * inline the same detectPlatform() ternary, so a link's short URL could disagree with itself.
 */
class LinkSlugTest extends TestCase
{
    public function test_a_custom_slug_beats_the_detected_platform(): void
    {
        $this->assertSame('fb', UrlUtils::linkSlug([
            'url' => 'https://facebook.com/emeklive',
            'slug' => 'fb',
        ]));
    }

    public function test_a_known_platform_supplies_the_slug_when_none_is_set(): void
    {
        $this->assertSame('facebook', UrlUtils::linkSlug(['url' => 'https://facebook.com/emeklive']));
    }

    public function test_an_unrecognized_domain_has_no_slug_of_its_own(): void
    {
        $this->assertSame('', UrlUtils::linkSlug(['url' => 'https://promee.co.il/?r=33221']));
    }

    /** decodeLinks() yields stdClass, the controller yields arrays; both reach this helper. */
    public function test_it_accepts_both_object_and_array_links(): void
    {
        $link = (object) ['url' => 'https://facebook.com/x', 'slug' => 'fb'];

        $this->assertSame('fb', UrlUtils::linkSlug($link));
        $this->assertSame('fb', UrlUtils::linkSlug((array) $link));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function normalizations(): array
    {
        return [
            'trims and lowercases' => ['  Promee  ', 'promee'],
            'collapses punctuation' => ['my shop!', 'my-shop'],
            'strips edge hyphens' => ['-shop-', 'shop'],
            'hebrew romanizes' => ['פרומי', 'prwmy'],
            'nothing usable' => ['!!!', ''],
            'empty' => ['', ''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('normalizations')]
    public function test_normalize_link_slug(string $input, string $expected): void
    {
        $this->assertSame($expected, UrlUtils::normalizeLinkSlug($input));
    }

    public function test_normalize_link_slug_accepts_null(): void
    {
        $this->assertSame('', UrlUtils::normalizeLinkSlug(null));
    }

    /** The cap is the analytics_social_clicks_daily.platform column width. */
    public function test_a_slug_is_capped_to_the_analytics_column_width(): void
    {
        $slug = UrlUtils::normalizeLinkSlug(str_repeat('a', 60));

        $this->assertSame(UrlUtils::LINK_SLUG_MAX, strlen($slug));
    }

    public function test_a_truncated_slug_never_ends_on_a_hyphen(): void
    {
        $slug = UrlUtils::normalizeLinkSlug(str_repeat('ab-', 20));

        $this->assertStringEndsNotWith('-', $slug);
    }

    /**
     * The trap this ordering exists for. getBrand() reads the first dot-segment of the host, so
     * a WhatsApp group link is branded "Chat" while its live short URL is /whatsapp - suggesting
     * the brand would move a working short link the moment the owner accepted it.
     */
    public function test_a_suggestion_prefers_the_platform_over_the_brand(): void
    {
        $this->assertSame('whatsapp', UrlUtils::suggestLinkSlug('https://chat.whatsapp.com/Jq0qET8'));
    }

    public function test_a_suggestion_falls_back_to_the_brand_for_an_unknown_domain(): void
    {
        $this->assertSame('promee', UrlUtils::suggestLinkSlug('https://promee.co.il/?r=33221'));
        $this->assertSame('n99', UrlUtils::suggestLinkSlug('https://n99.co.il/articles/222'));
    }

    /** A suggestion that fails validation the instant it is accepted is worse than none. */
    public function test_a_suggestion_avoids_a_slug_already_taken(): void
    {
        $this->assertSame('promee-2', UrlUtils::suggestLinkSlug('https://promee.co.il/x', ['promee']));
    }

    public function test_reserved_path_slugs_cover_the_routes_that_would_shadow_a_short_link(): void
    {
        $reserved = UrlUtils::reservedPathSlugs();

        foreach (['edit', 'follow', 'book', 'request'] as $slug) {
            $this->assertContains($slug, $reserved);
        }

        foreach (['promee', 'n99'] as $slug) {
            $this->assertNotContains($slug, $reserved);
        }
    }

    public function test_the_map_gives_an_unrecognised_domain_its_brand_name(): void
    {
        $this->assertSame(
            [0 => 'facebook', 1 => 'promee'],
            UrlUtils::shortLinkSlugs([
                ['url' => 'https://facebook.com/emeklive'],
                ['url' => 'https://promee.co.il/?r=33221'],
            ])
        );
    }

    /** Pass 1 is the whole set, so a name is safe from pass 2 wherever it sits in the list. */
    public function test_an_owned_slug_later_in_the_list_still_beats_a_suggestion_earlier_in_it(): void
    {
        $this->assertSame(
            [0 => '', 1 => 'promee'],
            UrlUtils::shortLinkSlugs([
                ['url' => 'https://promee.co.il/?r=33221'],
                ['url' => 'https://facebook.com/emeklive', 'slug' => 'promee'],
            ])
        );
    }

    /**
     * No "-2" consolation prize. A suffixed address is not one an owner would print, and moving a
     * link's public URL because an unrelated link was added later is worse than offering none.
     */
    public function test_a_contested_name_is_dropped_rather_than_numbered(): void
    {
        $this->assertSame(
            [0 => 'promee', 1 => ''],
            UrlUtils::shortLinkSlugs([
                ['url' => 'https://promee.co.il/one'],
                ['url' => 'https://promee.com/two'],
            ])
        );
    }

    public function test_the_map_never_hands_out_a_name_the_schedule_has_taken(): void
    {
        $this->assertSame(
            [0 => ''],
            UrlUtils::shortLinkSlugs([['url' => 'https://promee.co.il/x']], ['promee'])
        );
    }

    /**
     * A typed slug is grandfathered - RoleUpdateRequest only validates a CHANGED one - so the map
     * reports it whatever the taken set says. Only pass 2 is de-conflicted.
     */
    public function test_a_typed_slug_is_reported_even_when_the_name_is_taken(): void
    {
        $this->assertSame(
            [0 => 'promee'],
            UrlUtils::shortLinkSlugs([['url' => 'https://promee.co.il/x', 'slug' => 'promee']], ['promee'])
        );
    }

    /** Takes stdClass (Role::decodeLinks) as well as arrays, the way linkSlug() does. */
    public function test_the_map_accepts_decoded_objects(): void
    {
        $this->assertSame(
            [0 => 'promee'],
            UrlUtils::shortLinkSlugs(json_decode('[{"url":"https://promee.co.il/x"}]'))
        );
    }
}
