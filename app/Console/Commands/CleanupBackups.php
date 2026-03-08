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

        // Mark stuck processing jobs as failed (2x timeout: export 1200s, import 1800s)
        $stuckExports = BackupJob::where('type', 'export')
            ->where('status', 'processing')
            ->where('started_at', '<', now()->subSeconds(1200))
            ->get();

        foreach ($stuckExports as $job) {
            $job->update([
                'status' => 'failed',
                'error_message' => 'Processing timed out.',
                'completed_at' => now(),
            ]);
        }

        $stuckImports = BackupJob::where('type', 'import')
            ->where('status', 'processing')
            ->where('started_at', '<', now()->subSeconds(1800))
            ->get();

        foreach ($stuckImports as $job) {
            if ($job->file_path && Storage::disk('local')->exists($job->file_path)) {
                Storage::disk('local')->delete($job->file_path);
            }
            $job->update([
                'status' => 'failed',
                'error_message' => 'Processing timed out.',
                'file_path' => null,
                'completed_at' => now(),
            ]);
        }

        $stuckCount = $stuckExports->count() + $stuckImports->count();
        if ($stuckCount > 0) {
            $this->info("Marked {$stuckCount} stuck processing job(s) as failed.");
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
