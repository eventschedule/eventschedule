<?php

namespace App\Utils;

use Illuminate\Support\Facades\Route;

/**
 * Accessors over the documentation manifest in config/docs.php.
 *
 * The config file holds pure data (it is var_export()ed by `config:cache`);
 * everything that needs route resolution or derivation lives here.
 */
class DocsUtils
{
    /** Route name => page key, built lazily. */
    private static ?array $routeIndex = null;

    /**
     * Every page, keyed by manifest key, with the key merged in as 'key'.
     */
    public static function pages(): array
    {
        static $pages = null;

        if ($pages === null) {
            $pages = [];
            foreach (config('docs.pages', []) as $key => $page) {
                $pages[$key] = $page + ['key' => $key];
            }
        }

        return $pages;
    }

    public static function page(?string $key): ?array
    {
        return $key === null ? null : (self::pages()[$key] ?? null);
    }

    public static function pageByRoute(?string $routeName): ?array
    {
        if ($routeName === null) {
            return null;
        }

        if (self::$routeIndex === null) {
            self::$routeIndex = [];
            foreach (self::pages() as $key => $page) {
                self::$routeIndex[$page['route']] = $key;
            }
        }

        return self::page(self::$routeIndex[$routeName] ?? null);
    }

    /**
     * The page for the current request, or null when not on a doc page.
     */
    public static function currentPage(): ?array
    {
        return self::pageByRoute(Route::currentRouteName());
    }

    /**
     * Groups, keyed by group key, with the key merged in as 'key'.
     */
    public static function groups(): array
    {
        static $groups = null;

        if ($groups === null) {
            $groups = [];
            foreach (config('docs.groups', []) as $key => $group) {
                $groups[$key] = $group + ['key' => $key];
            }
        }

        return $groups;
    }

    public static function group(?string $key): ?array
    {
        return $key === null ? null : (self::groups()[$key] ?? null);
    }

    /**
     * Pages in a group, in manifest order.
     */
    public static function pagesInGroup(string $group): array
    {
        return array_values(array_filter(
            self::pages(),
            fn ($page) => $page['group'] === $group
        ));
    }

    /**
     * Pages in an index-page cluster, in manifest order.
     */
    public static function pagesInCluster(string $cluster): array
    {
        return array_values(array_filter(
            self::pages(),
            fn ($page) => ($page['cluster'] ?? null) === $cluster
        ));
    }

    public static function clusters(): array
    {
        $clusters = [];

        foreach (config('docs.clusters', []) as $key => $cluster) {
            $clusters[$key] = $cluster + [
                'key' => $key,
                'pages' => self::pagesInCluster($key),
            ];
        }

        return $clusters;
    }

    /**
     * Absolute URL for a page, with an optional '#anchor'.
     */
    public static function url(string $key, string $anchor = ''): string
    {
        $page = self::page($key);

        return $page ? route($page['route']).$anchor : '';
    }

    /**
     * Page key => URL, for callers that need the whole map at once
     * (getDocSearchIndex, the sitemap).
     */
    public static function urlMap(): array
    {
        return array_map(fn ($page) => route($page['route']), self::pages());
    }

    /**
     * Previous and next page WITHIN the page's own group.
     *
     * Returns nulls for an unknown key. The predecessor of this method,
     * MarketingController::getDocNavigation(), compared a null index with
     * `null < count($pages) - 1`, which is true in PHP, so an unlisted route
     * silently reported $pages[1] as its Next.
     *
     * @return array{prev: ?array, next: ?array}
     */
    public static function prevNext(?string $key): array
    {
        $none = ['prev' => null, 'next' => null];

        $page = self::page($key);

        if ($page === null) {
            return $none;
        }

        $siblings = self::pagesInGroup($page['group']);

        $index = null;
        foreach ($siblings as $i => $sibling) {
            if ($sibling['key'] === $page['key']) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return $none;
        }

        return [
            'prev' => $siblings[$index - 1] ?? null,
            'next' => $siblings[$index + 1] ?? null,
        ];
    }

    /**
     * Breadcrumb props for <x-docs-breadcrumb>.
     *
     * The middle crumb is suppressed when the page IS its group's landing
     * page, so pages like /docs/saas and /docs/developer/api do not link to
     * themselves. The User Guide has no landing page of its own (its hub is
     * /docs, which is already the first crumb), so it stays two levels.
     *
     * @return array{currentTitle: string, section: ?string, sectionTitle: ?string, sectionRoute: ?string}
     */
    public static function breadcrumb(string $key): array
    {
        $page = self::page($key);

        if ($page === null) {
            return ['currentTitle' => '', 'section' => null, 'sectionTitle' => null, 'sectionRoute' => null];
        }

        $group = self::group($page['group']);
        $indexRoute = $group['index_route'] ?? null;
        $selfReferential = $indexRoute !== null && $indexRoute === $page['route'];

        $showSection = $indexRoute !== null && ! $selfReferential;

        return [
            'currentTitle' => $page['title'],
            'section' => $showSection ? $page['group'] : null,
            'sectionTitle' => $showSection ? $group['title'] : null,
            'sectionRoute' => $showSection ? $indexRoute : null,
        ];
    }

    /**
     * Distinct icon keys across the manifest, for the inline SVG sprite.
     */
    public static function iconKeys(): array
    {
        $keys = array_column(self::pages(), 'icon');

        foreach (self::groups() as $group) {
            if (! empty($group['icon'])) {
                $keys[] = $group['icon'];
            }
        }

        $keys = array_values(array_unique(array_filter($keys)));
        sort($keys);

        return $keys;
    }

    /**
     * Page key => icon key, for the search widget's result rows.
     */
    public static function iconMap(): array
    {
        return array_map(fn ($page) => $page['icon'] ?? 'book', self::pages());
    }

    /**
     * Label shown in the left rail, falling back to the page title.
     */
    public static function navTitle(array $page): string
    {
        return $page['nav_title'] ?? $page['title'];
    }
}
