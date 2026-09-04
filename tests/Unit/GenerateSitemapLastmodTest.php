<?php

namespace Tests\Unit;

use App\Console\Commands\GenerateSitemapLastmod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Two things about `sitemap:lastmod` are load-bearing and neither is obvious from reading it.
 *
 * A page could not be green in the commit that added it: the view exists, the sitemap lists it,
 * `git log` returns nothing for it, so it landed in the undated list SitemapCoverageTest asserts.
 * That is what uncommittedDate() fixes.
 *
 * And a page could not be green in the commit that EDITED it either, for a subtler reason: that
 * commit becomes the view's newest commit, so no manifest committed inside it can hold its date,
 * and CI's "Sitemap lastmod manifest is current" step - regenerate, diff - failed on every push
 * touching a marketing page. Dating a file the working tree has already changed as today, at day
 * granularity, is what closes that loop. Restore second precision or drop the dirty branch and the
 * check goes unsatisfiable again, which is why both are pinned here.
 *
 * The command is not run end to end - it resolves ~150 routes and shells out to git for each one,
 * about ten seconds - so the pieces are exercised directly instead.
 */
class GenerateSitemapLastmodTest extends TestCase
{
    private string $untracked;

    protected function setUp(): void
    {
        parent::setUp();

        // Under storage/, which is gitignored, so git genuinely has no history for it.
        $this->untracked = 'storage/framework/testing/never-committed-view.blade.php';

        @mkdir(base_path('storage/framework/testing'), 0755, true);
        file_put_contents(base_path($this->untracked), "<p>A page added in this very commit.</p>\n");
    }

    protected function tearDown(): void
    {
        @unlink(base_path($this->untracked));

        parent::tearDown();
    }

