<?php

namespace App\Jobs;

use App\Mail\BackupImportComplete;
use App\Models\BackupJob;
use App\Services\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessBackupImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    protected int $backupJobId;

    protected array $selectedIndices;

    public function __construct(int $backupJobId, array $selectedIndices)
    {
        $this->backupJobId = $backupJobId;
        $this->selectedIndices = $selectedIndices;
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

        try {
            $filePath = $job->file_path;
            if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Backup file not found.',
                    'completed_at' => now(),
                ]);

                return;
            }

            // Extract JSON from ZIP
            $zipFullPath = Storage::disk('local')->path($filePath);
            $zip = new \ZipArchive;
            if ($zip->open($zipFullPath) !== true) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Failed to open backup file.',
                    'completed_at' => now(),
                ]);

                return;
            }

            $jsonContent = $zip->getFromName('backup.json');
            $zip->close();

            if ($jsonContent === false) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Invalid backup file: missing data.',
                    'completed_at' => now(),
                ]);

                return;
            }

            $data = json_decode($jsonContent, true, 10);
            if (! $data) {
                $job->update([
                    'status' => 'failed',
                    'error_message' => 'Invalid backup file: corrupted data.',
                    'completed_at' => now(),
                ]);

                return;
            }

            $service = new BackupService;
            $report = $service->importSchedules($data, $this->selectedIndices, $job->user_id, $job);

            $job->update([
                'status' => 'completed',
                'report' => $report,
                'completed_at' => now(),
            ]);

            // Clean up uploaded file
            Storage::disk('local')->delete($filePath);
            $job->update(['file_path' => null]);

            // Send email report
            Mail::to($job->user->email)->send(
                new BackupImportComplete($report)
            );

        } catch (\Exception $e) {
            report($e);
            $job->update([
                'status' => 'failed',
                'error_message' => 'Import failed. Please try again.',
                'completed_at' => now(),
            ]);

            if ($job->file_path) {
                Storage::disk('local')->delete($job->file_path);
                $job->update(['file_path' => null]);
            }
        }
    }
}
