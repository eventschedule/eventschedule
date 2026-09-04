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
 * Run it in any commit that edits a marketing page (see CLAUDE.md) and commit the result; CI
 * regenerates this and fails the build on a diff. A page missing from the manifest simply gets no
 * <lastmod>, which Google prefers to a wrong one.
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
 *   4. The date is the newest `git log -1 --format=%cI` across that file set, as a plain Y-m-d in
 *      UTC. A source the working tree has already changed is dated TODAY instead of read from git,
 *      and a view that exists but has no history at all - a page being added in the commit being
 *      written - is dated today too, because an undated page fails SitemapCoverageTest.
 *
 * Why days rather than timestamps, and why today for a file with uncommitted changes: this manifest
 * is committed, and the commit that edits a view is the commit that becomes that view's newest
 * commit. A run made just before that commit therefore has to predict its date, which is possible
 * to the day and impossible to the second. Get either half wrong and the manifest is permanently one
 * commit behind, which makes the "Sitemap lastmod manifest is current" step in
 * .github/workflows/test.yml unsatisfiable: it regenerates and diffs, so every push that touches a
 * marketing page fails, including the one that dutifully refreshed the manifest. Do not restore
 * second precision.
 *
 * Two cases still need a second run, both harmless and both a one-line fix: generating just before
 * UTC midnight and committing just after it, and a rebase, which resets committer dates to now.
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

    /** @var array<string, array<int, array{oldStart: int, oldCount: int, newStart: int, newCount: int}>>|null */
    private ?array $changes = null;

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
        $uncommitted = [];

        foreach ($routes as $name => $route) {
            $sources = $this->sourcesFor($name, $route['action'], $routes);

            if (! $sources) {
                $skipped[$name] = $route['path'];

                continue;
            }

            $date = $this->newestDate($sources);

            if (! $date) {
                // The view is on disk but git has never seen it, which is exactly what a page
                // being added in the commit currently being written looks like. Skipping it left
                // that page undated, and SitemapCoverageTest asserts the undated list, so a new
                // page could not be green in its own commit. Date it now instead.
                $date = $this->uncommittedDate();
                $uncommitted[$name] = $sources[0]['file'];
            }

            $manifest[$route['path']] = $date;
        }

        ksort($manifest);

        foreach ($skipped as $name => $where) {
            $this->line('  skipped '.$name.' - '.$where);
        }

        foreach ($uncommitted as $name => $file) {
            $this->line('  '.$name.' - '.$file.': not in git yet, dated today because today is when it is being committed');
        }

        $contents = $this->render($manifest);

        if ($this->option('dry-run')) {
            $this->line($contents);
        } else {
            file_put_contents(base_path(self::MANIFEST), $contents);
            $this->info('Wrote '.self::MANIFEST.'.');
        }

        $this->info(count($manifest).' pages dated ('.count($uncommitted).' from a view git has not seen yet), '.
            count($skipped).' skipped.');

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
     * The newest date across a file set, as a plain Y-m-d in UTC, or null when git has never seen
     * any of them.
     *
     * Sources are compared at full precision and only the winner is truncated, so "newest" still
     * means newest.
     *
     * @param  array<int, array{file: string, lines?: array{int, int}}>  $sources
     */
    private function newestDate(array $sources): ?string
    {
        $newest = null;

        foreach ($sources as $source) {
            $date = $this->dateFor($source);

            if ($date === null) {
                continue;
            }

            if ($newest === null || $date->gt($newest)) {
                $newest = $date;
            }
        }

        return $newest?->toDateString();
    }

    /**
     * One source's date: today when the working tree has already changed it, otherwise the date of
     * the newest commit that touched it.
     *
     * The dirty case is what lets a run made just before a commit predict what the same run will
     * produce just after it - that commit is about to become the file's newest commit, and its date
     * is today. See the class docblock for why the alternative is unworkable.
     *
     * @param  array{file: string, lines?: array{int, int}}  $source
     */
    private function dateFor(array $source): ?Carbon
    {
        if (! isset($source['lines'])) {
            if (isset($this->changes()[$source['file']])) {
                return Carbon::now()->utc();
            }

            $raw = $this->git(['log', '-1', '--format=%cI', '--', $source['file']]);
        } else {
            // A line range counts as changed only when an edit actually lands inside it. Dating
            // every range in a dirty MarketingController today would not converge: after the commit
            // `git log -L` still reports the older commit for the data blocks that commit left
            // alone, so the 29 compare and replace pages would drift straight back on the next run.
            if ($this->rangeIsDirty($source['file'], $source['lines'])) {
                return Carbon::now()->utc();
            }

            [$from, $to] = $this->rangeInHead($source['file'], $source['lines']);

            $raw = $this->git(['log', '-1', '--format=%cI', '--no-patch', '-L'.$from.','.$to.':'.$source['file']]);
        }

        if (! $raw) {
            return null;
        }

        try {
            return Carbon::parse(trim($raw))->utc();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The working tree's uncommitted changes, as hunk line ranges keyed by repo-relative path.
     *
     * One `git diff` for the whole run, memoized: the command already spawns a `git log` per source
     * file, so the cost is noise. `HEAD` rather than nothing, so staged and unstaged changes both
     * count - a view is about to be committed either way. `--unified=0` so a hunk covers only the
     * lines that actually changed, which is what makes the line-range test above meaningful.
     *
     * Untracked files are deliberately absent: git has no history for one, so newestDate() returns
     * null and the uncommittedDate() fallback already dates it today. A rename lands in that same
     * fallback, because the new path has no history in HEAD either.
     *
     * A path git has to quote (a space or a non-ASCII byte in it) does not match the header pattern
     * and reads as clean. Nothing under resources/views is named that way, and the cost would be a
     * stale date rather than a wrong one.
     *
     * Overridable so tests can drive both branches without depending on the real working tree.
     *
     * @return array<string, array<int, array{oldStart: int, oldCount: int, newStart: int, newCount: int}>>
     */
    protected function workingTreeChanges(): array
    {
        $diff = $this->git(['diff', '--unified=0', '--no-color', '--no-ext-diff', 'HEAD']);

        return $diff ? $this->parseDiff($diff) : [];
    }

    /**
     * The hunk ranges in a `git diff --unified=0` document, keyed by the new-side path.
     *
     * Split out from the git call so it can be tested against a literal diff: this is a regex over
     * another program's output, and the cost of getting a hunk header subtly wrong is a page dated
     * from the wrong commit, which nothing else here would notice.
     *
     * @return array<string, array<int, array{oldStart: int, oldCount: int, newStart: int, newCount: int}>>
     */
    private function parseDiff(string $diff): array
    {
        $changes = [];
        $file = null;

        foreach (explode("\n", $diff) as $line) {
            if (preg_match('~^diff --git a/(.+) b/(.+)$~', $line, $header)) {
                $file = $header[2];

                continue;
            }

            if ($file === null || ! preg_match('/^@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@/', $line, $hunk)) {
                continue;
            }

            $changes[$file][] = [
                'oldStart' => (int) $hunk[1],
                'oldCount' => ! isset($hunk[2]) || $hunk[2] === '' ? 1 : (int) $hunk[2],
                'newStart' => (int) $hunk[3],
                'newCount' => ! isset($hunk[4]) || $hunk[4] === '' ? 1 : (int) $hunk[4],
            ];
        }

        return $changes;
    }

    /** @return array<string, array<int, array{oldStart: int, oldCount: int, newStart: int, newCount: int}>> */
    private function changes(): array
    {
        return $this->changes ??= $this->workingTreeChanges();
    }

    /**
     * Whether any uncommitted change falls inside a working-tree line range.
     *
     * @param  array{int, int}  $lines
     */
    private function rangeIsDirty(string $file, array $lines): bool
    {
        foreach ($this->changes()[$file] ?? [] as $hunk) {
            [$from, $to] = $this->hunkExtent($hunk);

            if ($from <= $lines[1] && $to >= $lines[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * A working-tree line range translated into HEAD's numbering.
     *
     * sharedDataSource() locates the page's data block in the file on disk, but `git log -L` reads
     * its range out of HEAD, so the two disagree by however many lines the uncommitted edits added
     * or removed above the block. On a clean tree that difference is zero, which is why this never
     * mattered before; dating a dirty tree is now the normal case, and an unshifted range reads a
     * neighbouring competitor's history instead.
     *
     * Only hunks entirely above the range are counted - one that overlaps it never reaches here,
     * because rangeIsDirty() has already dated the page today.
     *
     * @param  array{int, int}  $lines
     * @return array{int, int}
     */
    private function rangeInHead(string $file, array $lines): array
    {
        $shift = 0;

        foreach ($this->changes()[$file] ?? [] as $hunk) {
            if ($this->hunkExtent($hunk)[1] < $lines[0]) {
                $shift += $hunk['oldCount'] - $hunk['newCount'];
            }
        }

        return [max(1, $lines[0] + $shift), max(1, $lines[1] + $shift)];
    }

    /**
     * The new-side lines a hunk covers.
     *
     * A `+c,0` hunk is a pure deletion: nothing occupies a new-side line, and what vanished sat
     * between c and c+1, so cover both rather than let a block that lost its last line read as
     * untouched.
     *
     * @param  array{oldStart: int, oldCount: int, newStart: int, newCount: int}  $hunk
     * @return array{int, int}
     */
    private function hunkExtent(array $hunk): array
    {
        return $hunk['newCount'] === 0
            ? [$hunk['newStart'], $hunk['newStart'] + 1]
            : [$hunk['newStart'], $hunk['newStart'] + $hunk['newCount'] - 1];
    }

    /**
     * The date a page whose view has no git history at all gets: today, in the same Y-m-d shape the
     * git-derived dates use, so the manifest's format check cannot tell the two apart.
     *
     * It is a prediction, and the same one dateFor() makes for a file with uncommitted changes: the
     * page is about to be committed, and that commit is today. The alternative - no date at all -
     * is worse, because an absent entry reads as a stale manifest rather than as a new page.
     */
    private function uncommittedDate(): string
    {
        return Carbon::now()->utc()->toDateString();
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
         * GENERATED - do not hand-edit. Run `php artisan sitemap:lastmod` in any commit that edits a
         * marketing page and commit the result; CI regenerates this file and fails the build on a
         * diff. See app/Console/Commands/GenerateSitemapLastmod.php for how the dates are resolved.
         *
         * The dates are days rather than timestamps on purpose: the commit that edits a view is the
         * commit that becomes that view's newest commit, so the run that necessarily precedes it can
         * predict the day and cannot predict the second.
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