    private function invokePrivate(string $method, array $arguments = [], ?GenerateSitemapLastmod $command = null): mixed
    {
        $reflection = new ReflectionMethod(GenerateSitemapLastmod::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($command ?? $this->commandSeeing([]), $arguments);
    }

    /**
     * The command with its view of the working tree pinned.
     *
     * Every test below would otherwise depend on whether the file it names happens to be edited at
     * that moment - and a run of this suite while this command is being worked on is exactly that
     * case, which would quietly send the "reads its date out of git" control down the dirty branch
     * and let it pass for the wrong reason.
     */
    private function commandSeeing(array $changes): GenerateSitemapLastmod
    {
        return new class($changes) extends GenerateSitemapLastmod
        {
            public function __construct(private array $pinned)
            {
                parent::__construct();
            }

            protected function workingTreeChanges(): array
            {
                return $this->pinned;
            }
        };
    }

    /** One hunk, in the shape parseDiff() produces. */
    private function hunk(int $start, int $count): array
    {
        return ['oldStart' => $start, 'oldCount' => $count, 'newStart' => $start, 'newCount' => $count];
    }

    private function gitDate(string $file): string
    {
        $raw = Process::path(base_path())->run(['git', 'log', '-1', '--format=%cI', '--', $file])->output();

        return Carbon::parse(trim($raw))->utc()->toDateString();
    }

    /** What `git log -L` reports for one line range, which is not the whole file's date. */
    private function gitRangeDate(string $file, int $from, int $to): string
    {
        $raw = Process::path(base_path())
            ->run(['git', 'log', '-1', '--format=%cI', '--no-patch', '-L'.$from.','.$to.':'.$file])
            ->output();

        return Carbon::parse(trim($raw))->utc()->toDateString();
    }

    /**
     * A frozen "now" no commit in this repo can share.
     *
     * Using a real date lets a broken dirty branch pass on the day the file it names was last
     * committed, which is exactly the day someone works on this command.
     */
    private const NEVER_A_COMMIT_DATE = '2031-01-01';

    public function test_a_view_with_no_git_history_has_no_git_date(): void
    {
        $this->assertNull(
            $this->invokePrivate('newestDate', [[['file' => $this->untracked]]]),
            'the uncommittedDate() fallback only exists because this returns null'
        );
    }

    /** The control: the same method against a clean tracked file has to read the real commit date. */
    public function test_a_tracked_file_still_reads_its_date_out_of_git(): void
    {
        if (! Process::path(base_path())->run(['git', 'rev-parse', '--is-inside-work-tree'])->successful()) {
            $this->markTestSkipped('not a git checkout, so there is no history to read');
        }

        $file = 'app/Console/Commands/GenerateSitemapLastmod.php';

        $this->assertSame(
            $this->gitDate($file),
            $this->invokePrivate('newestDate', [[['file' => $file]]]),
            'a clean file must be dated from git, not from the clock'
        );
    }

    /**
     * The whole point: the run that necessarily precedes a commit predicts what the run after it
     * will produce. Without this the manifest is one commit behind for every view a commit touches,
     * and CI fails on the commit that refreshed it correctly.
     */
    public function test_a_file_with_uncommitted_changes_is_dated_today(): void
    {
        $this->travelTo(Carbon::parse(self::NEVER_A_COMMIT_DATE.' 09:58:57', 'UTC'));

        $command = $this->commandSeeing(['resources/views/marketing/about.blade.php' => [$this->hunk(12, 3)]]);

        $this->assertSame(
            self::NEVER_A_COMMIT_DATE,
            $this->invokePrivate('newestDate', [[['file' => 'resources/views/marketing/about.blade.php']]], $command)
        );
    }

    /**
     * A line range - how the 29 compare and replace pages are told apart inside one shared view -
     * counts as changed only when an edit lands in it. Dating every range in a dirty
     * MarketingController today would not converge: after the commit `git log -L` still reports the
     * older commit for the blocks that commit left alone.
     */
    public function test_a_line_range_is_dated_today_only_when_a_hunk_lands_in_it(): void
    {
        $this->travelTo(Carbon::parse(self::NEVER_A_COMMIT_DATE.' 09:58:57', 'UTC'));

        $file = 'app/Console/Commands/GenerateSitemapLastmod.php';
        $command = $this->commandSeeing([$file => [$this->hunk(400, 10)]]);

        $this->assertSame(
            self::NEVER_A_COMMIT_DATE,
            $this->invokePrivate('newestDate', [[['file' => $file, 'lines' => [405, 420]]]], $command),
            'a hunk inside the range means the block is being committed now'
        );

        // The hunk sits below this range, so nothing shifts and `git log -L1,20` is the answer -
        // which is a different date from the whole file's, since other lines have moved since.
        $this->assertSame(
            $this->gitRangeDate($file, 1, 20),
            $this->invokePrivate('newestDate', [[['file' => $file, 'lines' => [1, 20]]]], $command),
            'a hunk elsewhere in the same file must leave this range reading git'
        );
    }

    /**
     * sharedDataSource() finds the block in the file on disk, but `git log -L` reads the range out
     * of HEAD. Uncommitted edits above the block move the two apart, and an unshifted range reads a
     * neighbouring competitor's history. Harmless on a clean tree, which is why it never mattered
     * until dating a dirty tree became the normal case.
     */
    public function test_a_range_is_shifted_back_into_heads_numbering(): void
    {
        // Ten lines inserted at the top of the file, so HEAD's copy is ten lines shorter.
        $command = $this->commandSeeing(['a.php' => [
            ['oldStart' => 1, 'oldCount' => 0, 'newStart' => 1, 'newCount' => 10],
        ]]);

        $this->assertSame([90, 110], $this->invokePrivate('rangeInHead', ['a.php', [100, 120]], $command));

        // A hunk below the range moves nothing above it.
        $below = $this->commandSeeing(['a.php' => [$this->hunk(500, 4)]]);

        $this->assertSame([100, 120], $this->invokePrivate('rangeInHead', ['a.php', [100, 120]], $below));
    }

    /** A regex over another program's output, and a wrong hunk header dates a page from the wrong commit. */
    public function test_the_diff_parser_reads_every_hunk_header_shape(): void
    {
        $diff = <<<'DIFF'
        diff --git a/resources/views/marketing/about.blade.php b/resources/views/marketing/about.blade.php
        index 1111111..2222222 100644
        --- a/resources/views/marketing/about.blade.php
        +++ b/resources/views/marketing/about.blade.php
        @@ -10,3 +10,5 @@ class Whatever
        -old
        +new
        @@ -20 +22 @@
        -one
        +uno
        @@ -30,0 +33,2 @@
        +added
        +added
        diff --git a/app/Http/Controllers/MarketingController.php b/app/Http/Controllers/MarketingController.php
        index 3333333..4444444 100644
        --- a/app/Http/Controllers/MarketingController.php
        +++ b/app/Http/Controllers/MarketingController.php
        @@ -40,2 +45,0 @@
        -gone
        -gone
        DIFF;

        $changes = $this->invokePrivate('parseDiff', [$diff]);

        $this->assertSame([
            ['oldStart' => 10, 'oldCount' => 3, 'newStart' => 10, 'newCount' => 5],
            // A bare `@@ -20 +22 @@` means one line on each side.
            ['oldStart' => 20, 'oldCount' => 1, 'newStart' => 22, 'newCount' => 1],
            ['oldStart' => 30, 'oldCount' => 0, 'newStart' => 33, 'newCount' => 2],
        ], $changes['resources/views/marketing/about.blade.php']);

        $this->assertSame([
            ['oldStart' => 40, 'oldCount' => 2, 'newStart' => 45, 'newCount' => 0],
        ], $changes['app/Http/Controllers/MarketingController.php']);
    }

    /**
     * A pure deletion occupies no new-side line: `+45,0` means what vanished sat between 45 and 46.
     * Treating it as an empty range would let a block that lost its last line read as untouched.
     */
    public function test_a_pure_deletion_still_marks_the_lines_it_sat_between(): void
    {
        $command = $this->commandSeeing(['a.php' => [
            ['oldStart' => 40, 'oldCount' => 2, 'newStart' => 45, 'newCount' => 0],
        ]]);

        $this->assertTrue($this->invokePrivate('rangeIsDirty', ['a.php', [30, 45]], $command));
        $this->assertTrue($this->invokePrivate('rangeIsDirty', ['a.php', [46, 60]], $command));
        $this->assertFalse($this->invokePrivate('rangeIsDirty', ['a.php', [47, 60]], $command));
    }

    public function test_an_uncommitted_view_is_dated_today_in_the_manifests_own_format(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 11:22:33', 'UTC'));

        $date = $this->invokePrivate('uncommittedDate');

        $this->assertSame('2026-09-02', $date);

        // Days, not timestamps: a timestamp cannot be predicted by the run that precedes the commit,
        // which is what made CI's regenerate-and-diff check impossible to satisfy.
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }
}
