<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The pages sitemap and the marketing routes have to agree in both directions.
 *
 * resources/views/sitemap.blade.php is hand-authored, which is what keeps "add the page here" the
 * only step needed when a page ships - and also what lets the two drift. Two ways they drift, both
 * of which have happened: a new marketing page never gets listed and is only ever found by a
 * crawler following an internal link, or a listed URL turns into a redirect and every crawl of the
 * sitemap collects a soft error (/blog 301'd to the blog host for months).
 *
 * The marketing block in that view is gated on is_nexus, and the suite runs with IS_NEXUS=true
 * (phpunit.xml), so the full list renders here. The sitemap routes are registered
 * withoutMiddleware('web'), so nothing about sessions or CSRF applies to fetching them.
 */
class SitemapCoverageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * marketing.* GET routes that are deliberately not pages, with the reason. Anything else that
     * is missing from the sitemap is a bug in the sitemap, not an entry for this list.
     */
    private const NOT_A_PAGE = [
        'marketing.search' => 'a noindex results page; every query is a different URL',
        'marketing.docs.search_index' => 'the JSON payload the docs search fetches on first focus',
        'marketing.docs.schedule_basics' => '301 to /docs/creating-schedules',
        'marketing.docs.availability' => '301 to /docs/managing-schedules#availability',
        'marketing.docs.fan_content' => '301 to /docs/creating-events#fan-content',
        'marketing.docs.polls' => '301 to /docs/creating-events#polls',
    ];

    /**
     * Nothing is shipped for /cookie-policy, so it is listed only where an operator has written
     * one. Writing one here is what puts that branch of the view under all three checks below.
     */
    protected function setUp(): void
    {
        parent::setUp();

        LegalDocument::create([
            'type' => LegalDocument::COOKIES,
            'content' => 'We use cookies to keep you signed in.',
        ]);
    }

    /** The paths listed in the rendered pages sitemap. */
    private function sitemapPaths(): array
    {
        $xml = $this->get('/sitemap-pages.xml')->assertOk()->streamedContent();
        $parsed = simplexml_load_string($xml);

        $this->assertNotFalse($parsed, 'the pages sitemap is not valid XML');

        return collect(iterator_to_array($parsed->url, false))
            ->map(fn ($node) => parse_url((string) $node->loc, PHP_URL_PATH) ?: '/')
            ->all();
    }

    /** Every marketing GET route that is meant to be a page, as path => route name. */
    private function pageRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! str_starts_with($name, 'marketing.')) {
                continue;
            }

            // Parameterised URIs are not static pages, and POST endpoints are not pages at all.
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

    public function test_every_marketing_page_is_listed_in_the_sitemap(): void
    {
        $listed = $this->sitemapPaths();
        $missing = [];

        foreach ($this->pageRoutes() as $path => $name) {
            if (! in_array($path, $listed, true)) {
                $missing[] = $path.' ('.$name.')';
            }
        }

        $this->assertSame([], $missing, 'add these to resources/views/sitemap.blade.php, or to '.
            'SitemapCoverageTest::NOT_A_PAGE with the reason they are not pages');
    }

    /**
     * A <loc> that redirects is a soft error: Google fetches it, follows the hop, and records the
     * submitted URL as "Page with redirect" rather than indexing anything.
     */
    public function test_no_listed_url_redirects_or_fails(): void
    {
        $bad = [];

        foreach ($this->sitemapPaths() as $path) {
            $status = $this->get($path)->getStatusCode();

            if ($status !== 200) {
                $bad[] = $path.' -> '.$status;
            }
        }

        $this->assertSame([], $bad, 'every URL in the sitemap must answer 200 at the URL listed');
    }

    /**
     * The manifest is keyed by the same path strings the view passes to $lastmodTag(), so a typo in
     * either one silently costs that page its date rather than failing anything.
     */
    /**
     * A submitted URL must not tell Google to index a different one.
     *
     * Sitemap inclusion is a canonical hint, so a listed page whose own rel=canonical points
     * elsewhere is a contradiction Search Console files as "Alternate page with proper canonical
     * tag" - the URL is submitted, crawled, and then permanently excluded. /docs/saas/federation
     * shipped in exactly that state: it gained a canonical pointing at its selfhost mirror while
     * staying in this sitemap, and neither the coverage checks above nor the length tests could
     * see it, because the page answers 200 and its meta is fine.
     *
     * test_no_listed_url_redirects_or_fails() is the sibling rule (a listed URL must not redirect);
     * this is the same idea for the softer signal.
     */
    public function test_no_listed_page_canonicals_somewhere_else(): void
    {
        $contradictions = [];

        foreach ($this->sitemapPaths() as $path) {
            // Only same-host paths; the blog lives on another host and is listed deliberately.
            if (! str_starts_with($path, '/')) {
                continue;
            }

            $html = $this->get($path)->getContent();

            if (! preg_match('~<link[^>]+rel="canonical"[^>]+href="([^"]+)"~i', $html, $m)) {
                continue;
            }

            $canonicalPath = parse_url($m[1], PHP_URL_PATH) ?: '/';

            if (rtrim($canonicalPath, '/') !== rtrim($path, '/')) {
                $contradictions[] = "{$path} canonicals to {$canonicalPath}";
            }
        }

        $this->assertSame([], $contradictions,
            'a page listed in the sitemap must be its own canonical. Either drop the canonical '.
            'override or stop listing the page - submitting both is how a URL ends up permanently '.
            'unindexed.');
    }

    /**
     * NOT_A_PAGE is a claim about indexing as much as about the sitemap: a route left out of the
     * sitemap because it is not a page must not ask to be indexed either, or Google finds it by a
     * link and indexes the thing the sitemap was keeping out. /search did exactly that - it said
     * noindex only once a query had been typed, so the empty results page was indexable.
     *
     * Only the routes that render HTML are checked: a redirect or a JSON payload has no robots
     * meta to carry. Both the bare URL and a queried one, because the old gate was on the query.
     */
    public function test_every_html_route_left_out_as_not_a_page_is_noindex(): void
    {
        $checked = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! array_key_exists($name, self::NOT_A_PAGE)
                || ! in_array('GET', $route->methods(), true) || str_contains($route->uri(), '{')) {
                continue;
            }

            $path = '/'.ltrim($route->uri(), '/');

            foreach ([$path, $path.'?q=x'] as $url) {
                $response = $this->get($url);

                if ($response->getStatusCode() !== 200
                    || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
                    continue;
                }

                $this->assertMatchesRegularExpression(
                    '~<meta name="robots" content="[^"]*\bnoindex\b~',
                    $response->getContent(),
                    "{$url} ({$name}) is left out of the sitemap as not a page, but does not say noindex"
                );

                $checked[] = $url;
            }
        }

        // Without this the loop can pass by rendering nothing at all.
        $this->assertContains('/search', $checked, 'fixture: the bare results page was not checked');
        $this->assertContains('/search?q=x', $checked, 'fixture: a queried results page was not checked');
    }

    /**
     * The platform 404 answers at the URL that was asked for, so every tag that names "this page's
     * URL" named the missing one: a canonical and og:url claiming a page that does not exist, and
     * a breadcrumb whose last crumb was the dead address.
     *
     * A fixture route, so the test cannot depend on what the guest portal's catch-alls do with an
     * unknown path. Three segments with a hyphenated last one: a route added at runtime is appended
     * after /{subdomain}/{slug}/{id} in routes/web.php, and {id} is constrained to the encodeId
     * charset, which has no hyphen.
     */
    public function test_the_404_page_names_no_url_of_its_own(): void
    {
        Route::get('/seo/error-page/missing-fixture', fn () => abort(404));

        $html = $this->get('/seo/error-page/missing-fixture')->assertNotFound()->getContent();

        $this->assertStringContainsString('<title>Page Not Found - Event Schedule</title>', $html,
            'fixture: the platform 404 page rendered');
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);

        $this->assertDoesNotMatchRegularExpression('~<link[^>]+rel="canonical"~i', $html, 'a 404 must not declare a canonical');
        $this->assertStringNotContainsString('property="og:url"', $html);
        $this->assertStringNotContainsString('name="twitter:url"', $html);
        $this->assertStringNotContainsString('BreadcrumbList', $html);

        // The Blog link used to be /blog, a 301 to the blog host on the hosted install.
        $this->assertStringContainsString('href="'.blog_url().'"', $html);
    }

    public function test_the_lastmod_manifest_keys_match_the_listed_paths(): void
    {
        $listed = $this->sitemapPaths();
        $manifest = array_keys(config('sitemap_lastmod') ?: []);

        $this->assertNotEmpty($manifest, 'config/sitemap_lastmod.php is empty - run `php artisan sitemap:lastmod`');

        // The manifest may cover pages the sitemap does not list (a noindex page still resolves to
        // a view), but a key that matches no page at all is a typo or a page that has been removed.
        $orphans = array_values(array_diff($manifest, $listed, ['/search']));

        $this->assertSame([], $orphans, 'these manifest keys match nothing in the sitemap');

        // Undated pages are allowed - Google prefers no lastmod to a wrong one - but the list of
        // them should be a deliberate short one, not a symptom of the manifest going stale.
        $undated = array_values(array_diff($listed, $manifest));

        // /blog is listed at its own host and dated by the blog child sitemap; /cookie-policy has
        // no bundled page to read a history off, so neither can be dated here.
        //
        // Compared as a set: the order is the order the sitemap view happens to list them in, and
        // moving a <url> block is not a regression. A page added in this commit is not on this
        // list either way, because sitemap:lastmod now dates an uncommitted view rather than
        // skipping it (GenerateSitemapLastmod::uncommittedDate()).
        $this->assertEqualsCanonicalizing(
            ['/cookie-policy', '/blog'],
            $undated,
            'unexpected pages have no date in the manifest'
        );
    }
}
