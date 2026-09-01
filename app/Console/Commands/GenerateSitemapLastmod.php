<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;

/**
 * Rebuilds config/sitemap_lastmod.php, the per-page <lastmod> manifest for /sitemap-pages.xml.
 *
 * Why a committed manifest rather than something computed at request time: the deployed container
 * has neither usable file mtimes (the buildpack stamps every file 1980-01-01) nor a git history,
 * so the only place the real dates exist is here, in a working checkout. Before this file existed
 * every one of the ~150 static pages reported the same <lastmod> - the container's boot time - so
 * every deploy told Google that the whole marketing site had changed, which is exactly what makes
 * Google stop trusting the signal.
 *
 * Run it before a release (see CLAUDE.md) and commit the result. A page missing from the manifest
 * simply gets no <lastmod>, which Google prefers to a wrong one.
 *
 * Resolution, per marketing GET route:
 *   1. Reflect the controller method and take the single view('marketing.…') literal in its body.
 *      Routes with no literal (JSON endpoints, redirects) or several are skipped and reported;
 *      the two legal pages whose view is chosen at runtime are pinned in VIEW_OVERRIDES.
 *   2. Follow @include() one level at a time, so a page whose body lives in a shared partial
 *      (both federation docs pages) tracks that partial.
 *   3. A view shared by several routes (compare-single, replace-single) also tracks the data block
 *      that distinguishes this page from its siblings - `'<key>' => [` inside the controller helper
 *      the method calls - via `git log -L`, falling back to the whole controller.
 *   4. The date is the newest `git log -1 --format=%cI` across that file set, in UTC.
 */
class GenerateSitemapLastmod extends Command
{
    protected $signature = 'sitemap:lastmod {--dry-run : Print the manifest instead of writing it}';

    protected $description = 'Rebuild config/sitemap_lastmod.php so the pages sitemap carries a real per-page <lastmod>';

    private const MANIFEST = 'config/sitemap_lastmod.php';

    private const CONTROLLER = 'app/Http/Controllers/MarketingController.php';

    /**
     * Routes whose view cannot be read off the method source.
     *
     * /privacy and /terms-of-service go through LegalController, which picks between an
     * operator-authored document and the bundled marketing page at runtime
     * (LegalDocument::BUILTIN_VIEWS). The bundled page is the one whose history is meaningful.
     * /cookie-policy is deliberately absent: nothing is bundled for it, so there is no file to
     * date, and the sitemap only lists it when an operator has written one.
     */
    private const VIEW_OVERRIDES = [
        'marketing.privacy' => 'marketing.privacy',
        'marketing.terms' => 'marketing.terms',
    ];

    public function handle(): int
    {
        if (! config('app.is_nexus')) {
            $this->error('The marketing routes are only registered on the nexus. Re-run with IS_NEXUS=true.');

            return self::FAILURE;
        }

        if (! Route::has('marketing.index')) {
            $this->error('marketing.* routes are not registered, so there is nothing to resolve.');

            return self::FAILURE;
        }

        if (! $this->git(['rev-parse', '--is-inside-work-tree'])) {
            $this->error('Not a git checkout - the dates come from git history, so there is nothing to read.');

            return self::FAILURE;
        }

        $routes = $this->pageRoutes();

        if (! $routes) {
            $this->error('No marketing GET routes found.');

            return self::FAILURE;
        }

        $manifest = [];
        $skipped = [];

        foreach ($routes as $name => $route) {
            $sources = $this->sourcesFor($name, $route['action'], $routes);

            if (! $sources) {
                $skipped[$name] = $route['path'];

                continue;
            }

            $date = $this->newestDate($sources);

            if (! $date) {
                $skipped[$name] = $route['path'].' (no git history)';

                continue;
            }

            $manifest[$route['path']] = $date;
        }

        ksort($manifest);

        foreach ($skipped as $name => $where) {
            $this->line('  skipped '.$name.' - '.$where);
        }

        $contents = $this->render($manifest);

        if ($this->option('dry-run')) {
            $this->line($contents);
        } else {
            file_put_contents(base_path(self::MANIFEST), $contents);
            $this->info('Wrote '.self::MANIFEST.'.');
        }

        $this->info(count($manifest).' pages dated, '.count($skipped).' skipped.');

        return self::SUCCESS;
    }

