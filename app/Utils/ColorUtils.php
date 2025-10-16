<?php

namespace App\Utils;

class ColorUtils
{
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
        $backgrounds = self::decodeJsonFile('storage/backgrounds.json');
        $backgroundOptions = [];

        foreach (self::yieldRoleAssetItems($backgrounds) as $background) {
            $value = self::determineRoleAssetName($background);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $label = self::determineRoleAssetLabel($background);

            if (! is_string($label) || trim($label) === '') {
                $label = str_replace('_', ' ', $value);
            } else {
                $label = trim($label);
            }

            $backgroundOptions[$value] = $label;
        }

        if (empty($backgroundOptions)) {
            return '';
        }

        $values = array_keys($backgroundOptions);
        $random = $values[array_rand($values)];

        return $random;
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
            foreach (['name', 'value', 'label', 'title', 'id', 'slug'] as $key) {
                if (array_key_exists($key, $item)) {
                    $candidate = self::determineRoleAssetName($item[$key], $depth + 1);

                    if (is_string($candidate) && trim($candidate) !== '') {
                        return $candidate;
                    }
                }
            }

            foreach ($item as $value) {
                $candidate = self::determineRoleAssetName($value, $depth + 1);

                if (is_string($candidate) && trim($candidate) !== '') {
                    return $candidate;
                }
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
            foreach (['label', 'name', 'title', 'value', 'id', 'slug'] as $key) {
                if (array_key_exists($key, $item)) {
                    $candidate = self::determineRoleAssetLabel($item[$key], $depth + 1);

                    if (is_string($candidate) && trim($candidate) !== '') {
                        return $candidate;
                    }
                }
            }

            foreach ($item as $value) {
                $candidate = self::determineRoleAssetLabel($value, $depth + 1);

                if (is_string($candidate) && trim($candidate) !== '') {
                    return $candidate;
                }
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

    private static function isColorString(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if ($value[0] === '#') {
            return true;
        }

        if (preg_match('/^(?:rgb|rgba|hsl|hsla)\s*\(/i', $value)) {
            return true;
        }

        if (preg_match('/^[0-9a-f]{3,8}$/i', $value)) {
            return true;
        }

        return false;
    }
}
