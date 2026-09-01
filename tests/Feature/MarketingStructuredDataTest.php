<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The marketing layout and several marketing pages emit JSON-LD. A block that does not parse is
 * invisible to Google and silent in the browser, so nothing else in the suite would catch it.
 *
 * The one failure mode worth naming: the BreadcrumbList used to interpolate the page title with
 * {{ }}, which HTML-escapes but does NOT JSON-escape. A title carrying a double quote produced a
 * raw " inside a JSON string and invalidated the whole block, and the layout hands that title to
 * every subpage on the site.
 */
class MarketingStructuredDataTest extends TestCase
{
    // The homepage queries the events table for its poster wall.
    use RefreshDatabase;

    /** See test_a_double_quote_in_the_page_title_does_not_break_the_breadcrumb for the depth. */
    private const QUOTE_FIXTURE_PATH = '/seo/breadcrumb/quote-fixture';

    /** Every page whose structured data this test walks. */
    private const PAGES = [
        '/',
        '/about',
        '/features/allocated-seating',
        '/for-musicians',
    ];

    public function test_every_json_ld_block_on_the_marketing_pages_parses(): void
    {
        foreach (self::PAGES as $path) {
            $blocks = $this->jsonLdBlocks($this->get($path)->assertOk()->getContent());

            $this->assertNotEmpty($blocks, "{$path} emits no JSON-LD at all");

            foreach ($blocks as $i => $block) {
                $this->assertIsArray(
                    $block,
                    "JSON-LD block {$i} on {$path} did not decode"
                );
            }
        }
    }

    public function test_a_double_quote_in_the_page_title_does_not_break_the_breadcrumb(): void
    {
        config(['app.url' => 'https://eventschedule.test']);

        // A real page cannot be relied on to carry a quote in its title, so bind one that does.
        // strip_tags and html_entity_decode are exercised too: the slot arrives already rendered.
        //
        // Three segments deep on purpose: a route added at runtime is appended AFTER
        // `/{subdomain}` and `/{subdomain}/{slug}` in routes/web.php, so any shorter path is
        // swallowed by the guest portal's catch-all and 302s before this ever runs.
        Route::get(self::QUOTE_FIXTURE_PATH, fn () => Blade::render(<<<'BLADE'
            <x-marketing-layout>
                <x-slot name="title">Sound &amp; "Vision" - Event Schedule</x-slot>
                <x-slot name="description">A page whose title carries a double quote.</x-slot>
                <x-slot name="breadcrumbTitle">Sound &amp; <em>"Vision"</em></x-slot>
                <p>Body.</p>
            </x-marketing-layout>
            BLADE));

        $blocks = $this->jsonLdBlocks($this->get(self::QUOTE_FIXTURE_PATH)->assertOk()->getContent());

        $breadcrumb = $this->nodeOfType($blocks, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb, 'the layout emitted no BreadcrumbList');

        $last = end($breadcrumb['itemListElement']);
        $this->assertSame('Sound & "Vision"', $last['name']);

        // The deepest crumb is this page, so it has to agree with the canonical, which is built
        // from config('app.url') rather than from whatever host served the request.
        $this->assertSame('https://eventschedule.test'.self::QUOTE_FIXTURE_PATH, $last['item']);
        $this->assertSame(1, $breadcrumb['itemListElement'][0]['position']);
        $this->assertSame('Home', $breadcrumb['itemListElement'][0]['name']);
    }

    public function test_about_does_not_emit_a_second_unrelated_organization(): void
    {
        $blocks = $this->jsonLdBlocks($this->get('/about')->assertOk()->getContent());

        $ids = [];
        foreach ($blocks as $block) {
            if (($block['@type'] ?? null) === 'Organization') {
                $this->assertArrayHasKey('@id', $block, 'an Organization node on /about has no @id to merge on');
                $ids[] = $block['@id'];
            }
        }

        $this->assertNotEmpty($ids, '/about lost its Organization node');
        $this->assertSame([config('app.url').'/#organization'], array_values(array_unique($ids)));
    }

    public function test_the_site_navigation_element_block_is_gone(): void
    {
        // Google does not consume SiteNavigationElement; it was ~1 KB on every one of ~150 pages.
        foreach (self::PAGES as $path) {
            foreach ($this->jsonLdBlocks($this->get($path)->getContent()) as $block) {
                $this->assertNotSame('SiteNavigationElement', $block['@type'] ?? null, "still on {$path}");
            }
        }
    }

