<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * config/marketing_keywords.php is the keyword map: one primary search query per marketing page.
 * This holds the pages to it.
 *
 * Two failures it exists to catch. A hero headline that is pure ad copy ("Paste it once. It is
 * never wrong again.") leaves the query in the <title> alone, so the h1 - the strongest on-page
 * signal after the title - says nothing a searcher typed; the fix is the keyword eyebrow inside
 * the h1 (<x-marketing.hero-eyebrow>). And two pages drifting onto one query compete with each
 * other (the feature and docs twins with near-identical titles, three pages all called "Google
 * Calendar sync"), which the uniqueness check and the complete map prevent.
 *
 * The pages come from the rendered pages sitemap, the same list SitemapCoverageTest holds the
 * routes to, so a page that ships without a keyword fails here. The suite runs with
 * IS_NEXUS=true, which is what renders the marketing block of that sitemap.
 */
class MarketingKeywordMapTest extends TestCase
{
    use RefreshDatabase;

    /** Listed pages that deliberately have no keyword, with the reason. */
    private const NOT_MAPPED = [
        '/blog' => 'lives on its own host, and every post targets its own query',
        '/privacy' => 'a legal document',
        '/terms-of-service' => 'a legal document',
        '/self-hosting-terms-of-service' => 'a legal document',
        '/cookie-policy' => 'a legal document, and only listed when an operator has written one',
        '/accessibility' => 'a conformance statement, not a page competing for a query',
    ];

    /** The listed paths that must carry a keyword: everything but the docs and NOT_MAPPED. */
    private function mappablePaths(): array
    {
        $xml = $this->get('/sitemap-pages.xml')->assertOk()->streamedContent();
        $parsed = simplexml_load_string($xml);

        $this->assertNotFalse($parsed, 'the pages sitemap is not valid XML');

        return collect(iterator_to_array($parsed->url, false))
            ->map(fn ($node) => parse_url((string) $node->loc, PHP_URL_PATH) ?: '/')
            ->reject(fn ($path) => $path === '/docs' || str_starts_with($path, '/docs/'))
            ->reject(fn ($path) => array_key_exists($path, self::NOT_MAPPED))
            ->unique()
            ->values()
            ->all();
    }

    /** The phrases that satisfy a page's entry: its keyword, then any accepted variants. */
    private function phrases(array|string $entry): array
    {
        if (is_string($entry)) {
            return [$entry];
        }

        return array_merge([$entry['keyword']], $entry['match'] ?? []);
    }

    /** Visible text as a reader or a crawler gets it: tags out, entities decoded, one space. */
    private function normalise(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
    }

    private function containsAny(string $haystack, array $phrases): bool
    {
        foreach ($phrases as $phrase) {
            if (str_contains($haystack, $this->normalise($phrase))) {
                return true;
            }
        }

        return false;
    }

    public function test_every_listed_page_has_a_keyword(): void
    {
        $map = config('marketing_keywords');
        $missing = array_values(array_diff($this->mappablePaths(), array_keys($map)));

        $this->assertSame([], $missing, 'give each of these pages its primary keyword in '.
            'config/marketing_keywords.php, or add it to NOT_MAPPED here with the reason it has none');
    }

    public function test_no_entry_names_a_page_that_is_not_listed(): void
    {
        $stale = array_values(array_diff(array_keys(config('marketing_keywords')), $this->mappablePaths()));

        $this->assertSame([], $stale, 'these keyword map entries match no page in the sitemap; '.
            'remove them, or fix the path');
    }

    public function test_no_two_pages_share_a_keyword(): void
    {
        $byKeyword = [];

        foreach (config('marketing_keywords') as $path => $entry) {
            $byKeyword[$this->normalise($this->phrases($entry)[0])][] = $path;
        }

        $shared = [];

        foreach ($byKeyword as $keyword => $paths) {
            if (count($paths) > 1) {
                $shared[] = '"'.$keyword.'" on '.implode(', ', $paths);
            }
        }

        $this->assertSame([], $shared, 'two pages targeting one query compete for it; give one of '.
            'them a narrower keyword, or merge them');
    }

    public function test_every_page_title_and_h1_carry_its_keyword(): void
    {
        $failures = [];

        foreach (config('marketing_keywords') as $path => $entry) {
            $response = $this->get($path);

            if ($response->getStatusCode() !== 200) {
                $failures[] = "{$path} answered {$response->getStatusCode()}";

                continue;
            }

            $html = $response->getContent();
            $phrases = $this->phrases($entry);

            preg_match('~<title>(.*?)</title>~s', $html, $title);
            $title = $this->normalise($title[1] ?? '');

            if (! $this->containsAny($title, $phrases)) {
                $failures[] = "{$path}: <title> \"{$title}\" lacks \"{$phrases[0]}\"";
            }

            $h1Count = preg_match_all('~<h1\b[^>]*>(.*?)</h1>~s', $html, $h1s);

            if ($h1Count !== 1) {
                $failures[] = "{$path}: expected one <h1>, found {$h1Count}";

                continue;
            }

            $h1 = $this->normalise($h1s[1][0]);

            if (! $this->containsAny($h1, $phrases)) {
                $failures[] = "{$path}: <h1> \"{$h1}\" lacks \"{$phrases[0]}\"";
            }
        }

        $this->assertSame([], $failures, 'each page must say its keyword (or a listed variant) in its '.
            '<title> and its <h1>. The usual place in the h1 is the <x-marketing.hero-eyebrow> pill.');
    }
}
