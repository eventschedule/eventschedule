<?php

namespace App\Utils;

class ColorUtils
{
    private const STATIC_BACKGROUND_IMAGES = [
        'Abstract_Sunrise' => 'Abstract Sunrise',
    ];

    public static function randomGradient()
    {
        $gradients = self::decodeJsonFile('storage/gradients.json');
        $gradientOptions = [];

        foreach (self::yieldRoleAssetItems($gradients) as $gradient) {
            $name = self::determineRoleAssetName($gradient);
            $colors = self::extractColors($gradient);

            if ($name === null || empty($colors)) {
                continue;
            }

            $key = join(', ', $colors);

            if ($key === '') {
                continue;
            }

            $gradientOptions[$key] = $key;
        }

        if (empty($gradientOptions)) {
            return '#1A2980, #26D0CE';
        }

        $values = array_values(array_unique($gradientOptions));
        $random = array_rand($values);

        return $values[$random];
    }

    public static function randomBackgroundImage()
    {
        $options = array_keys(self::STATIC_BACKGROUND_IMAGES);

        if (empty($options)) {
            return '';
        }

        return $options[array_rand($options)];
    }

    public static function backgroundImageOptions(): array
    {
        return self::STATIC_BACKGROUND_IMAGES;
    }

    /**
     * @return array<int, mixed>
     */
    private static function decodeJsonFile(string $relativePath): array
    {
        $path = base_path($relativePath);

        if (! file_exists($path) || ! is_readable($path)) {
            return [];
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            return [];
        }

        return $data;
    }

    private static function yieldRoleAssetItems($items): iterable
    {
        $normalized = self::normalizeDecodedJsonStructure($items);

        if ($normalized === null) {
            return;
        }

        if (! is_array($normalized)) {
            yield $normalized;

            return;
        }

        foreach ($normalized as $item) {
            yield $item;
        }
    }

