<?php

namespace App\Console\Commands;

use App\Models\BackupJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupBackups extends Command
{
    protected $signature = 'app:cleanup-backups';

    protected $description = 'Clean up expired backup files and stale import uploads';

    public function handle(): void
    {
        // Delete expired export files
        $expired = BackupJob::where('type', 'export')
            ->whereNotNull('file_path')
            ->where('file_expires_at', '<', now())
            ->get();

        foreach ($expired as $job) {
            if ($job->file_path && Storage::disk('local')->exists($job->file_path)) {
                Storage::disk('local')->delete($job->file_path);
            }
            $job->update(['file_path' => null]);
        }

        if ($expired->count() > 0) {
            $this->info("Cleaned up {$expired->count()} expired export(s).");
        }

        // Delete stale import uploads (pending/failed older than 1 hour)
        $stale = BackupJob::where('type', 'import')
            ->whereIn('status', ['pending', 'failed'])
            ->whereNotNull('file_path')
            ->where('created_at', '<', now()->subHour())
            ->get();

        foreach ($stale as $job) {
            if ($job->file_path && Storage::disk('local')->exists($job->file_path)) {
                Storage::disk('local')->delete($job->file_path);
            }
            $job->update(['file_path' => null]);
        }

        if ($stale->count() > 0) {
            $this->info("Cleaned up {$stale->count()} stale import upload(s).");
        }

        // Clean orphaned import uploads (uploaded but never confirmed, no BackupJob record)
        $orphaned = 0;
        $directories = Storage::disk('local')->directories('backups');
        foreach ($directories as $dir) {
            $files = Storage::disk('local')->files($dir);
            foreach ($files as $file) {
                if (str_contains($file, '/import-') &&
                    Storage::disk('local')->lastModified($file) < now()->subHour()->timestamp &&
                    ! BackupJob::where('file_path', $file)->exists()) {
                    Storage::disk('local')->delete($file);
                    $orphaned++;
                }
            }
        }

        if ($orphaned > 0) {
            $this->info("Cleaned up {$orphaned} orphaned import upload(s).");
        }
    }
}
