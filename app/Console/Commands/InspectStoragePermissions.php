<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class InspectStoragePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:permissions {--json : Output the result as JSON.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect storage and cache directory permissions';

    public function handle(): int
    {
        $paths = Collection::make([
            base_path('storage'),
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ])->filter(fn ($path) => is_dir($path))
            ->flatMap(fn ($path) => $this->scanDirectory($path))
            ->unique('path')
            ->sortBy('path')
            ->values();

        if ($paths->isEmpty()) {
            $this->info('No storage directories were found.');

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $this->line($paths->toJson(JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $rows = $paths->map(fn ($entry) => [
            $entry['type'],
            $entry['path'],
            $entry['permissions'],
            $entry['owner'],
            $entry['group'],
            $entry['issues'] ? implode(', ', $entry['issues']) : '—',
        ]);

        $this->table([
            'Type',
            'Path',
            'Permissions',
            'Owner',
            'Group',
            'Issues',
        ], $rows);

        $problematic = $paths->filter(fn ($entry) => $entry['issues']);

        if ($problematic->isEmpty()) {
            $this->info('All scanned directories and files look accessible.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn('Some paths are not accessible to the PHP process.');
        $this->line('Update ownership and permissions, e.g.:');
        $this->line('  sudo chown -R www-data:www-data storage bootstrap/cache');
        $this->line('  sudo find storage bootstrap/cache -type d -exec chmod 775 {} +');
        $this->line('  sudo find storage bootstrap/cache -type f -exec chmod 664 {} +');

        return self::FAILURE;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function scanDirectory(string $directory): Collection
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $items = Collection::make();

        foreach ($iterator as $item) {
            $items->push($this->describePath($item->getPathname(), $item->isDir()));
        }

        return $items->prepend($this->describePath($directory, true));
    }

    /**
     * @return array<string, mixed>
     */
    private function describePath(string $path, bool $isDirectory): array
    {
        $permissions = @fileperms($path);
        $ownerId = @fileowner($path);
        $groupId = @filegroup($path);

        $issues = [];

        if (! is_readable($path)) {
            $issues[] = 'not readable';
        }

        if (! is_writable($path)) {
            $issues[] = 'not writable';
        }

        if ($isDirectory && ! is_executable($path)) {
            $issues[] = 'not traversable';
        }

        return [
            'type' => $isDirectory ? 'dir' : 'file',
            'path' => $this->relativePath($path),
            'permissions' => $permissions ? $this->formatPermissions($permissions) : 'unknown',
            'owner' => $this->resolveOwner($ownerId),
            'group' => $this->resolveGroup($groupId),
            'issues' => $issues,
        ];
    }

    private function relativePath(string $path): string
    {
        $basePath = base_path();

        return Str::startsWith($path, $basePath)
            ? ltrim(Str::after($path, $basePath), DIRECTORY_SEPARATOR)
            : $path;
    }

    private function formatPermissions(int $permissions): string
    {
        $type = match ($permissions & 0xF000) {
            0xC000 => 's', // socket
            0xA000 => 'l', // symbolic link
            0x8000 => '-', // regular
            0x6000 => 'b', // block special
            0x4000 => 'd', // directory
            0x2000 => 'c', // character special
            0x1000 => 'p', // FIFO pipe
            default => 'u', // unknown
        };

        $user = [
            ($permissions & 0x0100) ? 'r' : '-',
            ($permissions & 0x0080) ? 'w' : '-',
            ($permissions & 0x0040) ? 'x' : '-',
        ];

        $group = [
            ($permissions & 0x0020) ? 'r' : '-',
            ($permissions & 0x0010) ? 'w' : '-',
            ($permissions & 0x0008) ? 'x' : '-',
        ];

        $other = [
            ($permissions & 0x0004) ? 'r' : '-',
            ($permissions & 0x0002) ? 'w' : '-',
            ($permissions & 0x0001) ? 'x' : '-',
        ];

        if ($permissions & 0x0800) { // setuid
            $user[2] = $user[2] === 'x' ? 's' : 'S';
        }

        if ($permissions & 0x0400) { // setgid
            $group[2] = $group[2] === 'x' ? 's' : 'S';
        }

        if ($permissions & 0x0200) { // sticky bit
            $other[2] = $other[2] === 'x' ? 't' : 'T';
        }

        return $type
            . implode('', $user)
            . implode('', $group)
            . implode('', $other);
    }

    private function resolveOwner($owner): string
    {
        if ($owner === false) {
            return 'unknown';
        }

        if (function_exists('posix_getpwuid')) {
            $info = @posix_getpwuid($owner);

            if ($info && isset($info['name'])) {
                return $info['name'];
            }
        }

        return (string) $owner;
    }

    private function resolveGroup($group): string
    {
        if ($group === false) {
            return 'unknown';
        }

        if (function_exists('posix_getgrgid')) {
            $info = @posix_getgrgid($group);

            if ($info && isset($info['name'])) {
                return $info['name'];
            }
        }

        return (string) $group;
    }
}
