<?php

namespace App\Jobs;

use App\Mail\PersonalDataExportReady;
use App\Models\BackupJob;
use App\Services\PersonalDataExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Writes the "Download my data" file (PersonalDataExportService) to the private backups disk and
 * emails the account a signed link to it, valid for seven days. Tracked as a backup_jobs row of
 * type 'personal', so app:cleanup-backups deletes the file when the link expires.
 */
class ExportPersonalData implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public int $backupJobId) {}

    public function handle(PersonalDataExportService $service): void
    {
        $job = BackupJob::find($this->backupJobId);

        if (! $job || ! $job->user || $job->status !== 'pending') {
            return;
        }

        $job->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $filename = 'personal-data-'.Str::uuid().'.json';
            $json = json_encode($service->build($job->user), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

            Storage::disk('backups')->put($filename, (string) $json, 'private');

            $expiresAt = now()->addDays(7);
            $job->update([
                'status' => 'completed',
                'file_path' => $filename,
                'file_expires_at' => $expiresAt,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $job->update(['status' => 'failed', 'error_message' => 'The export could not be written.']);
            report($e);

            return;
        }

        // The link goes to the app host whatever host queued this, as ProcessBackupExport does.
        $previousRootUrl = config('app.url');
        URL::forceRootUrl(rtrim(app_url('/'), '/'));

        try {
            $downloadUrl = URL::temporarySignedRoute('profile.data_export.download', $expiresAt, ['backupJob' => $job->id]);
        } finally {
            URL::forceRootUrl($previousRootUrl);
        }

        try {
            Mail::to($job->user->email)->send(new PersonalDataExportReady($downloadUrl, $expiresAt));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
