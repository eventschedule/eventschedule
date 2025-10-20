#!/usr/bin/env php
<?php

declare(strict_types=1);

$allowedChannels = ['production', 'beta'];

$channelInput = $argv[1] ?? null;

if ($channelInput === null) {
    fwrite(STDERR, "Usage: php scripts/bump-version.php <production|beta>\n");
    exit(1);
}

$channel = strtolower(trim((string) $channelInput));

if (! in_array($channel, $allowedChannels, true)) {
    fwrite(STDERR, sprintf(
        'Invalid channel "%s". Allowed values are: %s' . PHP_EOL,
        $channel,
        implode(', ', $allowedChannels)
    ));
    exit(1);
}

$versionFile = dirname(__DIR__) . '/VERSION';

if (! file_exists($versionFile)) {
    fwrite(STDERR, "Unable to locate VERSION file." . PHP_EOL);
    exit(1);
}

$currentVersion = trim((string) file_get_contents($versionFile));

if ($currentVersion === '') {
    fwrite(STDERR, "VERSION file is empty." . PHP_EOL);
    exit(1);
}

try {
    $nextVersion = bumpVersion($currentVersion, $channel);
} catch (InvalidArgumentException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}

if ($nextVersion === $currentVersion) {
    fwrite(STDERR, "New version matches the current version. No changes made." . PHP_EOL);
    exit(1);
}

file_put_contents($versionFile, $nextVersion . PHP_EOL);

if ($githubOutput = getenv('GITHUB_OUTPUT')) {
    $outputLines = [
        'version=' . $nextVersion,
        'channel=' . $channel,
        'prerelease=' . ($channel === 'beta' ? 'true' : 'false'),
    ];

    file_put_contents($githubOutput, implode(PHP_EOL, $outputLines) . PHP_EOL, FILE_APPEND);
}

fwrite(STDOUT, sprintf(
    "Bumped version: %s -> %s (%s channel)\n",
    $currentVersion,
    $nextVersion,
    $channel
));

function bumpVersion(string $currentVersion, string $channel): string
{
    $parsed = parseVersion($currentVersion);

    if ($channel === 'beta') {
        if ($parsed['suffix'] !== null) {
            $patch = $parsed['patch'] ?? 0;

            return formatVersion(
                $parsed['major'],
                $parsed['minor'],
                $patch + 1,
                $parsed['suffix']
            );
        }

        return formatVersion(
            $parsed['major'] + 1,
            0,
            1,
            'b'
        );
    }

    // Production channel
    $nextMinor = $parsed['minor'] + 1;

    return formatVersion($parsed['major'], $nextMinor, null, null);
}

function parseVersion(string $version): array
{
    $pattern = '/^(?<major>\d+)\.(?<minor>\d+)(?:\.(?<patch>\d+))?(?<suffix>[a-z])?$/i';

    if (! preg_match($pattern, $version, $matches)) {
        throw new InvalidArgumentException(sprintf('Invalid version format: %s', $version));
    }

    return [
        'major' => (int) $matches['major'],
        'minor' => (int) $matches['minor'],
        'patch' => array_key_exists('patch', $matches) && $matches['patch'] !== ''
            ? (int) $matches['patch']
            : null,
        'suffix' => array_key_exists('suffix', $matches) && $matches['suffix'] !== ''
            ? strtolower($matches['suffix'])
            : null,
    ];
}

function formatVersion(int $major, int $minor, ?int $patch, ?string $suffix): string
{
    $version = sprintf('%d.%d', $major, $minor);

    if ($patch !== null) {
        $version .= '.' . $patch;
    }

    if ($suffix !== null && $suffix !== '') {
        $version .= $suffix;
    }

    return $version;
}
