<?php

namespace Tests\Unit;

use App\Console\Commands\GenerateSitemapLastmod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use ReflectionMethod;
use Tests\TestCase;

/**
 * `sitemap:lastmod` used to SKIP a resolved view that git had never seen, which meant a marketing
 * page could not be green in the commit that added it: the view exists, the sitemap lists it,
 * `git log` returns nothing for it, so it landed in the undated list SitemapCoverageTest asserts.
 *
 * The command is not run end to end here - it resolves ~150 routes and shells out to git for each
 * one - so the two halves of that decision are exercised directly instead: newestDate() still
 * returns null for a file with no history (which is what makes the fallback fire) and
 * uncommittedDate() supplies a date in the same shape the git-derived ones use.
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

    private function invokePrivate(string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionMethod(GenerateSitemapLastmod::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(new GenerateSitemapLastmod, $arguments);
    }

    public function test_a_view_with_no_git_history_has_no_git_date(): void
    {
        $this->assertNull(
            $this->invokePrivate('newestDate', [[['file' => $this->untracked]]]),
            'the fallback only exists because this returns null'
        );
    }

    /** The control: the same method against a tracked file has to produce a real date, or the test above proves nothing. */
    public function test_a_tracked_file_still_reads_its_date_out_of_git(): void
    {
        if (! Process::path(base_path())->run(['git', 'rev-parse', '--is-inside-work-tree'])->successful()) {
            $this->markTestSkipped('not a git checkout, so there is no history to read');
        }

        $date = $this->invokePrivate('newestDate', [[['file' => 'app/Console/Commands/GenerateSitemapLastmod.php']]]);

        $this->assertNotNull($date, 'a committed file reported no date, so this suite cannot tell the two cases apart');
        $this->assertNotNull(Carbon::parse($date));
    }

    public function test_an_uncommitted_view_is_dated_now_in_the_manifests_own_format(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 11:22:33', 'UTC'));

        $date = $this->invokePrivate('uncommittedDate');

        $this->assertSame('2026-09-02T11:22:33+00:00', $date);

        // The manifest is read back by SitemapController, which only accepts this exact shape.
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $date);
    }
}
