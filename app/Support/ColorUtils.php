<?php

namespace App\Support;

class ColorUtils
{
    public static function normalizeHexColor(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '#')) {
            $trimmed = substr($trimmed, 1);
        }

        if (strlen($trimmed) === 3) {
            $expanded = '';
            foreach (str_split($trimmed) as $char) {
                $expanded .= str_repeat($char, 2);
            }
            $trimmed = $expanded;
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $trimmed)) {
            return null;
        }

        return '#' . strtoupper($trimmed);
    }

    public static function hexToRgb(?string $hex): ?array
    {
        $normalized = self::normalizeHexColor($hex);

        if ($normalized === null) {
            return null;
        }

        $hexValue = substr($normalized, 1);

        return [
            hexdec(substr($hexValue, 0, 2)),
            hexdec(substr($hexValue, 2, 2)),
            hexdec(substr($hexValue, 4, 2)),
        ];
    }

    public static function hexToRgbString(?string $hex): ?string
    {
        $components = self::hexToRgb($hex);

        if ($components === null) {
            return null;
        }

        return implode(', ', array_map(fn ($component) => (string) $component, $components));
    }

    public static function relativeLuminance(?string $hex): ?float
    {
        $rgb = self::hexToRgb($hex);

        if ($rgb === null) {
            return null;
        }

        [$r, $g, $b] = array_map(fn ($value) => $value / 255, $rgb);

        $transform = static function (float $channel): float {
            return $channel <= 0.03928
                ? $channel / 12.92
                : pow(($channel + 0.055) / 1.055, 2.4);
        };

        $r = $transform($r);
        $g = $transform($g);
        $b = $transform($b);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    public static function contrastRatio(?string $hexA, ?string $hexB): ?float
    {
        $lumA = self::relativeLuminance($hexA);
        $lumB = self::relativeLuminance($hexB);

        if ($lumA === null || $lumB === null) {
            return null;
        }

        $lighter = max($lumA, $lumB);
        $darker = min($lumA, $lumB);

        if ($darker < 0) {
            $darker = 0.0;
        }

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    public static function mix(string $hexA, string $hexB, float $weightForA = 0.5): ?string
    {
        $rgbA = self::hexToRgb($hexA);
        $rgbB = self::hexToRgb($hexB);

        if ($rgbA === null || $rgbB === null) {
            return null;
        }

        $weightForA = max(0.0, min(1.0, $weightForA));
        $weightForB = 1.0 - $weightForA;

        $mixed = [];

        for ($index = 0; $index < 3; $index++) {
            $mixed[$index] = (int) round($rgbA[$index] * $weightForA + $rgbB[$index] * $weightForB);
            $mixed[$index] = max(0, min(255, $mixed[$index]));
        }

        return sprintf('#%02X%02X%02X', $mixed[0], $mixed[1], $mixed[2]);
    }
}
