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
    $suffix = $channel === 'beta' ? 'b' : 'p';
    $currentDate = resolveVersionDate();

    $parsed = null;

    try {
        $parsed = parseVersion($currentVersion);
    } catch (InvalidArgumentException $exception) {
        if (! isLegacyVersion($currentVersion)) {
            throw $exception;
        }
    }

    if ($forceMajor) {
        return formatVersion($currentDate, 1, $suffix);
    }

    if ($parsed !== null
        && $parsed['date'] === $currentDate
        && $parsed['suffix'] === $suffix
    ) {
        $sequence = $parsed['sequence'] + 1;
    } else {
        $sequence = 1;
    }

    return formatVersion($currentDate, $sequence, $suffix);
}

function parseVersion(string $version): array
{
    $pattern = '/^(?<date>\d{8})-(?<sequence>\d{2,})(?<suffix>[bp])$/i';

    if (preg_match($pattern, $version, $matches) !== 1) {
        throw new InvalidArgumentException(sprintf('Invalid version format: %s', $version));
    }

    $date = $matches['date'];
    $sequence = (int) $matches['sequence'];
    $suffix = strtolower($matches['suffix']);

    if (! validateVersionDate($date)) {
        throw new InvalidArgumentException(sprintf('Invalid version date: %s', $version));
    }

    if ($sequence < 1) {
        throw new InvalidArgumentException(sprintf('Invalid version sequence: %s', $version));
    }

    return [
        'date' => $date,
        'sequence' => $sequence,
        'suffix' => $suffix,
    ];
}

function formatVersion(string $date, int $sequence, string $suffix): string
{
    return sprintf('%s-%02d%s', $date, $sequence, $suffix);
}

function resolveVersionDate(): string
{
    $override = getenv('BUMP_VERSION_DATE');

    if (is_string($override) && $override !== '') {
        $override = trim($override);

        if (! preg_match('/^\d{8}$/', $override)) {
            throw new InvalidArgumentException('BUMP_VERSION_DATE must be in Ymd format (e.g. 20251024).');
        }

        if (! validateVersionDate($override)) {
            throw new InvalidArgumentException(sprintf('Invalid BUMP_VERSION_DATE value: %s', $override));
        }

        return $override;
    }

    return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Ymd');
}

function validateVersionDate(string $date): bool
{
    $year = (int) substr($date, 0, 4);
    $month = (int) substr($date, 4, 2);
    $day = (int) substr($date, 6, 2);

    return checkdate($month, $day, $year);
}

function isLegacyVersion(string $version): bool
{
    return preg_match('/^\d+\.\d+(?:\.\d+)?[a-z]?$/i', $version) === 1;
}
