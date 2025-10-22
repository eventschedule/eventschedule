#!/usr/bin/env php
<?php

declare(strict_types=1);

const ALLOWED_CHANNELS = ['production', 'beta'];

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    exit(run($argv));
}

function run(array $arguments): int
{
    $channelInput = $arguments[1] ?? null;

    if ($channelInput === null) {
        fwrite(STDERR, "Usage: php scripts/bump-version.php <production|beta> [--major]\n");

        return 1;
    }

    $channel = strtolower(trim((string) $channelInput));

    if (! in_array($channel, ALLOWED_CHANNELS, true)) {
        fwrite(STDERR, sprintf(
            'Invalid channel "%s". Allowed values are: %s' . PHP_EOL,
            $channel,
            implode(', ', ALLOWED_CHANNELS)
        ));

        return 1;
    }

    try {
        $forceMajor = parseFlags(array_slice($arguments, 2));
    } catch (InvalidArgumentException $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);

        return 1;
    }

    $versionFile = dirname(__DIR__) . '/VERSION';

    if (! file_exists($versionFile)) {
        fwrite(STDERR, "Unable to locate VERSION file." . PHP_EOL);

        return 1;
    }

    $currentVersion = trim((string) file_get_contents($versionFile));

    if ($currentVersion === '') {
        fwrite(STDERR, "VERSION file is empty." . PHP_EOL);

        return 1;
    }

    try {
        $nextVersion = bumpVersion($currentVersion, $channel, $forceMajor);
    } catch (InvalidArgumentException $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);

        return 1;
    }

    if ($nextVersion === $currentVersion) {
        fwrite(STDERR, "New version matches the current version. No changes made." . PHP_EOL);

        return 1;
    }

    file_put_contents($versionFile, $nextVersion . PHP_EOL);

    writeGithubOutputs($nextVersion, $channel, $forceMajor);

    $details = sprintf('%s channel', $channel);

    if ($forceMajor) {
        $details .= ', major release';
    }

    fwrite(STDOUT, sprintf(
        "Bumped version: %s -> %s (%s)\n",
        $currentVersion,
        $nextVersion,
        $details
    ));

    return 0;
}

function parseFlags(array $flags): bool
{
    $forceMajor = false;

    foreach ($flags as $flag) {
        if ($flag === '--major') {
            $forceMajor = true;

            continue;
        }

        if ($flag === '' || $flag === null) {
            continue;
        }

        throw new InvalidArgumentException(sprintf('Unknown option: %s', $flag));
    }

    return $forceMajor;
}

function writeGithubOutputs(string $version, string $channel, bool $forceMajor): void
{
    $githubOutput = getenv('GITHUB_OUTPUT');

    if (! $githubOutput) {
        return;
    }

    $outputLines = [
        'version=' . $version,
        'channel=' . $channel,
        'prerelease=' . ($channel === 'beta' ? 'true' : 'false'),
        'is_major=' . ($forceMajor ? 'true' : 'false'),
    ];

    file_put_contents($githubOutput, implode(PHP_EOL, $outputLines) . PHP_EOL, FILE_APPEND);
}

function bumpVersion(string $currentVersion, string $channel, bool $forceMajor = false): string
{
    $parsed = parseVersion($currentVersion);

    if ($forceMajor) {
        $nextMajor = $parsed['major'] + 1;

        if ($channel === 'beta') {
            return formatVersion($nextMajor, 0, null, 'b');
        }

        return formatVersion($nextMajor, 0, null, null);
    }

    if ($channel === 'beta') {
        $patch = $parsed['patch'] ?? 0;

        if ($parsed['suffix'] !== null) {
            return formatVersion(
                $parsed['major'],
                $parsed['minor'],
                $patch + 1,
                $parsed['suffix']
            );
        }

        return formatVersion(
            $parsed['major'],
            $parsed['minor'],
            $patch + 1,
            'b'
        );
    }

    // Production channel
    if ($parsed['suffix'] !== null) {
        return formatVersion(
            $parsed['major'],
            $parsed['minor'],
            $parsed['patch'],
            null
        );
    }

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
