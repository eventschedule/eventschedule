<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every marketing page has to ship a <title> and a <meta name="description"> that a search
 * engine will print whole.
 *
 * Google truncates a title at roughly 60 characters and a description at roughly 160, and it
 * rewrites the description itself when it thinks the one on the page is unhelpful - which a
 * 272-character wall of feature names reliably is. The bounds below are the hard ones: the
 * house target is 50-60 for a title and 120-155 for a description, and a page landing between
 * the target and the bound is a nudge rather than a failure, so only the bound is asserted.
 *
 * Two identical titles is a separate problem: it tells Google the two URLs are the same page,
 * and it picks one. That is why the duplicate check is here rather than in the length test.
 */
class MarketingMetaLengthTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_TITLE = 65;

    private const MIN_DESCRIPTION = 80;

    private const MAX_DESCRIPTION = 165;

    /**
     * marketing.* GET routes that are not pages with meta of their own, with the reason. The
     * same list SitemapCoverageTest keeps, for the same reasons.
     */
    private const NOT_A_PAGE = [
        'marketing.search' => 'a noindex results page; every query is a different URL',
        'marketing.docs.search_index' => 'the JSON payload the docs search fetches on first focus',
        'marketing.docs.schedule_basics' => '301 to /docs/creating-schedules',
        'marketing.docs.availability' => '301 to /docs/managing-schedules#availability',
        'marketing.docs.fan_content' => '301 to /docs/creating-events#fan-content',
        'marketing.docs.polls' => '301 to /docs/creating-events#polls',
        'marketing.cookie_policy' => 'an operator-authored document on layouts/legal.blade.php, '.
            'whose title and description are derived from the document rather than authored here '.
            '(and which 404s until an operator writes one)',
    ];

    /**
     * Pages allowed to sit outside the bounds, each with the reason it cannot be trimmed.
     *
     * Keep this empty. An entry is a debt, not a setting: test_the_allow_list_is_all_still_needed
     * fails the build once the page it names comes back inside the bounds.
     *
     * @var array<string, string>
     */
    private const ALLOWED = [];

    /** Every marketing GET route that is meant to be a page, as path => route name. */
    private function pageRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! str_starts_with($name, 'marketing.')) {
                continue;
            }

            if (! in_array('GET', $route->methods(), true) || str_contains($route->uri(), '{')) {
                continue;
            }

            if (array_key_exists($name, self::NOT_A_PAGE)) {
                continue;
            }

            // Both route blocks in routes/web.php register the same names; the first one wins.
            $routes['/'.ltrim($route->uri() === '/' ? '' : $route->uri(), '/')] ??= $name;
        }

        return $routes;
    }

    /**
     * path => ['title' => string, 'description' => string] for every page that answers 200.
     */
    private function renderedMeta(): array
    {
        $meta = [];

        foreach ($this->pageRoutes() as $path => $name) {
            $response = $this->get($path);

            $this->assertSame(200, $response->getStatusCode(),
                "{$path} ({$name}) did not answer 200; add it to NOT_A_PAGE if it is not a page");

            $html = $response->getContent();

            preg_match('~<title>(.*?)</title>~s', $html, $title);
            preg_match('~<meta name="description" content="(.*?)">~s', $html, $description);

            $meta[$path] = [
                'title' => html_entity_decode(trim($title[1] ?? ''), ENT_QUOTES | ENT_HTML5),
                'description' => html_entity_decode(trim($description[1] ?? ''), ENT_QUOTES | ENT_HTML5),
            ];
        }

        return $meta;
    }

    public function test_every_page_title_fits_a_search_result(): void
    {
        $tooLong = [];

        foreach ($this->renderedMeta() as $path => $meta) {
            if (array_key_exists($path, self::ALLOWED)) {
                continue;
            }

            $length = mb_strlen($meta['title']);

            $this->assertNotSame(0, $length, "{$path} renders no <title>");

            if ($length > self::MAX_TITLE) {
                $tooLong[] = "{$path} ({$length} chars): {$meta['title']}";
            }
        }

        $this->assertSame([], $tooLong,
            'trim these to at most '.self::MAX_TITLE.' characters (target 50-60): cut the trailing '.
            'clause, keep the leading keyword, and drop the brand suffix rather than the subject');
    }

    public function test_every_page_description_fits_a_search_result(): void
    {
        $outOfRange = [];

        foreach ($this->renderedMeta() as $path => $meta) {
            if (array_key_exists($path, self::ALLOWED)) {
                continue;
            }

            $length = mb_strlen($meta['description']);

            if ($length < self::MIN_DESCRIPTION || $length > self::MAX_DESCRIPTION) {
                $outOfRange[] = "{$path} ({$length} chars)";
            }
        }

        $this->assertSame([], $outOfRange,
            'these descriptions must be '.self::MIN_DESCRIPTION.'-'.self::MAX_DESCRIPTION.
            ' characters (target 120-155)');
    }

    /**
     * Two pages with one title tell Google they are one page, and it indexes whichever it
     * prefers. The Federation pair was exactly this: byte-identical bodies under one title,
     * which is why the SaaS copy now names itself and canonicals to the selfhost copy.
     */
    public function test_no_two_pages_share_a_title(): void
    {
        $byTitle = [];

        foreach ($this->renderedMeta() as $path => $meta) {
            $byTitle[$meta['title']][] = $path;
        }

        $duplicates = [];

        foreach ($byTitle as $title => $paths) {
            if (count($paths) > 1) {
                $duplicates[] = '"'.$title.'" on '.implode(', ', $paths);
            }
        }

        $this->assertSame([], $duplicates, 'give each of these pages a title of its own');
    }

    /**
     * The allow-list is a debt ledger. An entry that no longer needs to be there hides the next
     * regression on that page, so removing it is part of fixing the page.
     */
    public function test_the_allow_list_is_all_still_needed(): void
    {
        $meta = $this->renderedMeta();
        $stale = [];

        foreach (self::ALLOWED as $path => $reason) {
            $this->assertArrayHasKey($path, $meta, "ALLOWED names '{$path}', which is not a marketing page.");

            $titleLength = mb_strlen($meta[$path]['title']);
            $descriptionLength = mb_strlen($meta[$path]['description']);

            $withinBounds = $titleLength > 0
                && $titleLength <= self::MAX_TITLE
                && $descriptionLength >= self::MIN_DESCRIPTION
                && $descriptionLength <= self::MAX_DESCRIPTION;

            if ($withinBounds) {
                $stale[] = $path;
            }
        }

        $this->assertSame([], $stale, 'these pages are inside the bounds now; remove them from ALLOWED');
    }

    /**
     * A raw `"` in a title or description slot truncates the meta tag in every browser.
     *
     * `<x-slot name="description">` becomes an Illuminate\View\ComponentSlot, which implements
     * Htmlable - and e() short-circuits on Htmlable and returns the value UNESCAPED. So a slot
     * containing a quote closes the content attribute early and the rest of the sentence is parsed
     * as junk boolean attributes on the <meta> tag. /features/custom-labels shipped 150 characters
     * of description of which a browser could see 42.
     *
     * The length tests above cannot see it: their regex ends at the LAST `">` on the line, so they
     * measure the full string the server wrote rather than the fragment a parser keeps. This asserts
     * on the raw attribute instead, which is the only place the difference shows.
     */
    public function test_no_page_meta_breaks_out_of_its_attribute(): void
    {
        $broken = [];

        foreach ($this->pageRoutes() as $path => $name) {
            $html = $this->get($path)->getContent();

            foreach (['description', 'og:description', 'twitter:description'] as $tag) {
                $attr = $tag === 'og:description' ? 'property' : 'name';

                if (! preg_match('~<meta '.$attr.'="'.preg_quote($tag, '~').'" content="(.*?)">~s', $html, $m)) {
                    continue;
                }

                if (str_contains($m[1], '"')) {
                    $broken[] = "{$path} ({$tag})";
                }
            }
        }

        $this->assertSame([], $broken,
            'these meta values contain a raw double quote, which ends the content attribute early. '.
            'A slot is Htmlable, so Blade does NOT escape it - reword without quotes, or use the '.
            'typographic characters.');
    }
}
