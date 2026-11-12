<?php

namespace App\Utils;

use App\Models\Role;

/**
 * The schedule form's "Translate into" picker (Details > Language and time): its options in the
 * order a person reads them, and the language it starts on when translation is switched on.
 *
 * It used to list the languages by code and, switched on, take the first one it could: every
 * schedule that turned translation on was offered Arabic first.
 */
class TranslationTargets
{
    /**
     * The language most of a country's visitors read, where the app speaks it. A country not listed
     * suggests nothing of its own.
     */
    public const BY_COUNTRY = [
        'pt' => 'pt-PT', 'br' => 'pt', 'ao' => 'pt-PT', 'mz' => 'pt-PT', 'cv' => 'pt-PT',
        'es' => 'es', 'mx' => 'es', 'ar' => 'es', 'co' => 'es', 'cl' => 'es', 'pe' => 'es', 've' => 'es',
        'ec' => 'es', 'gt' => 'es', 'cu' => 'es', 'bo' => 'es', 'do' => 'es', 'hn' => 'es', 'py' => 'es',
        'sv' => 'es', 'ni' => 'es', 'cr' => 'es', 'pa' => 'es', 'uy' => 'es', 'pr' => 'es',
        'fr' => 'fr', 'mc' => 'fr', 'sn' => 'fr', 'ci' => 'fr',
        'de' => 'de', 'at' => 'de', 'li' => 'de',
        'it' => 'it', 'sm' => 'it', 'va' => 'it',
        'nl' => 'nl', 'sr' => 'nl',
        'il' => 'he',
        'ru' => 'ru', 'by' => 'ru', 'kz' => 'ru', 'kg' => 'ru',
        'ro' => 'ro', 'md' => 'ro',
        'ee' => 'et',
        'sa' => 'ar', 'ae' => 'ar', 'eg' => 'ar', 'jo' => 'ar', 'lb' => 'ar', 'ma' => 'ar', 'dz' => 'ar',
        'tn' => 'ar', 'iq' => 'ar', 'kw' => 'ar', 'qa' => 'ar', 'bh' => 'ar', 'om' => 'ar', 'ye' => 'ar',
        'ly' => 'ar', 'sy' => 'ar', 'ps' => 'ar', 'sd' => 'ar',
        'us' => 'en', 'gb' => 'en', 'ie' => 'en', 'au' => 'en', 'nz' => 'en', 'ca' => 'en', 'za' => 'en',
    ];

    /**
     * The supported languages as [code => name in the reader's language], sorted by that name the
     * way the reader's language sorts.
     *
     * @return array<string, string>
     */
    public static function options(?string $locale = null): array
    {
        $names = [];
        foreach (config('app.supported_languages', []) as $code => $key) {
            $names[$code] = __('messages.'.$key);
        }

        $collator = class_exists(\Collator::class) ? new \Collator(str_replace('-', '_', $locale ?? app()->getLocale())) : null;

        uasort($names, fn ($a, $b) => $collator ? $collator->compare($a, $b) : strcasecmp($a, $b));

        return $names;
    }

    /**
     * The language "Translate into" starts on: the one already chosen while translation is on;
     * otherwise the language of the schedule's country, when that is not the language it is
     * written in; otherwise English for a schedule not written in English; otherwise none, and the
     * picker asks.
     */
    public static function suggest(Role $role): ?string
    {
        $authored = $role->language_code ?: 'en';
        $target = $role->translation_language_code;
        $supported = config('app.supported_languages', []);

        if ($target && $target !== $authored && isset($supported[$target])) {
            return $target;
        }

        // Not the language it is written in, in either of its forms (pt and pt-PT).
        $base = fn ($code) => strtolower(explode('-', (string) $code)[0]);
        $fromCountry = self::BY_COUNTRY[strtolower((string) $role->country_code)] ?? null;
        if ($fromCountry && $base($fromCountry) !== $base($authored) && isset($supported[$fromCountry])) {
            return $fromCountry;
        }

        return $authored !== 'en' ? 'en' : null;
    }
}
