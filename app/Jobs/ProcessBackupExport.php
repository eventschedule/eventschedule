<?php

namespace App\Jobs;

use App\Mail\BackupExportComplete;
use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
            $jsonContent = json_encode($result['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $timestamp = now()->format('Y-m-d-His');
            $zipFilename = "backups/{$job->user_id}/backup-{$timestamp}.zip";

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

            // Store on local disk (not public)
            Storage::disk('local')->put($zipFilename, file_get_contents($tempZip));

            $expiresAt = now()->addDays(7);
            $job->update([
                'status' => 'completed',
                'file_path' => $zipFilename,
                'file_expires_at' => $expiresAt,
                'completed_at' => now(),
            ]);

            // Send email
            $downloadUrl = URL::temporarySignedRoute(
                'backup.download',
                $expiresAt,
                ['backupJob' => $job->id]
            );

            $scheduleNames = $roles->pluck('name')->toArray();

            Mail::to($job->user->email)->send(
                new BackupExportComplete($downloadUrl, $scheduleNames, $expiresAt)
            );

        } catch (\Exception $e) {
            report($e);
            $job->update([
                'status' => 'failed',
                'error_message' => 'Export failed. Please try again.',
                'completed_at' => now(),
            ]);
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
