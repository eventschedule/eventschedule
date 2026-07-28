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
}