    private static function normalizeDecodedJsonStructure($value, int $depth = 0)
    {
        if ($depth > 20) {
            return [];
        }

        if ($value instanceof \JsonSerializable) {
            try {
                $value = $value->jsonSerialize();
            } catch (\Throwable $e) {
                return [];
            }
        }

        if ($value instanceof \Traversable) {
            try {
                $value = iterator_to_array($value);
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (is_object($value)) {
            try {
                $value = get_object_vars($value);
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = self::normalizeDecodedJsonStructure($item, $depth + 1);
        }

        return $value;
    }

    private static function determineRoleAssetName($item, int $depth = 0): ?string
    {
        if ($depth > 10) {
            return null;
        }

        if ($item instanceof \JsonSerializable) {
            try {
                $item = $item->jsonSerialize();
            } catch (\Throwable $e) {
                return null;
            }
        }

        if ($item instanceof \Traversable) {
            try {
                $item = iterator_to_array($item);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_object($item)) {
            try {
                $item = get_object_vars($item);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($item)) {
            $item = trim($item);

            return $item === '' ? null : $item;
        }

        if (is_scalar($item)) {
            $string = trim((string) $item);

            return $string === '' ? null : $string;
        }

        if (is_array($item)) {
            $colors = self::extractColors($item, $depth + 1);
            $colorName = empty($colors) ? null : implode(' → ', $colors);

            foreach (['name', 'value', 'label', 'title', 'id', 'slug'] as $key) {
                if (! array_key_exists($key, $item)) {
                    continue;
                }

                $candidate = self::determineRoleAssetName($item[$key], $depth + 1);

                if (! is_string($candidate)) {
                    continue;
                }

                $candidate = trim($candidate);

                if ($candidate === '') {
                    continue;
                }

                if ($colorName !== null && self::shouldDeferToColorFallback($candidate, $colors)) {
                    continue;
                }

                return $candidate;
            }

            foreach ($item as $value) {
                $candidate = self::determineRoleAssetName($value, $depth + 1);

                if (! is_string($candidate)) {
                    continue;
                }

                $candidate = trim($candidate);

                if ($candidate === '') {
                    continue;
                }

                if ($colorName !== null && self::shouldDeferToColorFallback($candidate, $colors)) {
                    continue;
                }

                return $candidate;
            }

            if ($colorName !== null && $colorName !== '') {
                return $colorName;
            }
        }

        return null;
    }

    private static function determineRoleAssetLabel($item, int $depth = 0): ?string
    {
        if ($depth > 10) {
            return null;
        }

        if ($item instanceof \JsonSerializable) {
            try {
                $item = $item->jsonSerialize();
            } catch (\Throwable $e) {
                return null;
            }
        }

        if ($item instanceof \Traversable) {
            try {
                $item = iterator_to_array($item);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_object($item)) {
            try {
                $item = get_object_vars($item);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($item)) {
            $item = trim($item);

            return $item === '' ? null : $item;
        }

        if (is_scalar($item)) {
            $string = trim((string) $item);

            return $string === '' ? null : $string;
        }

        if (is_array($item)) {
            $colors = self::extractColors($item, $depth + 1);
            $colorLabel = empty($colors) ? null : implode(' → ', $colors);

            foreach (['label', 'name', 'title', 'value', 'id', 'slug'] as $key) {
                if (! array_key_exists($key, $item)) {
                    continue;
                }

                $candidate = self::determineRoleAssetLabel($item[$key], $depth + 1);

                if (! is_string($candidate)) {
                    continue;
                }

                $candidate = trim($candidate);

                if ($candidate === '') {
                    continue;
                }

                if ($colorLabel !== null && self::shouldDeferToColorFallback($candidate, $colors)) {
                    continue;
                }

                return $candidate;
            }

            foreach ($item as $value) {
                $candidate = self::determineRoleAssetLabel($value, $depth + 1);

                if (! is_string($candidate)) {
                    continue;
                }

                $candidate = trim($candidate);

                if ($candidate === '') {
                    continue;
                }

                if ($colorLabel !== null && self::shouldDeferToColorFallback($candidate, $colors)) {
                    continue;
                }

                return $candidate;
            }

            if ($colorLabel !== null && $colorLabel !== '') {
                return $colorLabel;
            }
        }

        return null;
    }

    private static function extractColors($item, int $depth = 0): array
    {
        if ($depth > 10) {
            return [];
        }

        if ($item instanceof \JsonSerializable) {
            try {
                $item = $item->jsonSerialize();
            } catch (\Throwable $e) {
                return [];
            }
        }

        if ($item instanceof \Traversable) {
            try {
                $item = iterator_to_array($item);
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (is_object($item)) {
            try {
                $item = get_object_vars($item);
            } catch (\Throwable $e) {
                return [];
            }
        }

        if (is_string($item) || is_numeric($item)) {
            $candidate = trim((string) $item);

            return self::isColorString($candidate) ? [$candidate] : [];
        }

        if (! is_array($item)) {
            return [];
        }

        $colors = [];

        foreach ($item as $value) {
            $extracted = self::extractColors($value, $depth + 1);

            if (! empty($extracted)) {
                $colors = array_merge($colors, $extracted);
            }
        }

        if (! empty($colors)) {
            return array_values(array_unique(array_filter($colors, function ($value) {
                return is_string($value) && trim($value) !== '';
            })));
        }

        return [];
    }

    private static function shouldDeferToColorFallback(string $candidate, array $colors): bool
    {
        if (count($colors) < 2) {
            return false;
        }

        $normalizedCandidate = self::normalizeColorForComparison($candidate);

        if ($normalizedCandidate === '') {
            return false;
        }

        foreach ($colors as $color) {
            if ($normalizedCandidate === self::normalizeColorForComparison($color)) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeColorForComparison(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^(?:#|0x)([0-9a-f]{3,8})$/i', $value, $matches)) {
            return strtolower('#' . $matches[1]);
        }

        if (preg_match('/^(rgb|rgba|hsl|hsla)\s*\(([^)]*)\)$/i', $value, $matches)) {
            $prefix = strtolower($matches[1]);
            $components = preg_replace('/\s+/', '', $matches[2]);

            return $prefix . '(' . $components . ')';
        }

        if (preg_match('/^[0-9a-f]{3,8}$/i', $value)) {
            return strtolower('#' . $value);
        }

        return '';
    }

    private static function isColorString(string $value): bool
    {
        return self::normalizeColorForComparison($value) !== '';
    }
}
