<?php

namespace Tests\Feature;

use App\Utils\DocsUtils;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Guards config/docs.php against drifting from the routes, the blade files and
 * the AP Help button's anchor map. The manifest is the single source of truth
 * for the /docs page list, so a page renamed or moved anywhere else must fail
 * loudly here rather than silently producing a dead nav entry.
 */
class DocsManifestTest extends TestCase
{
    public function test_manifest_is_not_empty(): void
    {
        $this->assertNotEmpty(DocsUtils::pages(), 'config/docs.php has no pages.');
        $this->assertNotEmpty(DocsUtils::groups(), 'config/docs.php has no groups.');
    }

    /**
     * anchorsFor() strips <x-doc-screenshot> because there `id` names an image file rather than
     * an anchor - which left nothing asserting the file is actually present. A referenced image
     * that does not exist renders as a broken-image icon on a public docs page, in both themes.
     */
    public function test_every_referenced_doc_screenshot_exists(): void
    {
        $missing = [];

        // array_merge, not +: both globs are numerically indexed, so the union operator would
        // silently drop the leading entries of the second list.
        $files = array_merge(
            glob(resource_path('views/marketing/docs/*.blade.php')),
            glob(resource_path('views/marketing/docs/**/*.blade.php'))
        );

        foreach ($files as $file) {
            preg_match_all('#<x-doc-screenshot\b[^>]*\bid="([^"]+)"#', file_get_contents($file), $matches);

            foreach ($matches[1] as $id) {
                foreach (["{$id}.png", "{$id}-dark.png"] as $variant) {
                    if (! is_file(public_path('images/docs/'.$variant))) {
                        $missing[] = $variant.' (referenced by '.basename($file).')';
                    }
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)),
            'Generate them with: php artisan app:generate-doc-screenshots --page=<page>');
    }

