<?php

namespace App\Http\Controllers;

use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
class AppController extends Controller
{
    public function update(Request $request, UpdaterManager $updater)
    {
        if (config('app.hosted')) {
            return redirect()->back()->with('error', 'Not authorized');
        }

        $request->validate([
            'package' => ['nullable', 'file', 'mimes:zip'],
        ]);

        if ($request->hasFile('package')) {
            try {
                $this->updateFromUploadedArchive($request->file('package'));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            return redirect()->back()->with('message', __('messages.app_updated'));
        }

        try {
            if ($updater->source()->isNewVersionAvailable()) {
                $versionAvailable = $updater->source()->getVersionAvailable();

                $release = $updater->source()->fetch($versionAvailable);
                
                $updater->source()->update($release);   
                
                Artisan::call('migrate', ['--force' => true]);
            } else {
                return redirect()->back()->with('error', __('messages.no_new_version_available'));
            }                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('message', __('messages.app_updated'));
    }

    public function setup()
    {
        return view('setup');
    }

    public function testDatabase(Request $request)
    {
        $host = $request->input('host');
        $port = $request->input('port');
        $database = $request->input('database');
        $username = $request->input('username');
        $password = $request->input('password');

        try {
            $connection = @mysqli_connect($host, $username, $password, $database, (int)$port);
            if (!$connection) {
                throw new \Exception(mysqli_connect_error());
            }
            mysqli_close($connection);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    public function translateData()
    {
        $requestSecret = request()->get('secret');
        $serverSecret = config('app.cron_secret');
        
        if (!$serverSecret || !$requestSecret || !hash_equals($serverSecret, $requestSecret)) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        \Artisan::call('app:import-curator-events');
        \Artisan::call('app:translate');

        return response()->json(['success' => true]);
    }

    private function updateFromUploadedArchive(UploadedFile $archive): void
    {
        Storage::disk('local')->makeDirectory('manual-updates');

        $filename = 'manual-' . Str::uuid() . '.zip';
        $relativeZipPath = $archive->storeAs('manual-updates', $filename);
        $zipPath = storage_path('app/' . $relativeZipPath);
        $extractPath = storage_path('app/manual-updates/' . pathinfo($filename, PATHINFO_FILENAME));

        File::ensureDirectoryExists($extractPath);

        try {
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Unable to open the uploaded update archive.');
            }

            if (! $zip->extractTo($extractPath)) {
                $zip->close();
                throw new \RuntimeException('Unable to extract the uploaded update archive.');
            }

            $zip->close();

            $releaseRoot = $this->resolveReleaseRoot($extractPath);

            if (! $releaseRoot || ! File::isDirectory($releaseRoot)) {
                throw new \RuntimeException('The uploaded archive did not contain a valid release.');
            }

            $this->copyReleaseContents($releaseRoot, base_path());

            Artisan::call('migrate', ['--force' => true]);
        } finally {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            if (File::isDirectory($extractPath)) {
                File::deleteDirectory($extractPath);
            }
        }
    }

    private function resolveReleaseRoot(string $extractPath): string
    {
        $directories = File::directories($extractPath);
        $files = File::files($extractPath);

        if (empty($files) && count($directories) === 1) {
            return $directories[0];
        }

        return $extractPath;
    }

    private function copyReleaseContents(string $source, string $destination): void
    {
        $excludeFolders = collect(config('self-update.exclude_folders', []))
            ->merge(['vendor'])
            ->map(fn ($folder) => trim($folder, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = ltrim(Str::replaceFirst($source, '', $item->getPathname()), DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($relativePath === '' || $relativePath === '.env') {
                continue;
            }

            if ($this->shouldSkipPath($relativePath, $excludeFolders)) {
                continue;
            }

            $targetPath = $destination . '/' . $relativePath;

            if ($item->isDir()) {
                File::ensureDirectoryExists($targetPath);
                continue;
            }

            File::ensureDirectoryExists(dirname($targetPath));

            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }

            File::copy($item->getPathname(), $targetPath);
        }
    }

    private function shouldSkipPath(string $relativePath, array $excludeFolders): bool
    {
        foreach ($excludeFolders as $folder) {
            if ($relativePath === $folder || Str::startsWith($relativePath, $folder . '/')) {
                return true;
            }
        }

        return false;
    }
}