    public function test_the_faq_schema_matches_the_visible_faq(): void
    {
        // The four for-* pages used to hand-roll this block beside a hand-written FAQ, so the two
        // could drift. Both now come off one array; this proves the copy and the schema agree.
        foreach (['/features/allocated-seating', '/for-musicians', '/for-djs', '/for-comedians', '/for-magicians'] as $path) {
            $body = $this->get($path)->assertOk()->getContent();

            $faq = $this->nodeOfType($this->jsonLdBlocks($body), 'FAQPage');
            $this->assertNotNull($faq, "{$path} renders an FAQ with no FAQPage schema");
            $this->assertNotEmpty($faq['mainEntity']);

            foreach ($faq['mainEntity'] as $entry) {
                $this->assertStringContainsString(
                    e($entry['name']),
                    $body,
                    "a question in {$path}'s schema is not on the page: {$entry['name']}"
                );
                $this->assertStringContainsString(
                    e($entry['acceptedAnswer']['text']),
                    $body,
                    "an answer in {$path}'s schema is not on the page"
                );
            }
        }
    }

    public function test_the_public_layouts_offer_a_favicon_google_will_accept(): void
    {
        // Google only shows a favicon in search results when it is a multiple of 48 px square.
        $this->assertFileExists(public_path('images/favicon-96.png'));
        $this->assertSame([96, 96], array_slice(getimagesize(public_path('images/favicon-96.png')), 0, 2));

        // A zero-byte favicon.ico served a 200 and taught crawlers there was no icon.
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));

        foreach (['/', '/about', '/for-musicians'] as $path) {
            $body = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('images/favicon.png', $body);
            $this->assertStringContainsString('images/favicon-96.png', $body);
            $this->assertStringContainsString('sizes="96x96"', $body);
        }
    }

    /**
     * The tags have to describe the actual bytes: a declared size the file does not have gets the
     * image re-cropped by every scraper, and the wrong type gets it rejected outright.
     *
     * The 300 KB ceiling is WhatsApp's - over it, it renders no link preview at all - and it is
     * why these are JPEG rather than the 460 KB PNG captures they started as.
     */
    public function test_the_og_image_tags_describe_the_file_that_is_served(): void
    {
        foreach (['/', '/pricing'] as $path) {
            $body = $this->get($path)->assertOk()->getContent();

            preg_match('~<meta property="og:image" content="([^"]+)">~', $body, $m);
            $this->assertNotEmpty($m, $path.' declares no og:image');
            $this->assertStringEndsWith('.jpg', $m[1], $path.' should share a JPEG');

            $file = public_path('images/social/'.basename($m[1]));
            $this->assertFileExists($file);

            [$width, $height, $type] = getimagesize($file);
            $this->assertSame(IMAGETYPE_JPEG, $type, $file.' is not a JPEG');
            $this->assertSame([1200, 630], [$width, $height], $file.' is not 1200x630');
            $this->assertStringContainsString('<meta property="og:image:type" content="image/jpeg">', $body);
            $this->assertStringContainsString('<meta property="og:image:width" content="'.$width.'">', $body);
            $this->assertStringContainsString('<meta property="og:image:height" content="'.$height.'">', $body);

            $this->assertLessThan(
                300 * 1024,
                filesize($file),
                basename($file).' is over 300 KB, so WhatsApp will not render a preview for it'
            );
        }
    }

    /** Nothing shipped in the social directory may exceed the ceiling or contradict the tags. */
    public function test_every_social_image_is_a_shareable_size(): void
    {
        $offenders = [];

        foreach (glob(public_path('images/social/*.jpg')) ?: [] as $file) {
            [$width, $height] = getimagesize($file);

            if ($width !== 1200 || $height !== 630) {
                $offenders[] = basename($file)." is {$width}x{$height}";
            }

            if (filesize($file) > 300 * 1024) {
                $offenders[] = basename($file).' is '.round(filesize($file) / 1024).' KB';
            }
        }

        $this->assertSame([], $offenders, 'regenerate these with `php artisan app:generate-social-images`');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $matches);

        return array_map(function (string $raw) {
            return json_decode($raw, true);
        }, $matches[1]);
    }

    /**
     * @param  array<int, mixed>  $blocks
     * @return array<string, mixed>|null
     */
    private function nodeOfType(array $blocks, string $type): ?array
    {
        foreach ($blocks as $block) {
            if (is_array($block) && ($block['@type'] ?? null) === $type) {
                return $block;
            }
        }

        return null;
    }
}