    public function test_every_page_has_the_required_keys(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            foreach (['group', 'route', 'path', 'title', 'blurb', 'icon'] as $required) {
                $this->assertArrayHasKey($required, $page, "Docs page '{$key}' is missing '{$required}'.");
                $this->assertNotEmpty($page[$required], "Docs page '{$key}' has an empty '{$required}'.");
            }
        }
    }

    public function test_every_page_route_is_registered(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $this->assertNotNull(
                Route::getRoutes()->getByName($page['route']),
                "Docs page '{$key}' references unregistered route '{$page['route']}'."
            );
        }
    }

    public function test_every_page_route_resolves_to_its_declared_path(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $uri = '/'.ltrim(Route::getRoutes()->getByName($page['route'])->uri(), '/');

            $this->assertSame(
                $page['path'],
                $uri,
                "Docs page '{$key}' declares path '{$page['path']}' but route '{$page['route']}' resolves to '{$uri}'."
            );
        }
    }

    public function test_every_page_has_a_blade_file(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $file = resource_path('views/marketing/docs/'.$key.'.blade.php');

            $this->assertFileExists($file, "Docs page '{$key}' has no blade file at {$file}.");
        }
    }

    public function test_every_blade_file_is_in_the_manifest(): void
    {
        $files = glob(resource_path('views/marketing/docs/').'{*,*/*}.blade.php', GLOB_BRACE);

        foreach ($files as $file) {
            $key = str_replace([resource_path('views/marketing/docs/'), '.blade.php'], '', $file);

            // The docs landing page is not a manifest page, and partials are includes.
            if ($key === 'index' || str_starts_with($key, 'partials/')) {
                continue;
            }

            $this->assertNotNull(
                DocsUtils::page($key),
                "Docs blade '{$key}.blade.php' is not registered in config/docs.php."
            );
        }
    }

    public function test_every_page_belongs_to_a_declared_group(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $this->assertNotNull(
                DocsUtils::group($page['group']),
                "Docs page '{$key}' references undeclared group '{$page['group']}'."
            );
        }
    }

    public function test_every_group_index_route_is_registered(): void
    {
        foreach (DocsUtils::groups() as $key => $group) {
            if (empty($group['index_route'])) {
                continue;
            }

            $this->assertNotNull(
                Route::getRoutes()->getByName($group['index_route']),
                "Docs group '{$key}' references unregistered index route '{$group['index_route']}'."
            );
        }
    }

    public function test_every_user_guide_page_has_a_declared_cluster(): void
    {
        $clusters = config('docs.clusters', []);

        foreach (DocsUtils::pagesInGroup('user-guide') as $page) {
            $this->assertArrayHasKey(
                'cluster',
                $page,
                "User Guide page '{$page['key']}' has no cluster, so it would not appear on the /docs index."
            );

            $this->assertArrayHasKey(
                $page['cluster'],
                $clusters,
                "User Guide page '{$page['key']}' references undeclared cluster '{$page['cluster']}'."
            );
        }
    }

    public function test_every_cluster_has_pages(): void
    {
        foreach (array_keys(config('docs.clusters', [])) as $cluster) {
            $this->assertNotEmpty(
                DocsUtils::pagesInCluster($cluster),
                "Cluster '{$cluster}' has no pages, so it would render as an empty row."
            );
        }
    }

    /**
     * Regression guard for the getDocNavigation() null-index bug: an unknown
     * key used to report $pages[1] as its Next because `null < count - 1` is
     * true in PHP.
     */
    public function test_prev_next_returns_nulls_for_an_unknown_page(): void
    {
        $this->assertSame(['prev' => null, 'next' => null], DocsUtils::prevNext('no-such-page'));
        $this->assertSame(['prev' => null, 'next' => null], DocsUtils::prevNext(null));
    }

    public function test_prev_next_walks_within_a_group(): void
    {
        $guide = DocsUtils::pagesInGroup('user-guide');
        $first = $guide[0];
        $last = $guide[count($guide) - 1];

        $this->assertNull(DocsUtils::prevNext($first['key'])['prev'], 'First page in a group must have no Previous.');
        $this->assertNull(DocsUtils::prevNext($last['key'])['next'], 'Last page in a group must have no Next.');

        $second = $guide[1];
        $this->assertSame($first['key'], DocsUtils::prevNext($second['key'])['prev']['key']);
        $this->assertSame($second['key'], DocsUtils::prevNext($first['key'])['next']['key']);
    }

    public function test_non_user_guide_groups_also_get_prev_next(): void
    {
        // Before the manifest these groups had no prev/next at all.
        foreach (['selfhost', 'saas', 'developer'] as $group) {
            $pages = DocsUtils::pagesInGroup($group);
            $this->assertGreaterThan(1, count($pages), "Group '{$group}' should have more than one page.");

            $this->assertNotNull(
                DocsUtils::prevNext($pages[1]['key'])['prev'],
                "Second page of '{$group}' should have a Previous."
            );
        }
    }

    public function test_a_group_landing_page_does_not_link_to_itself_in_the_breadcrumb(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $crumb = DocsUtils::breadcrumb($key);

            if ($crumb['sectionRoute'] !== null) {
                $this->assertNotSame(
                    $page['route'],
                    $crumb['sectionRoute'],
                    "Docs page '{$key}' breadcrumb links to itself."
                );
            }
        }
    }

    public function test_leaf_pages_in_a_sectioned_group_get_a_middle_crumb(): void
    {
        $crumb = DocsUtils::breadcrumb('selfhost/stripe');

        $this->assertSame('Stripe Integration', $crumb['currentTitle']);
        $this->assertSame('Selfhost', $crumb['sectionTitle']);
        $this->assertSame('marketing.docs.selfhost', $crumb['sectionRoute']);
    }

    public function test_user_guide_pages_stay_two_levels(): void
    {
        $crumb = DocsUtils::breadcrumb('gift-cards');

        $this->assertSame('Gift Cards', $crumb['currentTitle']);
        $this->assertNull($crumb['sectionRoute']);
    }

    public function test_page_keys_and_routes_are_unique(): void
    {
        $routes = array_column(DocsUtils::pages(), 'route');
        $paths = array_column(DocsUtils::pages(), 'path');

        $this->assertSame(array_unique($routes), $routes, 'Duplicate route name in config/docs.php.');
        $this->assertSame(array_unique($paths), $paths, 'Duplicate path in config/docs.php.');
    }

    /**
     * The AP Help button deep-links into docs by raw path. Those paths are
     * maintained separately (they carry per-anchor detail the manifest does
     * not), so this catches a page being renamed or moved out from under them.
     */
    public function test_help_button_doc_paths_all_exist_in_the_manifest(): void
    {
        $source = file_get_contents(app_path('Utils/HelpUtils.php'));
        preg_match_all("#'(/docs/[a-z0-9/-]+)(?:\#[a-z0-9-]+)?'#", $source, $matches);

        $known = array_column(DocsUtils::pages(), 'path');
        $known[] = '/docs';

        foreach (array_unique($matches[1]) as $path) {
            $this->assertContains(
                $path,
                $known,
                "HelpUtils links to '{$path}', which is not a page in config/docs.php."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Anchor guards
    |--------------------------------------------------------------------------
    |
    | The checks above validate PAGES. Nothing validated the '#anchor' half of a
    | link, which is how /docs/tickets#boost, #text-templates and
    | #available-variables all survived: each named a real page, so every
    | existing assertion passed while the link landed at the top of the wrong
    | section.
    |
    */

    /**
     * Every id a page can be linked to, including ids that live in a partial the
     * page @includes.
     *
     * Both federation pages are 16-line shells around
     * partials/federation-content.blade.php, so scanning the page file alone
     * reports six of its section ids as missing.
     *
     * Skips <x-doc-screenshot id="..."> - there `id` is a prop naming an image
     * file, not an attribute that reaches the rendered HTML.
     */
    private function anchorsFor(string $key): array
    {
        $file = resource_path('views/marketing/docs/'.$key.'.blade.php');

        if (! is_file($file)) {
            return [];
        }

        $sources = [file_get_contents($file)];

        preg_match_all("#@include\('marketing\.docs\.([a-z0-9._-]+)'#", $sources[0], $includes);

        foreach ($includes[1] as $include) {
            $partial = resource_path('views/marketing/docs/'.str_replace('.', '/', $include).'.blade.php');

            if (is_file($partial)) {
                $sources[] = file_get_contents($partial);
            }
        }

        $ids = [];

        foreach ($sources as $source) {
            $source = preg_replace('#<x-doc-screenshot\b[^>]*>#', '', $source);
            preg_match_all('#\bid="([a-z0-9-]+)"#', $source, $matches);
            $ids = array_merge($ids, $matches[1]);
        }

        return array_unique($ids);
    }

    /** Manifest page key for a /docs URL, or null when the URL is not a doc page. */
    private function pageKeyForUrl(string $url): ?string
    {
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        foreach (DocsUtils::pages() as $key => $page) {
            if ($page['path'] === $path) {
                return $key;
            }
        }

        return null;
    }

    public function test_doc_pages_do_not_declare_the_same_anchor_twice(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $source = file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php'));
            $source = preg_replace('#<x-doc-screenshot\b[^>]*>#', '', $source);
            preg_match_all('#\bid="([a-z0-9-]+)"#', $source, $matches);

            $duplicates = array_keys(array_filter(array_count_values($matches[1]), fn ($n) => $n > 1));

            $this->assertEmpty(
                $duplicates,
                "Docs page '{$key}' declares duplicate id(s): ".implode(', ', $duplicates).'.'
            );
        }
    }

    public function test_every_internal_doc_link_resolves_to_a_real_anchor(): void
    {
        $routeToKey = [];

        foreach (DocsUtils::pages() as $key => $page) {
            $routeToKey[$page['route']] = $key;
        }

        foreach (DocsUtils::pages() as $key => $page) {
            $source = file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php'));
            $own = $this->anchorsFor($key);

            // Cross-page: route('marketing.docs.x') }}#anchor
            preg_match_all("#route\('(marketing\.docs\.[a-z_.]+)'\)\s*\}\}\#([a-z0-9-]+)#", $source, $cross, PREG_SET_ORDER);

            foreach ($cross as [, $route, $anchor]) {
                $target = $routeToKey[$route] ?? null;

                $this->assertNotNull($target, "Docs page '{$key}' links to unknown route '{$route}'.");
                $this->assertContains(
                    $anchor,
                    $this->anchorsFor($target),
                    "Docs page '{$key}' links to '{$target}#{$anchor}', which does not exist."
                );
            }

            // Same-page: href="#anchor"
            preg_match_all('#href="\#([a-z0-9-]+)"#', $source, $same);

            foreach (array_unique($same[1]) as $anchor) {
                $this->assertContains(
                    $anchor,
                    $own,
                    "Docs page '{$key}' links to '#{$anchor}' on itself, which does not exist."
                );
            }
        }
    }

    public function test_help_button_anchors_all_exist(): void
    {
        $source = file_get_contents(app_path('Utils/HelpUtils.php'));
        preg_match_all("#'(/docs/[a-z0-9/-]+)\#([a-z0-9-]+)'#", $source, $matches, PREG_SET_ORDER);

        $this->assertNotEmpty($matches, 'Expected HelpUtils to deep-link into docs with anchors.');

        foreach ($matches as [, $path, $anchor]) {
            $key = $this->pageKeyForUrl($path);

            $this->assertNotNull($key, "HelpUtils links to '{$path}', which is not a page in config/docs.php.");
            $this->assertContains(
                $anchor,
                $this->anchorsFor($key),
                "HelpUtils links to '{$path}#{$anchor}', which does not exist."
            );
        }
    }

    public function test_search_index_entries_point_at_real_pages_and_anchors(): void
    {
        foreach ($this->searchIndex() as $row) {
            $label = "Search index entry '{$row['page']} / {$row['section']}'";

            // Rows may point at marketing feature pages rather than docs; only
            // the doc ones can be checked against the manifest.
            $key = $this->pageKeyForUrl($row['url']);

            if ($key === null) {
                $this->assertStringNotContainsString(
                    '/docs/',
                    parse_url($row['url'], PHP_URL_PATH) ?? '',
                    "{$label} points at a /docs URL that is not a page in config/docs.php: {$row['url']}"
                );

                continue;
            }

            $anchor = parse_url($row['url'], PHP_URL_FRAGMENT);

            if ($anchor !== null) {
                $this->assertContains(
                    $anchor,
                    $this->anchorsFor($key),
                    "{$label} links to '{$key}#{$anchor}', which does not exist."
                );
            }
        }
    }

    /**
     * The search widget looks its icon up by the row's 'page' value, falling back
     * to a generic book, so a renamed page degrades silently rather than failing.
     * A row may use either the full title or the shorter nav_title.
     */
    public function test_search_index_page_names_match_manifest_titles(): void
    {
        $names = [];

        foreach (DocsUtils::pages() as $page) {
            $names[] = mb_strtolower($page['title']);

            if (! empty($page['nav_title'])) {
                $names[] = mb_strtolower($page['nav_title']);
            }
        }

        foreach ($this->searchIndex() as $row) {
            $this->assertContains(
                mb_strtolower($row['page']),
                $names,
                "Search index uses page name '{$row['page']}', which matches no title or nav_title in config/docs.php."
            );

            $this->assertNotSame(
                'book',
                $row['icon'],
                "Search index row '{$row['page']} / {$row['section']}' fell back to the generic book icon."
            );
        }
    }

    public function test_every_manifest_page_is_reachable_from_search(): void
    {
        $covered = [];

        foreach ($this->searchIndex() as $row) {
            if ($key = $this->pageKeyForUrl($row['url'])) {
                $covered[$key] = true;
            }
        }

        foreach (DocsUtils::pages() as $key => $page) {
            $this->assertArrayHasKey(
                $key,
                $covered,
                "Docs page '{$key}' has no entry in the docs search index."
            );
        }
    }

    /**
     * RouteLoadTest renders the doc pages too, but from a hand-maintained list of
     * paths. Driving this off the manifest means a page added there cannot quietly
     * end up with no render coverage at all.
     */
    public function test_every_manifest_page_renders(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $status = $this->get($page['path'])->status();

            $this->assertSame(200, $status, "Docs page '{$key}' ({$page['path']}) returned {$status}.");
        }
    }

    public function test_every_manifest_page_is_in_the_sitemap(): void
    {
        $sitemap = file_get_contents(resource_path('views/sitemap.blade.php'));

        foreach (DocsUtils::pages() as $key => $page) {
            $this->assertTrue(
                str_contains($sitemap, $page['route']) || str_contains($sitemap, $page['path']),
                "Docs page '{$key}' is missing from resources/views/sitemap.blade.php."
            );
        }
    }

    /** @return array<int, array{page: string, section: string, url: string}> */
    private function searchIndex(): array
    {
        $response = $this->get(route('marketing.docs.search_index'));

        $response->assertOk();

        $index = $response->json();

        $this->assertNotEmpty($index, 'The docs search index is empty.');

        return $index;
    }
}
