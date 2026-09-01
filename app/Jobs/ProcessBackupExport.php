<?php

namespace App\Jobs;

use App\Mail\BackupExportComplete;
use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use App\Services\OneSignalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProcessBackupExport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    protected int $backupJobId;

    public function __construct(int $backupJobId)
    {
        $this->backupJobId = $backupJobId;
    }

    public function handle(): void
    {
        $job = BackupJob::find($this->backupJobId);
        if (! $job || $job->status !== 'pending') {
            return;
        }

        $job->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $tempZip = null;

        try {
            $roleIds = $job->role_ids ?? [];
            $roles = Role::whereIn('id', $roleIds)->where('is_deleted', false)->get();

            if ($roles->isEmpty()) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'No valid schedules found.',
                    'completed_at' => now(),
                ]);

                return;
            }

            $service = new BackupService;
            $result = $service->exportSchedules($roles->all(), $job->include_images, $job);

            // Create ZIP
            $jsonContent = json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $timestamp = now()->format('Y-m-d-His');

            // The random component is the backstop, not decoration. Without it the key is a small
            // integer plus a second-resolution timestamp bounded by a created_at the owner can
            // already see - enumerable by anyone who ever gets read access to the bucket. The
            // bucket is private and downloads are served through a signed route, so this only
            // matters the day one of those is misconfigured; that is exactly when it matters.
            // strtolower(Str::random(32)) matches how the rest of the app names unguessable
            // uploads (ImageUtils::saveImageData, the sponsor/flyer/addon paths in EventRepo).
            $zipFilename = "backups/{$job->user_id}/backup-{$timestamp}-".strtolower(Str::random(32)).'.zip';

            $tempZip = tempnam(sys_get_temp_dir(), 'backup');
            $zip = new \ZipArchive;
            $zip->open($tempZip, \ZipArchive::CREATE);
            $zip->addFromString('backup.json', $jsonContent);

            foreach ($result['images'] as $zipEntryPath => $storagePath) {
                $contents = Storage::get($storagePath);
                if ($contents !== null) {
                    $zip->addFromString($zipEntryPath, $contents);
                }
            }

            $zip->close();

            // The 'backups' disk, not 'local'. On hosted this is shared object storage, because
            // the container that builds the export is not the one that later serves the download:
            // storage_path('app') is per-container and wiped on every deploy, so a file written
            // here would be gone long before file_expires_at. Streamed rather than
            // file_get_contents()'d because nothing caps the size of an export.
            $handle = fopen($tempZip, 'r');

            // Guarded rather than passed straight in: put() treats a non-resource as string
            // contents, so a failed fopen would silently store an EMPTY archive and the code
            // below would still mark the job completed and mail the user a link to it.
            if ($handle === false) {
                throw new \RuntimeException('Could not open the generated backup archive for upload.');
            }

            try {
                Storage::disk('backups')->put($zipFilename, $handle, 'private');
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }

            $expiresAt = now()->addDays(7);
            $job->update([
                'status' => 'completed',
                'file_path' => $zipFilename,
                'file_expires_at' => $expiresAt,
                'completed_at' => now(),
            ]);

            // Send email
            $previousRootUrl = config('app.url');
            URL::forceRootUrl(rtrim(app_url('/'), '/'));
            $downloadUrl = URL::temporarySignedRoute(
                'backup.download',
                $expiresAt,
                ['backupJob' => $job->id]
            );
            URL::forceRootUrl($previousRootUrl);

            $scheduleNames = $roles->pluck('name')->toArray();

            // Notifying is NOT part of building the export, so it gets its own catch.
            //
            // The job is already marked completed with its file_path above. Letting a transient
            // SMTP failure fall through to the catch below would flip a genuinely finished export
            // back to 'failed' AND delete the archive - losing the user's export because the email
            // about it did not send, and leaving a row whose file_path points at nothing. The
            // download route is signed but reachable from the backups list either way, so a missing
            // notification is a nuisance; a deleted archive is data loss.
            try {
                Mail::to($job->user->email)->send(
                    new BackupExportComplete($downloadUrl, $scheduleNames, $expiresAt)
                );

                OneSignalService::pushToUser($job->user, [
                    'title_key' => 'messages.push_backup_export_title',
                    'body_key' => 'messages.push_backup_export_body',
                    'url' => $downloadUrl,
                ], null);
            } catch (\Throwable $notificationFailure) {
                report($notificationFailure);
            }

        } catch (\Exception $e) {
            report($e);

            // Mark the job failed FIRST. The backups disk is deliberately 'throw' => true, and the
            // likeliest reason to be in this catch is that the disk write failed - so a cleanup
            // attempt here can throw straight back out and skip the status update, leaving the row
            // stuck in 'processing'. BackupController refuses a new export while one is pending or
            // processing, so that would lock the user out of exporting entirely, with nothing in
            // the UI to clear it.
            $job->update([
                'status' => 'failed',
                'error_message' => 'Export failed. Please try again.',
                'completed_at' => now(),
            ]);

            // Best-effort cleanup of a partial upload; never allowed to mask the failure above.
            try {
                if (isset($zipFilename) && Storage::disk('backups')->exists($zipFilename)) {
                    Storage::disk('backups')->delete($zipFilename);
                }
            } catch (\Throwable $cleanupFailure) {
                report($cleanupFailure);
            }
        } finally {
            if ($tempZip && file_exists($tempZip)) {
                unlink($tempZip);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        report($e);

        $job = BackupJob::find($this->backupJobId);
        if ($job && $job->status !== 'completed') {
            $job->update([
                'status' => 'failed',
                'error_message' => 'Export failed. Please try again.',
                'completed_at' => now(),
            ]);
        }
    }
}
