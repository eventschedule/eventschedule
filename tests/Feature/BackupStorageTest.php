<?php

namespace Tests\Feature;

use App\Jobs\ProcessBackupExport;
use App\Models\BackupJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Where backup archives live, which is not a detail: the two directions have opposite constraints.
 *
 * An EXPORT is written by a queued job and read back later by a download request. Once the queue
 * is drained by a separate worker container those are different machines, and storage_path('app')
 * is per-container and wiped on every deploy - so exports have to go to shared storage.
 *
 * An IMPORT is the mirror: the archive is uploaded by a web request and opened by ZipArchive
 * through Storage::path(), which returns a bucket key rather than a filesystem path on any remote
 * driver. So imports have to stay on the container that received the upload, which is why they run
 * inline regardless of the queue driver.
 */
class BackupStorageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The images bucket is fronted by a public CDN and ImageUtils::getUrl() addresses any object in
     * it by concatenating the raw storage key onto the CDN hostname. A backup archive holds every
     * sale, attendee email and phone number for the schedules inside it, at a path that is a small
     * integer plus a timestamp - so putting the two in one bucket puts tenant exports one ACL slip
     * from public, with CDN caching making the mistake hard to take back.
     */
    public function test_the_backups_disk_is_private_and_never_the_public_images_bucket(): void
    {
        $backups = config('filesystems.disks.backups');

        $this->assertSame('private', $backups['visibility'] ?? null,
            'backup archives must never be world-readable');

        $this->assertTrue((bool) ($backups['throw'] ?? false),
            'without throw, a failed put() returns false and ProcessBackupExport still marks the job completed');

        $images = config('filesystems.disks.do_spaces.bucket');

        if ($backups['bucket'] !== null && $images !== null) {
            $this->assertNotSame($images, $backups['bucket'],
                'the backups bucket must not be the CDN-fronted images bucket');
        }

        // The comparison above only bites on an install that has both buckets configured, and CI
        // has neither - so assert the config EXPRESSION too. BACKUP_SPACES_BUCKET must have no
        // fallback: a misconfigured install has to fail loudly rather than quietly write tenant
        // exports into the public bucket, and a default is exactly how that would happen.
        $config = file_get_contents(config_path('filesystems.php'));
        $backupsBlock = substr($config, strpos($config, "'backups' => ["));

        $this->assertStringContainsString("'bucket' => env('BACKUP_SPACES_BUCKET'),", $backupsBlock,
            'BACKUP_SPACES_BUCKET must not fall back to another bucket');
        $this->assertStringNotContainsString("env('BACKUP_SPACES_BUCKET', ", $backupsBlock,
            'BACKUP_SPACES_BUCKET must not fall back to another bucket');
    }

    /**
     * On a remote driver, `root` is the object KEY PREFIX, not a filesystem path.
     *
     * Leaving storage_path() in turns every key into
     * /var/www/.../storage/app/backups/{id}/... The app never notices, because reads and writes
     * share the prefixer - but a bucket lifecycle rule or an IAM policy scoped to "backups/*",
     * which is what an operator sets up as the backstop for the 7-day retention, silently matches
     * nothing. It also leaks the deploy path into every object key.
     *
     * Re-evaluates the config file with the env var set, rather than overriding `driver` on the
     * resolved array: `root` is decided when the file is loaded, so a runtime override of the
     * driver alone would leave the old root in place and the assertion would prove nothing.
     * Storage::fake() is no use here either - a faked disk ignores `root` entirely, which is
     * exactly why the rest of this file could not catch the bug.
     */
    public function test_the_backups_disk_does_not_prefix_s3_keys_with_a_filesystem_path(): void
    {
        $previous = $_SERVER['BACKUP_DISK_DRIVER'] ?? null;
        $_SERVER['BACKUP_DISK_DRIVER'] = 's3';

        try {
            $config = require config_path('filesystems.php');
        } finally {
            if ($previous === null) {
                unset($_SERVER['BACKUP_DISK_DRIVER']);
            } else {
                $_SERVER['BACKUP_DISK_DRIVER'] = $previous;
            }
        }

        $this->assertSame('s3', $config['disks']['backups']['driver'], 'env override did not take effect');
        $this->assertSame('', $config['disks']['backups']['root'],
            'the backups disk must not prefix S3 object keys with a filesystem path');
    }

    /**
     * .env.example and the SaaS setup doc both ship the BACKUP_SPACES_* block with empty values for
     * the operator to fill in. env()'s second argument only fires on a MISSING key, so an operator
     * who uncomments the block and fills in only some of it gets '' for the rest and silently loses
     * the DO_SPACES_* fallback the config comment advertises. `?:` is what makes the fallback real.
     *
     * BACKUP_SPACES_BUCKET is deliberately exempt and must stay exempt - see the test above.
     */
    public function test_blank_backup_credentials_still_fall_back_to_the_spaces_defaults(): void
    {
        $overrides = [
            'BACKUP_DISK_DRIVER' => 's3',
            'BACKUP_SPACES_KEY' => '',
            'BACKUP_SPACES_SECRET' => '',
            'BACKUP_SPACES_REGION' => '',
            'BACKUP_SPACES_ENDPOINT' => '',
            'BACKUP_SPACES_BUCKET' => '',
            'DO_SPACES_KEY' => 'images-key',
            'DO_SPACES_SECRET' => 'images-secret',
            'DO_SPACES_REGION' => 'nyc3',
            'DO_SPACES_ENDPOINT' => 'https://nyc3.digitaloceanspaces.com',
        ];

        $previous = [];

        foreach ($overrides as $key => $value) {
            $previous[$key] = $_SERVER[$key] ?? null;
            $_SERVER[$key] = $value;
        }

        try {
            $backups = (require config_path('filesystems.php'))['disks']['backups'];
        } finally {
            foreach ($previous as $key => $value) {
                if ($value === null) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $value;
                }
            }
        }

        $this->assertSame('images-key', $backups['key']);
        $this->assertSame('images-secret', $backups['secret']);
        $this->assertSame('nyc3', $backups['region']);
        $this->assertSame('https://nyc3.digitaloceanspaces.com', $backups['endpoint']);

        // The bucket must NOT fall back. A blank value has to stay blank and fail, rather than
        // reaching for the CDN-fronted images bucket and publishing every tenant export in it.
        $this->assertEmpty($backups['bucket'], 'the backups bucket must never inherit another bucket');
    }

    /** The local default still roots at storage/app, so selfhost paths keep resolving. */
    public function test_the_backups_disk_still_roots_at_storage_app_on_the_local_driver(): void
    {
        $this->assertSame('local', config('filesystems.disks.backups.driver'));
        $this->assertSame(storage_path('app'), config('filesystems.disks.backups.root'));
    }

    public function test_an_export_is_written_privately_to_the_backups_disk(): void
    {
        Storage::fake('backups');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $job = BackupJob::create([
            'user_id' => $owner->id,
            'type' => 'export',
            'status' => 'pending',
            'role_ids' => [$role->id],
            'include_images' => false,
        ]);

        (new ProcessBackupExport($job->id))->handle();

        $job->refresh();

        $this->assertSame('completed', $job->status, $job->error_message ?? '');
        $this->assertNotNull($job->file_path);
        Storage::disk('backups')->assertExists($job->file_path);
    }

    /**
     * The export key must carry a high-entropy component.
     *
     * Without it the path is backups/{user_id}/backup-{Y-m-d-His}.zip - a small integer plus a
     * second-resolution timestamp the owner can narrow from backup_jobs.created_at. That is
     * enumerable, which turns any future loosening of the bucket ACL into "every customer's export
     * is downloadable" rather than "one customer's is".
     */
    /**
     * A failed export must always land in 'failed', never stay 'processing'.
     *
     * The backups disk is 'throw' => true, so a cleanup call inside the catch can itself throw -
     * and the likeliest reason to be in that catch is a disk that is not answering. If that escapes
     * before the status update, the row stays 'processing' and BackupController then refuses every
     * future export for that user, with nothing in the UI to clear it.
     */
    public function test_a_failed_export_is_marked_failed_even_when_cleanup_throws(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // A disk whose every call raises, standing in for object storage that is down.
        Storage::shouldReceive('disk')->with('backups')->andThrow(new \RuntimeException('storage is down'));
        Storage::shouldReceive('disk')->andReturnUsing(fn ($name) => Storage::createLocalDriver(['root' => storage_path('app')]));

        $job = BackupJob::create([
            'user_id' => $owner->id,
            'type' => 'export',
            'status' => 'pending',
            'role_ids' => [$role->id],
            'include_images' => false,
        ]);

        (new ProcessBackupExport($job->id))->handle();

        $this->assertSame('failed', $job->refresh()->status,
            'a stuck "processing" row locks the user out of exporting for good');
    }

    public function test_an_export_key_is_not_guessable(): void
    {
        Storage::fake('backups');

        // Frozen, so both exports produce the SAME {Y-m-d-His} stamp. Without this the two runs
        // land a second or more apart and the collision assertion below passes on the timestamp
        // alone - it was vacuous until a constant suffix was A/B tested through it.
        $this->travelTo(now());

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $paths = [];

        foreach (range(1, 2) as $ignored) {
            $job = BackupJob::create([
                'user_id' => $owner->id,
                'type' => 'export',
                'status' => 'pending',
                'role_ids' => [$role->id],
                'include_images' => false,
            ]);

            (new ProcessBackupExport($job->id))->handle();

            $paths[] = $job->refresh()->file_path;
        }

        $this->assertMatchesRegularExpression(
            '#^backups/'.$owner->id.'/backup-\d{4}-\d{2}-\d{2}-\d{6}-[a-z0-9]{32}\.zip$#',
            $paths[0]
        );

        // The assertion with teeth: a timestamp-only name satisfies the pattern above but collides
        // for two exports in the same second, so only real randomness passes this.
        $this->assertNotSame($paths[0], $paths[1],
            'two exports produced the same key - the random component is missing or not random');
    }

    public function test_the_download_serves_an_export_from_the_backups_disk(): void
    {
        Storage::fake('backups');

        $owner = $this->createOwner();
        $job = BackupJob::create([
            'user_id' => $owner->id,
            'type' => 'export',
            'status' => 'completed',
            'file_path' => 'backups/'.$owner->id.'/backup-test.zip',
            'file_expires_at' => now()->addDays(7),
        ]);

        Storage::disk('backups')->put($job->file_path, 'zip-bytes');

        $this->actingAs($owner)
            ->get(URL::temporarySignedRoute('backup.download', now()->addHour(), ['backupJob' => $job->id]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=backup-'.now()->format('Y-m-d').'.zip');
    }

    /**
     * Rows written before the backups disk existed still point at storage_path('app'). They expire
     * seven days after this ships; until then the download must find them rather than 404.
     */
    public function test_the_download_still_finds_an_export_left_on_the_local_disk(): void
    {
        Storage::fake('backups');
        Storage::fake('local');

        $owner = $this->createOwner();
        $job = BackupJob::create([
            'user_id' => $owner->id,
            'type' => 'export',
            'status' => 'completed',
            'file_path' => 'backups/'.$owner->id.'/legacy.zip',
            'file_expires_at' => now()->addDays(7),
        ]);

        Storage::disk('local')->put($job->file_path, 'zip-bytes');

        $this->actingAs($owner)
            ->get(URL::temporarySignedRoute('backup.download', now()->addHour(), ['backupJob' => $job->id]))
            ->assertOk();
    }

    /**
     * The import must never reach the queue, whatever QUEUE_CONNECTION says. A worker on another
     * container cannot see the uploaded archive, and BackupService opens it via Storage::path(),
     * which returns a bucket key rather than a filesystem path on any remote driver.
     *
     * Deliberately NOT Queue::fake(): the fake intercepts dispatchSync() as well as dispatch(), so
     * both would record a push and the assertion would pass no matter which one the controller
     * used. Pointing the real database connection at the real jobs table is what distinguishes
     * them - a genuine dispatch() inserts a row, dispatchSync() runs the job instead.
     */
    public function test_an_import_runs_inline_even_on_a_queued_connection(): void
    {
        Storage::fake('local');
        config(['queue.default' => 'database']);

        $owner = $this->createOwner();
        $path = 'backups/'.$owner->id.'/import-'.now()->format('YmdHis').'.zip';
        Storage::disk('local')->put($path, 'not-a-real-zip');

        $this->actingAs($owner)
            ->post(route('backup.confirm'), ['file_path' => $path, 'selected_indices' => [0]])
            ->assertOk();

        $this->assertSame(0, DB::table('jobs')->count(),
            'the import was queued - a worker on another container cannot open the uploaded archive');

        // It ran here and now, so it has already reached a terminal state rather than sitting
        // pending. The archive is deliberately not a real zip, so that state is "failed".
        $this->assertSame('failed', BackupJob::where('user_id', $owner->id)
            ->where('type', 'import')->latest('id')->value('status'));
    }
}
