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
        // Delete expired export files.
        //
        // Exports live on the 'backups' disk (shared object storage on hosted, because the
        // container that writes an export is not the one that serves the download); imports stay
        // on 'local', because the import job runs inline on the container that received the
        // upload. Every disk choice below follows from that split. The 'local' arm here is for
        // export rows written before the 'backups' disk existed and can go once they have aged out.
        $expired = BackupJob::where('type', 'export')
            ->whereNotNull('file_path')
            ->where('file_expires_at', '<', now())
            ->get();

        $failedDeletes = 0;

        foreach ($expired as $job) {
            // Per-row try/catch, because the 'backups' disk is configured 'throw' => true. One S3
            // hiccup here would otherwise abort the whole command - including the stuck-job sweep
            // at the bottom, which is the ONLY thing that unwedges a user whose export is stuck in
            // 'processing' and who therefore cannot start another one. A file we failed to delete
            // is retried tomorrow; a user locked out of exporting is not self-healing.
            try {
                foreach (['backups', 'local'] as $disk) {
                    if ($job->file_path && Storage::disk($disk)->exists($job->file_path)) {
                        Storage::disk($disk)->delete($job->file_path);
                    }
                }
            } catch (\Throwable $e) {
                report($e);
                $failedDeletes++;

                // file_path is deliberately left set, so the next run tries this row again rather
                // than orphaning the object with nothing pointing at it.
                continue;
            }

            $job->update(['file_path' => null]);
        }

        if ($failedDeletes > 0) {
            $this->warn("Could not delete {$failedDeletes} expired export file(s); will retry next run.");
        }

        $cleaned = $expired->count() - $failedDeletes;

        if ($cleaned > 0) {
            $this->info("Cleaned up {$cleaned} expired export(s).");
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
        // One recursive listing rather than directories() + a files() call per user: upload() stores
        // the file and hands file_path straight back to the browser WITHOUT creating a BackupJob
        // row, so this sweep is the only thing that ever collects an abandoned upload.
        $orphaned = 0;
        foreach (Storage::disk('local')->allFiles('backups') as $file) {
            if (str_contains($file, '/import-') &&
                Storage::disk('local')->lastModified($file) < now()->subHour()->timestamp &&
                ! BackupJob::where('file_path', $file)->exists()) {
                Storage::disk('local')->delete($file);
                $orphaned++;
            }
        }

        if ($orphaned > 0) {
            $this->info("Cleaned up {$orphaned} orphaned import upload(s).");
        }
    }
}