    /**
     * The marketing GET routes that could be a plain page, keyed by route name.
     *
     * Parameterised URIs are excluded: nothing in the static sitemap has one, and a path with a
     * {placeholder} in it could never match a <loc>.
     */
    private function pageRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            $action = $route->getAction('uses');

            if (! $name || ! str_starts_with($name, 'marketing.')) {
                continue;
            }

            if (! in_array('GET', $route->methods(), true) || ! is_string($action)) {
                continue;
            }

            if (str_contains($route->uri(), '{')) {
                continue;
            }

            // Both route blocks in routes/web.php register the same names, so the first wins.
            $routes[$name] ??= [
                'path' => '/'.ltrim($route->uri() === '/' ? '' : $route->uri(), '/'),
                'action' => $action,
            ];
        }

        return $routes;
    }

    /**
     * Every file whose history counts as this page's history, or an empty array when the page
     * cannot be resolved to one.
     *
     * @return array<int, array{file: string, lines?: array{int, int}}>
     */
    private function sourcesFor(string $name, string $action, array $routes): array
    {
        [$class, $method] = array_pad(explode('@', $action, 2), 2, '__invoke');

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return [];
        }

        $body = $this->methodSource($class, $method);
        $view = $this->viewFor($name, $body);

        if (! $view) {
            return [];
        }

        $file = 'resources/views/'.str_replace('.', '/', $view).'.blade.php';

        if (! is_file(base_path($file))) {
            return [];
        }

        $sources = [];

        foreach ($this->withIncludes($file) as $included) {
            $sources[] = ['file' => $included];
        }

        // compare-single and replace-single render 28 different pages; the view alone would give
        // all of them one shared date, which is the failure mode this whole manifest exists to
        // undo. The page's own data block is what actually changes.
        if ($this->sharedView($view, $routes) && ($shared = $this->sharedDataSource($class, $body))) {
            $sources[] = $shared;
        }

        return $sources;
    }

    private function viewFor(string $name, string $body): ?string
    {
        if (isset(self::VIEW_OVERRIDES[$name])) {
            return self::VIEW_OVERRIDES[$name];
        }

        preg_match_all("/view\(\s*'(marketing\.[A-Za-z0-9_.-]+)'/", $body, $matches);

        $views = array_unique($matches[1]);

        return count($views) === 1 ? reset($views) : null;
    }

    /** Whether more than one marketing route renders this view. */
    private function sharedView(string $view, array $routes): bool
    {
        $seen = 0;

        foreach ($routes as $name => $route) {
            [$class, $method] = array_pad(explode('@', $route['action'], 2), 2, '__invoke');

            if (! class_exists($class) || ! method_exists($class, $method)) {
                continue;
            }

            if ($this->viewFor($name, $this->methodSource($class, $method)) === $view) {
                $seen++;
            }
        }

        return $seen > 1;
    }

    /**
     * The line range of the page's own entry in the controller helper its method calls, e.g. the
     * `'eventbrite' => [ … ]` block inside getComparisonData(). Falls back to the whole controller
     * file, which is coarse but never wrong.
     *
     * @return array{file: string, lines?: array{int, int}}|null
     */
    private function sharedDataSource(string $class, string $body): ?array
    {
        $whole = ['file' => self::CONTROLLER];

        if (! is_file(base_path(self::CONTROLLER))) {
            return null;
        }

        if (! preg_match("/\\\$this->(\w+)\(\s*'([^']+)'\s*\)/", $body, $call)) {
            return $whole;
        }

        [, $helper, $key] = $call;

        if (! method_exists($class, $helper)) {
            return $whole;
        }

        try {
            $reflection = new \ReflectionMethod($class, $helper);
        } catch (\ReflectionException) {
            return $whole;
        }

        if ($reflection->getFileName() !== base_path(self::CONTROLLER)) {
            return $whole;
        }

        $lines = file(base_path(self::CONTROLLER));
        $depth = 0;
        $start = null;

        for ($i = $reflection->getStartLine() - 1; $i < $reflection->getEndLine(); $i++) {
            $line = $lines[$i] ?? '';

            if ($start === null) {
                if (! str_contains($line, "'".$key."' => [")) {
                    continue;
                }

                $start = $i + 1;
            }

            $depth += substr_count($line, '[') - substr_count($line, ']');

            if ($start !== null && $depth <= 0) {
                return ['file' => self::CONTROLLER, 'lines' => [$start, $i + 1]];
            }
        }

        return $whole;
    }

    /**
     * A view plus every view it @includes, transitively.
     *
     * Only literal includes are followed, which is all this codebase has. Blade components are
     * deliberately not followed: they are page chrome (the layout, the related-pages strip), and
     * treating a chrome edit as a content change on every page is the churn being fixed.
     *
     * @return array<int, string>
     */
    private function withIncludes(string $file, array $seen = []): array
    {
        if (in_array($file, $seen, true)) {
            return [];
        }

        $seen[] = $file;
        $files = [$file];

        preg_match_all("/@include\(\s*'([A-Za-z0-9_.-]+)'/", (string) file_get_contents(base_path($file)), $matches);

        foreach (array_unique($matches[1]) as $view) {
            $included = 'resources/views/'.str_replace('.', '/', $view).'.blade.php';

            if (is_file(base_path($included))) {
                foreach ($this->withIncludes($included, $seen) as $nested) {
                    $files[] = $nested;
                    $seen[] = $nested;
                }
            }
        }

        return $files;
    }

    /**
     * The newest commit date across a file set, as an ISO 8601 UTC string.
     *
     * @param  array<int, array{file: string, lines?: array{int, int}}>  $sources
     */
    private function newestDate(array $sources): ?string
    {
        $newest = null;

        foreach ($sources as $source) {
            $raw = isset($source['lines'])
                ? $this->git(['log', '-1', '--format=%cI', '--no-patch', '-L'.$source['lines'][0].','.$source['lines'][1].':'.$source['file']])
                : $this->git(['log', '-1', '--format=%cI', '--', $source['file']]);

            if (! $raw) {
                continue;
            }

            try {
                $date = Carbon::parse(trim($raw))->utc();
            } catch (\Throwable) {
                continue;
            }

            if ($newest === null || $date->gt($newest)) {
                $newest = $date;
            }
        }

        return $newest?->toIso8601String();
    }

    private function git(array $arguments): ?string
    {
        $result = Process::path(base_path())->run(array_merge(['git'], $arguments));

        return $result->successful() ? trim($result->output()) : null;
    }

    /** @param  array<string, string>  $manifest */
    private function render(array $manifest): string
    {
        $rows = '';

        foreach ($manifest as $path => $date) {
            $rows .= "    '".$path."' => '".$date."',\n";
        }

        return <<<PHP
        <?php

        /*
         * Per-page <lastmod> for /sitemap-pages.xml, keyed by the URL path as it appears in
         * resources/views/sitemap.blade.php.
         *
         * GENERATED - do not hand-edit. Run `php artisan sitemap:lastmod` before a release and
         * commit the result; see app/Console/Commands/GenerateSitemapLastmod.php for how the dates
         * are resolved, and CLAUDE.md for where this sits in the release checklist.
         *
         * The deployed container has neither real file mtimes nor a git history, which is why this
         * is committed rather than computed. A path that is absent gets no <lastmod> at all, which
         * Google prefers to one it can prove wrong.
         *
         * Scalars only - this file is var_export()ed by `php artisan config:cache`.
         */

        return [
        {$rows}];

        PHP;
    }

    /** The source of one method, so a view literal can be read out of it. */
    private function methodSource(string $class, string $method): string
    {
        static $memo = [];

        $key = $class.'@'.$method;

        if (isset($memo[$key])) {
            return $memo[$key];
        }

        try {
            $reflection = new \ReflectionMethod($class, $method);
        } catch (\ReflectionException) {
            return $memo[$key] = '';
        }

        $file = $reflection->getFileName();

        if (! $file || ! is_file($file)) {
            return $memo[$key] = '';
        }

        $lines = file($file);
        $slice = array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1
        );

        return $memo[$key] = implode('', $slice);
    }
}
