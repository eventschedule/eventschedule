<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * app:cleanup-backups, which had no test at all while it swept files on a recursive listing.
 *
 * The sweep changed from directories() + files($dir) - one level deep - to allFiles('backups'),
 * which is fully recursive. With BACKUP_DISK_DRIVER unset the 'backups' and 'local' disks share
 * storage_path('app'), so that listing sees every user's EXPORT archive too, and the only thing
 * standing between the sweep and deleting them is str_contains($file, '/import-'). That filter is
 * load-bearing, so it is pinned here.
 */
class CleanupBackupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('backups');
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_the_orphan_sweep_never_touches_an_export_archive(): void
    {
        $export = 'backups/1/backup-2026-09-01-120000-'.str_repeat('a', 32).'.zip';
        $orphan = 'backups/1/import-20260901120000.zip';

        Storage::disk('local')->put($export, 'export');
        Storage::disk('local')->put($orphan, 'import');

        // Older than the one-hour cutoff.
        touch(Storage::disk('local')->path($export), now()->subDay()->timestamp);
        touch(Storage::disk('local')->path($orphan), now()->subDay()->timestamp);

        $this->artisan('app:cleanup-backups')->assertExitCode(0);

        Storage::disk('local')->assertExists($export);
        Storage::disk('local')->assertMissing($orphan);
    }

    /** A confirmed import still has its BackupJob row, so the sweep must leave it alone. */
    public function test_an_upload_still_referenced_by_a_job_is_kept(): void
    {
        $path = 'backups/1/import-20260901120000.zip';

        Storage::disk('local')->put($path, 'import');
        touch(Storage::disk('local')->path($path), now()->subDay()->timestamp);

        BackupJob::create([
            'user_id' => $this->user()->id,
            'type' => 'import',
            'status' => 'processing',
            'file_path' => $path,
        ]);

        $this->artisan('app:cleanup-backups')->assertExitCode(0);

        Storage::disk('local')->assertExists($path);
    }

    public function test_an_expired_export_is_deleted_from_the_backups_disk(): void
    {
        $path = 'backups/1/backup-2026-08-01-120000-'.str_repeat('b', 32).'.zip';

        Storage::disk('backups')->put($path, 'export');

        $job = BackupJob::create([
            'user_id' => $this->user()->id,
            'type' => 'export',
            'status' => 'completed',
            'file_path' => $path,
            'file_expires_at' => now()->subDay(),
        ]);

        $this->artisan('app:cleanup-backups')->assertExitCode(0);

        Storage::disk('backups')->assertMissing($path);
        $this->assertNull($job->fresh()->file_path);
    }

    /**
     * The 'backups' disk is configured 'throw' => true, so one S3 hiccup used to abort the whole
     * command - including the stuck-job sweep below it, which is the ONLY thing that unwedges a
     * user stuck in 'processing' and therefore unable to start another export.
     */
    public function test_a_failing_delete_does_not_abort_the_stuck_job_sweep(): void
    {
        $expired = BackupJob::create([
            'user_id' => $this->user()->id,
            'type' => 'export',
            'status' => 'completed',
            'file_path' => 'backups/1/gone.zip',
            'file_expires_at' => now()->subDay(),
        ]);

        $wedged = BackupJob::create([
            'user_id' => $this->user()->id,
            'type' => 'export',
            'status' => 'processing',
            'file_path' => null,
            // started_at, not created_at: that is what the stuck-job sweep filters on.
            'started_at' => now()->subDay(),
        ]);

        // Storage::set() swaps ONE disk, rather than Storage::shouldReceive() replacing the whole
        // facade with a mock - which would turn any other Storage:: call in the command into an
        // unrelated BadMethodCallException and make this test pass or fail for the wrong reason.
        $broken = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $broken->shouldReceive('exists')->andThrow(new \RuntimeException('S3 is having a day'));
        Storage::set('backups', $broken);

        $this->artisan('app:cleanup-backups')->assertExitCode(0);

        $this->assertSame('failed', $wedged->fresh()->status,
            'the stuck-job sweep must still run when an earlier delete throws');
        $this->assertNotNull($expired->fresh()->file_path,
            'a file we could not delete keeps its path so the next run retries it');
    }
}
