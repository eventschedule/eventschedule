<?php

namespace App\Utils;

class ColorUtils
{
    public static function randomGradient()
    {
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {
            $gradientOptions[] = implode(', ', $gradient->colors);
        }

        $random = rand(0, count($gradientOptions) - 1);

        return $gradientOptions[$random];
    }

    public static function randomBackgroundImage()
    {
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $random = rand(0, count($backgrounds) - 1);

        return $backgrounds[$random]->name;
    }

    /**
     * Calculate relative luminance of a hex color (WCAG formula)
     */
    public static function getLuminance(string $hexColor): float
    {
        $hex = ltrim($hexColor, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // sRGB to linear RGB conversion
        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Get contrasting text color (black or white) for a background
     */
    public static function getContrastColor(string $backgroundColor): string
    {
        $luminance = self::getLuminance($backgroundColor);

        return $luminance > 0.25 ? '#000000' : '#ffffff';
    }

    public static function getContrastRatio(string $colorA, string $colorB): float
    {
        $a = self::getLuminance($colorA);
        $b = self::getLuminance($colorB);
        $light = max($a, $b);
        $dark = min($a, $b);

        return ($light + 0.05) / ($dark + 0.05);
    }

    /**
     * Pick a readable color for accent text rendered on $background.
     * Falls back to $fallback when accent contrast vs background is below WCAG 3:1.
     */
    public static function readableAccentColor(string $accentColor, string $background, string $fallback): string
    {
        return self::getContrastRatio($accentColor, $background) >= 3.0
            ? $accentColor
            : $fallback;
    }

    /**
     * '#rrggbb' in lower case, or null for anything that is not a hex colour.
     *
     * getLuminance() reads only the first six characters, so '#abc' would measure as the colour
     * 0x0abc.. and an alpha byte would be silently ignored. A three or four digit form is
     * expanded; alpha is dropped.
     */
    public static function normalizeHex(?string $hex): ?string
    {
        if (! is_string($hex) || ! preg_match('/^#?([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', trim($hex), $m)) {
            return null;
        }

        $digits = strtolower($m[1]);

        if (strlen($digits) <= 4) {
            $digits = $digits[0].$digits[0].$digits[1].$digits[1].$digits[2].$digits[2];
        }

        return '#'.substr($digits, 0, 6);
    }

    /** [r, g, b], 0 to 255, of a normalized '#rrggbb'. */
    public static function toRgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    public static function fromRgb(array $rgb): string
    {
        return '#'.implode('', array_map(fn ($c) => str_pad(dechex((int) round(max(0, min(255, $c)))), 2, '0', STR_PAD_LEFT), $rgb));
    }

    /** $colour blended into $base at $weight (0 = all $base, 1 = all $colour). */
    public static function mix(string $colour, string $base, float $weight): string
    {
        $a = self::toRgb($colour);
        $b = self::toRgb($base);

        return self::fromRgb(array_map(fn ($i) => $a[$i] * $weight + $b[$i] * (1 - $weight), [0, 1, 2]));
    }

    /** [hue, saturation, lightness], each 0 to 1. */
    public static function toHsl(string $hex): array
    {
        [$r, $g, $b] = array_map(fn ($c) => $c / 255, self::toRgb($hex));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };

        return [$h / 6, $s, $l];
    }

    public static function fromHsl(float $h, float $s, float $l): string
    {
        if ($s == 0.0) {
            return self::fromRgb([$l * 255, $l * 255, $l * 255]);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $channel = function (float $t) use ($p, $q): float {
            $t = $t < 0 ? $t + 1 : ($t > 1 ? $t - 1 : $t);

            return match (true) {
                $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
                $t < 1 / 2 => $q,
                $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
                default => $p,
            };
        };

        return self::fromRgb([$channel($h + 1 / 3) * 255, $channel($h) * 255, $channel($h - 1 / 3) * 255]);
    }

    /**
     * $colour moved along its own hue, darker or lighter, until it reaches $ratio against every
     * background. Null when it never does. The colour itself is the first candidate, so one that
     * already passes comes back unchanged (to rounding).
     */
    public static function shiftUntil(string $colour, array $backgrounds, bool $darker, float $ratio = 4.5): ?string
    {
        [$h, $s, $l] = self::toHsl($colour);

        for ($step = 0; $step <= 100; $step++) {
            $candidate = self::fromHsl($h, $s, max(0.0, min(1.0, $l + ($darker ? -0.01 : 0.01) * $step)));
            $passes = true;

            foreach ($backgrounds as $background) {
                if (self::getContrastRatio($candidate, $background) < $ratio) {
                    $passes = false;
                    break;
                }
            }

            if ($passes) {
                return $candidate;
            }
        }

        return null;
    }
}
