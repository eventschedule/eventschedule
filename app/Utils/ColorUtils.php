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
}
