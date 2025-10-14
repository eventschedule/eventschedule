<?php

namespace App\Utils;

class ColorUtils
{
    public static function randomGradient()
    {
        $gradients = self::decodeJsonFile('storage/gradients.json');

        $gradientOptions = [];

        foreach ($gradients as $gradient) {
            if (is_object($gradient)) {
                $gradient = get_object_vars($gradient);
            }

            if (is_array($gradient) && isset($gradient['colors']) && is_array($gradient['colors'])) {
                $colors = array_values(array_filter($gradient['colors'], 'is_string'));

                if (! empty($colors)) {
                    $gradientOptions[] = join(', ', $colors);
                }
            } elseif (is_string($gradient) && trim($gradient) !== '') {
                // Some legacy data stores gradients as simple comma separated strings.
                $gradientOptions[] = $gradient;
            }
        }

        if (empty($gradientOptions)) {
            return '#1A2980, #26D0CE';
        }

        $random = array_rand($gradientOptions);

        return $gradientOptions[$random];
    }

    public static function randomBackgroundImage()
    {
        $backgrounds = self::decodeJsonFile('storage/backgrounds.json');

        $backgroundOptions = [];

        foreach ($backgrounds as $background) {
            if (is_object($background)) {
                $background = get_object_vars($background);
            }

            if (is_array($background) && isset($background['name']) && is_string($background['name'])) {
                $backgroundOptions[] = $background['name'];
            } elseif (is_string($background) && trim($background) !== '') {
                $backgroundOptions[] = $background;
            }
        }

        if (empty($backgroundOptions)) {
            return '';
        }

        $random = array_rand($backgroundOptions);

        return $backgroundOptions[$random];
    }

    /**
     * @return array<int, mixed>
     */
    private static function decodeJsonFile(string $relativePath): array
    {
        $path = base_path($relativePath);

        if (! file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        $data = json_decode($contents);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            return [];
        }

        return $data;
    }
}
