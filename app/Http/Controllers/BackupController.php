<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBackupExport;
use App\Jobs\ProcessBackupImport;
use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function export(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'integer',
            'include_images' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $roleIds = $request->input('role_ids');

        // Verify user is editor of each selected role
        $roles = Role::whereIn('id', $roleIds)->where('is_deleted', false)->get();
        foreach ($roles as $role) {
            if (! $user->isEditor($role->subdomain)) {
                return response()->json(['error' => __('messages.unauthorized')], 403);
            }
        }

        if ($roles->isEmpty()) {
            return response()->json(['error' => __('messages.backup_no_schedules_selected')], 422);
        }

        // Check no export already processing
        $existing = BackupJob::where('user_id', $user->id)
            ->where('type', 'export')
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($existing) {
            return response()->json(['error' => __('messages.backup_already_processing')], 422);
        }

        $job = BackupJob::create([
            'user_id' => $user->id,
            'type' => 'export',
            'status' => 'pending',
            'role_ids' => $roles->pluck('id')->toArray(),
            'include_images' => (bool) $request->input('include_images', false),
        ]);

        if (config('queue.default') !== 'sync') {
            ProcessBackupExport::dispatch($job->id);
        } else {
            set_time_limit(0);
            ProcessBackupExport::dispatchSync($job->id);
        }

        return response()->json(['job_id' => $job->id]);
    }

    public function upload(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $request->validate([
            'file' => 'required|file|max:102400|mimes:zip',
        ]);

        $user = $request->user();

        // Check no import already processing
        $existing = BackupJob::where('user_id', $user->id)
            ->where('type', 'import')
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($existing) {
            return response()->json(['error' => __('messages.backup_already_processing')], 422);
        }

        $file = $request->file('file');
        $zipPath = $file->getRealPath();

        // ZIP bomb and path traversal protection
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        $totalUncompressed = 0;
        $hasBackupJson = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = $stat['name'];

            // Path traversal protection
            if (str_contains($entryName, '..') || str_starts_with($entryName, '/') || str_starts_with($entryName, '\\') || str_contains($entryName, "\0")) {
                $zip->close();

                return response()->json(['error' => __('messages.backup_invalid_file')], 422);
            }

            // Only allow backup.json and images/*
            if ($entryName !== 'backup.json' && ! str_starts_with($entryName, 'images/')) {
                $zip->close();

                return response()->json(['error' => __('messages.backup_invalid_file')], 422);
            }

            if ($entryName === 'backup.json') {
                $hasBackupJson = true;
            }

            $totalUncompressed += $stat['size'];

            // Check compression ratio per entry
            if ($stat['comp_size'] > 0 && $stat['size'] / $stat['comp_size'] > 100) {
                $zip->close();

                return response()->json(['error' => __('messages.backup_invalid_file')], 422);
            }
        }

        // Max 500MB uncompressed
        if ($totalUncompressed > 500 * 1024 * 1024) {
            $zip->close();

            return response()->json(['error' => __('messages.backup_file_too_large')], 422);
        }

        if (! $hasBackupJson) {
            $zip->close();

            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        $jsonContent = $zip->getFromName('backup.json');
        $zip->close();

        if ($jsonContent === false) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        $data = json_decode($jsonContent, true, 512);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        $service = new BackupService;
        $errors = $service->validateBackupJson($data);
        if (! empty($errors)) {
            return response()->json(['error' => $errors[0]], 422);
        }

        // Store uploaded file
        $storagePath = 'backups/'.$user->id.'/import-'.now()->format('YmdHis').'.zip';
        Storage::disk('local')->put($storagePath, file_get_contents($zipPath));

        $preview = $service->getImportPreview($data);

        return response()->json([
            'preview' => $preview,
            'file_path' => $storagePath,
        ]);
    }

    public function confirm(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $request->validate([
            'file_path' => 'required|string',
            'selected_indices' => 'required|array|min:1',
            'selected_indices.*' => 'integer|min:0',
        ]);

        $user = $request->user();
        $filePath = $request->input('file_path');

        // Path traversal protection
        if (str_contains($filePath, '..') || str_contains($filePath, "\0")) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        // Validate file exists and belongs to user
        if (! str_starts_with($filePath, 'backups/'.$user->id.'/') || ! Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        // Check no import already processing
        $existing = BackupJob::where('user_id', $user->id)
            ->where('type', 'import')
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($existing) {
            return response()->json(['error' => __('messages.backup_already_processing')], 422);
        }

        $selectedIndices = $request->input('selected_indices');

        $job = BackupJob::create([
            'user_id' => $user->id,
            'type' => 'import',
            'status' => 'pending',
            'file_path' => $filePath,
        ]);

        if (config('queue.default') !== 'sync') {
            ProcessBackupImport::dispatch($job->id, $selectedIndices);
        } else {
            set_time_limit(0);
            ProcessBackupImport::dispatchSync($job->id, $selectedIndices);
        }

        return response()->json([
            'job_id' => $job->id,
            'message' => __('messages.backup_import_started'),
        ]);
    }

    public function cancelUpload(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        $user = $request->user();
        $filePath = $request->input('file_path');

        // Path traversal protection
        if (str_contains($filePath, '..') || str_contains($filePath, "\0")) {
            return response()->json(['error' => __('messages.backup_invalid_file')], 422);
        }

        // Validate file belongs to user
        if (! str_starts_with($filePath, 'backups/'.$user->id.'/')) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        return response()->json(['message' => 'ok']);
    }

    public function cancel(Request $request, BackupJob $backupJob)
    {
        if ($backupJob->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! in_array($backupJob->status, ['pending', 'processing'])) {
            return response()->json(['error' => __('messages.backup_not_cancellable')], 422);
        }

        $backupJob->update([
            'status' => 'failed',
            'error_message' => __('messages.cancelled'),
        ]);

        // Clean up uploaded file for import jobs
        if ($backupJob->isImport() && $backupJob->file_path && Storage::disk('local')->exists($backupJob->file_path)) {
            Storage::disk('local')->delete($backupJob->file_path);
        }

        return response()->json(['message' => 'ok']);
    }

    public function download(Request $request, BackupJob $backupJob)
    {
        // Verify ownership
        if ($backupJob->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $backupJob->hasDownload()) {
            abort(404);
        }

        $filePath = $backupJob->file_path;
        if (! Storage::disk('local')->exists($filePath)) {
            abort(404);
        }

        return Storage::disk('local')->download($filePath, 'backup-'.now()->format('Y-m-d').'.zip', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function status(Request $request, BackupJob $backupJob)
    {
        if ($backupJob->user_id !== $request->user()->id) {
            abort(403);
        }

        $response = [
            'id' => $backupJob->id,
            'type' => $backupJob->type,
            'status' => $backupJob->status,
            'progress' => $backupJob->progress,
        ];

        if ($backupJob->isCompleted() && $backupJob->isExport() && $backupJob->hasDownload()) {
            $response['download_url'] = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'backup.download',
                $backupJob->file_expires_at,
                ['backupJob' => $backupJob->id]
            );
            $response['expires_at'] = $backupJob->file_expires_at->toIso8601String();
        }

        if ($backupJob->isCompleted() && $backupJob->isImport()) {
            $response['report'] = $backupJob->report;
        }

        if ($backupJob->isFailed()) {
            $response['error'] = $backupJob->error_message;
        }

        return response()->json($response);
    }
}
