<?php

namespace App\Support;

use Illuminate\Support\Arr;

class UpdateConfigManager
{
    public static function apply(?string $repositoryUrl): void
    {
        $defaults = config('self-update.github_defaults');

        $sanitizedUrl = static::sanitizeUrl($repositoryUrl);
        $urlToUse = $sanitizedUrl ?? ($defaults['url'] ?? null);

        if ($urlToUse) {
            config(['self-update.repository_types.github.repository_url' => $urlToUse]);
        }

        $details = $urlToUse ? static::deriveRepositoryDetails($urlToUse) : null;

        if ($details !== null) {
            config([
                'self-update.repository_types.github.repository_vendor' => $details['vendor'],
                'self-update.repository_types.github.repository_name' => $details['name'],
            ]);
        } elseif (! empty($defaults)) {
            config([
                'self-update.repository_types.github.repository_vendor' => $defaults['vendor'] ?? null,
                'self-update.repository_types.github.repository_name' => $defaults['name'] ?? null,
            ]);
        }
    }

    public static function makeReleaseDownloadUrl(string $version): ?string
    {
        $repositoryUrl = config('self-update.repository_types.github.repository_url');
        $packageName = config('self-update.repository_types.github.package_file_name', 'eventschedule.zip');

        if (empty($repositoryUrl)) {
            return null;
        }

        $details = static::deriveRepositoryDetails($repositoryUrl);

        if ($details === null) {
            return rtrim($repositoryUrl, '/') . '/releases';
        }

        $base = rtrim(sprintf('https://github.com/%s/%s', $details['vendor'], $details['name']), '/');

        return sprintf('%s/releases/download/%s/%s', $base, $version, $packageName);
    }

    protected static function sanitizeUrl(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $trimmed = trim($url);

        if ($trimmed === '') {
            return null;
        }

        return rtrim($trimmed, '/');
    }

    protected static function deriveRepositoryDetails(string $repositoryUrl): ?array
    {
        $parsed = parse_url($repositoryUrl);

        if ($parsed === false || empty($parsed['path'])) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $parsed['path'])));

        if (count($segments) < 2) {
            return null;
        }

        $vendor = Arr::get($segments, count($segments) - 2);
        $name = Arr::get($segments, count($segments) - 1);

        if (! is_string($vendor) || $vendor === '' || ! is_string($name) || $name === '') {
            return null;
        }

        $name = preg_replace('/\.git$/', '', $name) ?: $name;

        return [
            'vendor' => $vendor,
            'name' => $name,
        ];
    }
}
