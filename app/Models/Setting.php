<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function forGroup(string $group): array
    {
        return Cache::rememberForever(static::cacheKey($group), function () use ($group) {
            return static::query()
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function setGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget(static::cacheKey($group));
    }

    public static function clearGroup(string $group): void
    {
        static::query()->where('group', $group)->delete();

        Cache::forget(static::cacheKey($group));
    }

    protected static function cacheKey(string $group): string
    {
        return "settings.{$group}";
    }
}
