<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class EventType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'translations',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'translations' => 'array',
    ];

    /**
     * Boot the model and register cache flushing callbacks.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }

    /**
     * Retrieve all event types ordered by the configured sort order.
     */
    public static function ordered(): Collection
    {
        if (! static::tableAvailable()) {
            return collect();
        }

        return static::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get the translated name for the provided locale.
     */
    public function translatedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $fallbackLocale = config('app.fallback_locale') ?: 'en';
        $translations = $this->translations ?? [];

        if ($locale && isset($translations[$locale]) && $translations[$locale] !== '') {
            return $translations[$locale];
        }

        if ($fallbackLocale && isset($translations[$fallbackLocale]) && $translations[$fallbackLocale] !== '') {
            return $translations[$fallbackLocale];
        }

        if (isset($translations['en']) && $translations['en'] !== '') {
            return $translations['en'];
        }

        return $this->name ?? '';
    }

    /**
     * Retrieve translated options indexed by their identifier for the provided locale.
     */
    public static function optionsForLocale(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $localeKey = $locale ?: 'default';
        $cacheKey = static::cacheKey($localeKey);

        return Cache::rememberForever($cacheKey, function () use ($locale, $localeKey) {
            if (! static::tableAvailable()) {
                return static::configFallback($locale);
            }

            $types = static::ordered();

            if ($types->isEmpty()) {
                return static::configFallback($locale);
            }

            return $types->mapWithKeys(function (self $type) use ($locale) {
                return [$type->id => $type->translatedName($locale)];
            })->toArray();
        });
    }

    /**
     * Flush cached option lists for all supported locales.
     */
    public static function flushCache(): void
    {
        $locales = config('app.supported_languages', []);
        $fallbackLocale = config('app.fallback_locale');

        if ($fallbackLocale && ! in_array($fallbackLocale, $locales, true)) {
            $locales[] = $fallbackLocale;
        }

        $locales[] = 'default';

        foreach (array_unique(array_filter($locales)) as $locale) {
            Cache::forget(static::cacheKey($locale));
        }
    }

    /**
     * Generate a unique slug based on the provided value.
     */
    public static function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'event-type';

        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Build a translations array ensuring required locales are populated.
     */
    public static function buildTranslations(string $defaultName, array $translations = []): array
    {
        $defaultName = trim($defaultName);
        $normalized = [];

        foreach ($translations as $locale => $value) {
            if (! is_string($locale)) {
                continue;
            }

            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                continue;
            }

            $normalized[$locale] = $value;
        }

        $fallbackLocale = config('app.fallback_locale', 'en') ?: 'en';

        if (! isset($normalized[$fallbackLocale]) || $normalized[$fallbackLocale] === '') {
            $normalized[$fallbackLocale] = $defaultName;
        }

        if (! isset($normalized['en']) || $normalized['en'] === '') {
            $normalized['en'] = $defaultName;
        }

        return $normalized;
    }

    /**
     * Determine whether the backing table is available.
     */
    protected static function tableAvailable(): bool
    {
        try {
            return Schema::hasTable((new static())->getTable());
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Resolve the cache key for the provided locale.
     */
    protected static function cacheKey(string $locale): string
    {
        return "event_types.options.{$locale}";
    }

    /**
     * Provide a fallback list of event types derived from configuration.
     */
    protected static function configFallback(?string $locale = null): array
    {
        $categories = config('app.event_categories', []);

        if (empty($categories)) {
            return [];
        }

        $locale = $locale ?: app()->getLocale() ?: config('app.fallback_locale', 'en');
        $results = [];

        foreach ($categories as $id => $englishName) {
            $key = Str::of($englishName)
                ->lower()
                ->replace(' & ', '_&_')
                ->replace(' ', '_')
                ->toString();

            $translation = Lang::get("messages.{$key}", [], $locale);

            if (! is_string($translation) || $translation === "messages.{$key}") {
                $translation = $englishName;
            }

            $results[$id] = $translation;
        }

        return $results;
    }
}
