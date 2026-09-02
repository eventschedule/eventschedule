<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * <x-marketing.related-pages /> reads config/marketing_related.php by request path and renders
 * NOTHING when the key is missing. That silence is the whole problem: 80 views invoked the
 * component while the config had 31 keys, so about 49 pages shipped a component that produced
 * no markup, no internal links and no sign anything was wrong.
 *
 * Both directions are checked here. A view that invokes the component with no key renders an
 * empty strip; a key that matches no page is dead weight that will rot (config had a
 * docs/allocated-seating key, and docs pages do not render this component at all).
 *
 * Which paths a view serves cannot be read off the routes - compare-single.blade.php serves 16
 * URLs and replace-single.blade.php serves 12 - so the pages are rendered and the view stack is
 * recorded through a wildcard composer.
 */
class MarketingRelatedPagesTest extends TestCase
{
    use RefreshDatabase;

    private const MIN_ROWS = 3;

    private const MAX_ROWS = 6;

    /** Not pages: a noindex search, a JSON payload, and four 301s. */
    private const NOT_A_PAGE = [
        'marketing.search',
        'marketing.docs.search_index',
        'marketing.docs.schedule_basics',
        'marketing.docs.availability',
        'marketing.docs.fan_content',
        'marketing.docs.polls',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        LegalDocument::create([
            'type' => LegalDocument::COOKIES,
            'content' => 'We use cookies to keep you signed in.',
        ]);
    }

    /** Dotted view names under resources/views/marketing that invoke the component. */
    private function viewsInvokingTheComponent(): array
    {
        $files = array_merge(
            glob(resource_path('views/marketing/*.blade.php')),
            glob(resource_path('views/marketing/**/*.blade.php'))
        );

        $views = [];

        foreach ($files as $file) {
            if (! str_contains(file_get_contents($file), 'x-marketing.related-pages')) {
                continue;
            }

            $key = str_replace([resource_path('views/'), '.blade.php'], '', $file);
            $views[] = str_replace('/', '.', $key);
        }

        return $views;
    }

    /** Every marketing GET route that is a page, as path => route name. */
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

            if (in_array($name, self::NOT_A_PAGE, true)) {
                continue;
            }

            $routes['/'.ltrim($route->uri() === '/' ? '' : $route->uri(), '/')] ??= $name;
        }

        return $routes;
    }

    /**
     * The config keys the rendered site actually asks for: one per page whose view stack
     * includes a view that invokes the component.
     */
    private function keysThePagesAskFor(): array
    {
        $invoking = $this->viewsInvokingTheComponent();
        $seen = [];

        View::composer('*', function ($view) use (&$seen) {
            $seen[] = $view->name();
        });

        $keys = [];

        foreach ($this->pageRoutes() as $path => $name) {
            $seen = [];
            $this->get($path)->assertOk();

            if (array_intersect($seen, $invoking)) {
                $keys[trim($path, '/')] = $name;
            }
        }

        return $keys;
    }

    public function test_every_page_that_invokes_the_component_has_an_entry(): void
    {
        $config = config('marketing_related');
        $missing = [];

        foreach ($this->keysThePagesAskFor() as $key => $name) {
            if (empty($config[$key])) {
                $missing[] = "{$key} ({$name})";
            }
        }

        $this->assertSame([], $missing,
            'these pages render an empty Related strip; add them to config/marketing_related.php');
    }

    public function test_no_entry_is_dead(): void
    {
        $asked = array_keys($this->keysThePagesAskFor());
        $orphans = array_values(array_diff(array_keys(config('marketing_related')), $asked));

        $this->assertSame([], $orphans,
            'no page reads these keys, so they render nowhere; remove them or add the component to the view');
    }

    public function test_every_entry_is_well_formed(): void
    {
        $problems = [];

        foreach (config('marketing_related') as $key => $rows) {
            $count = count($rows);

            if ($count < self::MIN_ROWS || $count > self::MAX_ROWS) {
                $problems[] = "{$key} has {$count} rows (want ".self::MIN_ROWS.'-'.self::MAX_ROWS.')';
            }

            foreach ($rows as $index => $row) {
                foreach (['title', 'path', 'blurb'] as $field) {
                    if (empty($row[$field] ?? null)) {
                        $problems[] = "{$key}[{$index}] has no '{$field}'";
                    }
                }

                if (($row['path'] ?? '') === '/'.$key) {
                    $problems[] = "{$key}[{$index}] links to itself";
                }
            }

            $paths = array_column($rows, 'path');

            if (count(array_unique($paths)) !== count($paths)) {
                $problems[] = "{$key} lists the same path twice";
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * The strip prints the title and the blurb through {{ }}, which escapes what it is given. A
     * row that already carries an entity therefore renders it doubly encoded, and the card reads
     * "For Live Q&amp;A Sessions" on the page - which is how two rows shipped.
     */
    public function test_no_entry_carries_an_html_entity(): void
    {
        $offenders = [];

        foreach (config('marketing_related') as $key => $rows) {
            foreach ($rows as $index => $row) {
                foreach (['title', 'blurb'] as $field) {
                    if (preg_match('/&[a-z]+;|&#/i', (string) ($row[$field] ?? ''))) {
                        $offenders[] = "{$key}[{$index}].{$field}: ".$row[$field];
                    }
                }
            }
        }

        $this->assertSame([], $offenders,
            'write the character itself - the component escapes on output, so an entity here is printed literally');
    }

    /**
     * The strip renders url($item['path']) with no validation, so a stale path ships a 404 or a
     * redirect to every visitor of the page that lists it.
     */
    public function test_every_linked_path_is_a_registered_route(): void
    {
        $registered = [];

        foreach (Route::getRoutes() as $route) {
            if (in_array('GET', $route->methods(), true) && ! str_contains($route->uri(), '{')) {
                $registered[] = '/'.ltrim($route->uri() === '/' ? '' : $route->uri(), '/');
            }
        }

        $registered = array_flip($registered);
        $unknown = [];

        foreach (config('marketing_related') as $key => $rows) {
            foreach ($rows as $row) {
                if (! isset($registered[$row['path']])) {
                    $unknown[] = "{$key} -> {$row['path']}";
                }
            }
        }

        $this->assertSame([], array_values(array_unique($unknown)),
            'these paths are not registered GET routes');
    }

    public function test_no_linked_path_redirects(): void
    {
        $paths = [];

        foreach (config('marketing_related') as $rows) {
            foreach ($rows as $row) {
                $paths[$row['path']] = true;
            }
        }

        $bad = [];

        foreach (array_keys($paths) as $path) {
            $status = $this->get($path)->getStatusCode();

            if ($status !== 200) {
                $bad[] = $path.' -> '.$status;
            }
        }

        $this->assertSame([], $bad, 'a Related card must land on the page it names, not on a hop');
    }
}
