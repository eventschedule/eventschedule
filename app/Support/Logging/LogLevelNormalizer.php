<?php

namespace App\Support\Logging;

use Monolog\Level;

class LogLevelNormalizer
{
    /**
     * @var array<string, bool>
     */
    private const VALID_LEVELS = [
        'debug' => true,
        'info' => true,
        'notice' => true,
        'warning' => true,
        'error' => true,
        'critical' => true,
        'alert' => true,
        'emergency' => true,
    ];

    public static function normalize($value, string $default): string
    {
        foreach (self::candidateStrings($value) as $candidate) {
            $normalized = strtolower($candidate);

            if (isset(self::VALID_LEVELS[$normalized])) {
                return $normalized;
            }

            if (is_numeric($candidate)) {
                $level = self::convertNumericLevel($candidate);

                if ($level !== null) {
                    return $level;
                }
            }

            try {
                $monologLevel = Level::fromName(strtoupper($candidate));

                return strtolower($monologLevel->getName());
            } catch (\Throwable $e) {
                // Ignore invalid levels and try the next candidate.
            }
        }

        return strtolower($default);
    }

    /**
     * @return iterable<int, string>
     */
    private static function candidateStrings($value): iterable
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            foreach ($value as $candidate) {
                foreach (self::candidateStrings($candidate) as $nested) {
                    yield $nested;
                }
            }

            return;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                yield from self::candidateStrings($decoded);
            }

            foreach (explode(',', $value) as $part) {
                $part = trim($part, " \t\n\r\0\x0B\"'");

                if ($part !== '') {
                    yield $part;
                }
            }

            if ($value !== '') {
                yield trim($value, " \t\n\r\0\x0B\"'");
            }

            return;
        }

        if (is_scalar($value)) {
            $stringValue = trim((string) $value);

            if ($stringValue !== '') {
                yield $stringValue;
            }
        }
    }

    private static function convertNumericLevel($value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        foreach (Level::cases() as $level) {
            if ($level->value === $intValue) {
                return strtolower($level->getName());
            }
        }

        return null;
    }
}
