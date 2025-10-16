<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JsonSerializable;
use Throwable;
use Traversable;

class GroupPayloadNormalizer
{
    /**
     * Normalize group input for persistence (controllers, requests, etc.).
     *
     * @param  mixed  $groups
     * @return array<int|string, array{id: string|null, name: string, name_en: string, slug: string}>
     */
    public static function forPersistence($groups): array
    {
        $normalized = static::deepNormalize($groups);

        if (! is_array($normalized)) {
            $normalized = (array) $normalized;
        }

        $result = [];

        foreach ($normalized as $key => $group) {
            $groupArray = static::normalizeGroupEntry($group);

            $result[$key] = [
                'id' => static::nullableString($groupArray['id'] ?? null),
                'name' => static::stringOrEmpty($groupArray['name'] ?? null),
                'name_en' => static::stringOrEmpty($groupArray['name_en'] ?? null),
                'slug' => static::stringOrEmpty($groupArray['slug'] ?? null),
            ];
        }

        return $result;
    }

    /**
     * Normalize group payloads for Blade views.
     *
     * @param  mixed  $groups
     * @return array<int|string, array{id: string, name: string, name_en: string, slug: string}>
     */
    public static function forView($groups): array
    {
        $normalized = static::forPersistence($groups);
        $result = [];

        foreach ($normalized as $key => $group) {
            $id = $group['id'];

            if (! is_string($id) || $id === '') {
                $id = is_scalar($key) ? (string) $key : '';
            }

            $result[$key] = [
                'id' => $id,
                'name' => $group['name'],
                'name_en' => $group['name_en'],
                'slug' => $group['slug'],
            ];
        }

        return $result;
    }

    /**
     * @param  mixed  $value
     * @param  int  $depth
     * @return mixed
     */
    private static function deepNormalize($value, int $depth = 0)
    {
        if ($depth > 20) {
            return [];
        }

        if ($value instanceof JsonSerializable) {
            try {
                $value = $value->jsonSerialize();
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($value instanceof Traversable) {
            try {
                $value = iterator_to_array($value);
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($value instanceof Collection) {
            try {
                $value = $value->all();
            } catch (Throwable $e) {
                return [];
            }
        }

        if (is_object($value)) {
            try {
                $value = get_object_vars($value);
            } catch (Throwable $e) {
                return [];
            }
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = static::deepNormalize($item, $depth + 1);
        }

        return $value;
    }

    /**
     * @param  mixed  $group
     * @return array<string, mixed>
     */
    private static function normalizeGroupEntry($group): array
    {
        if ($group instanceof Arrayable) {
            try {
                $group = $group->toArray();
            } catch (Throwable $e) {
                report($e);
                $group = [];
            }
        } elseif ($group instanceof Model) {
            try {
                $group = $group->toArray();
            } catch (Throwable $e) {
                report($e);
                $group = [];
            }
        } elseif (is_object($group)) {
            try {
                $group = get_object_vars($group);
            } catch (Throwable $e) {
                report($e);
                $group = [];
            }
        } elseif (! is_array($group)) {
            $group = is_scalar($group)
                ? ['name' => (string) $group]
                : [];
        }

        return is_array($group) ? $group : [];
    }

    private static function nullableString($value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function stringOrEmpty($value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
