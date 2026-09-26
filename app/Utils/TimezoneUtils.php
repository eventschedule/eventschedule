<?php

namespace App\Utils;

class TimezoneUtils
{
    /**
     * Backward-compatibility aliases that browsers still report, mapped to the IANA name PHP lists.
     *
     * Chrome reports India as Asia/Calcutta, Linux desktops report Etc/UTC, and so on: the browser
     * follows ICU, whose "canonical" IDs are the OLD names. PHP's DateTimeZone::ALL list - which is
     * what Laravel's plain `timezone` rule tests - has none of these, so a schedule created from such
     * a browser failed validation on a hidden field the user could neither see nor fix.
     *
     * This map comes before IntlTimeZone::getCanonicalID() on purpose: ICU maps Asia/Kolkata TO
     * Asia/Calcutta and Europe/Kyiv TO Europe/Kiev, the wrong direction for us. It covers exactly the
     * aliases the ICU fallback cannot resolve to a listed name (the rest, US/*, Canada/*, Australia/*
     * and so on, ICU does resolve). Etc/GMT+N, EST, CET and friends have no geographic canonical
     * name, so they are deliberately absent and pass through unchanged.
     */
    public const ALIASES = [
        'Africa/Asmera' => 'Africa/Asmara',
        'America/Argentina/ComodRivadavia' => 'America/Argentina/Catamarca',
        'America/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
        'America/Catamarca' => 'America/Argentina/Catamarca',
        'America/Coral_Harbour' => 'America/Panama',
        'America/Cordoba' => 'America/Argentina/Cordoba',
        'America/Fort_Wayne' => 'America/Indiana/Indianapolis',
        'America/Godthab' => 'America/Nuuk',
        'America/Indianapolis' => 'America/Indiana/Indianapolis',
        'America/Jujuy' => 'America/Argentina/Jujuy',
        'America/Louisville' => 'America/Kentucky/Louisville',
        'America/Mendoza' => 'America/Argentina/Mendoza',
        'America/Rosario' => 'America/Argentina/Cordoba',
        'Asia/Calcutta' => 'Asia/Kolkata',
        'Asia/Katmandu' => 'Asia/Kathmandu',
        'Asia/Rangoon' => 'Asia/Yangon',
        'Asia/Saigon' => 'Asia/Ho_Chi_Minh',
        'Atlantic/Faeroe' => 'Atlantic/Faroe',
        'Etc/GMT' => 'UTC',
        'Etc/GMT+0' => 'UTC',
        'Etc/GMT-0' => 'UTC',
        'Etc/GMT0' => 'UTC',
        'Etc/Greenwich' => 'UTC',
        'Etc/UCT' => 'UTC',
        'Etc/Universal' => 'UTC',
        'Etc/UTC' => 'UTC',
        'Etc/Zulu' => 'UTC',
        'Europe/Kiev' => 'Europe/Kyiv',
        'Europe/Uzhgorod' => 'Europe/Kyiv',
        'Europe/Zaporozhye' => 'Europe/Kyiv',
        'GMT' => 'UTC',
        'GMT+0' => 'UTC',
        'GMT-0' => 'UTC',
        'GMT0' => 'UTC',
        'Greenwich' => 'UTC',
        'Pacific/Enderbury' => 'Pacific/Kanton',
        'Pacific/Ponape' => 'Pacific/Pohnpei',
        'Pacific/Truk' => 'Pacific/Chuuk',
        'Pacific/Yap' => 'Pacific/Chuuk',
        'UCT' => 'UTC',
        'US/East-Indiana' => 'America/Indiana/Indianapolis',
        'Universal' => 'UTC',
        'Zulu' => 'UTC',
    ];

    /**
     * The name PHP lists for $timezone, or $timezone itself when it is usable but has no listed name,
     * or null when it is not a timezone at all.
     *
     * Never returns null for a constructible zone: every caller falls back to America/New_York on
     * null, so an unmapped-but-valid alias (Etc/GMT-3) must survive rather than be silently replaced.
     *
     * Takes mixed because callers hand it request input, where `timezone[]=x` arrives as an array.
     */
    public static function canonicalize(mixed $timezone): ?string
    {
        $timezone = is_string($timezone) ? trim($timezone) : '';

        if ($timezone === '') {
            return null;
        }

        static $resolved = [];

        if (array_key_exists($timezone, $resolved)) {
            return $resolved[$timezone];
        }

        return $resolved[$timezone] = self::resolve($timezone);
    }

    /**
     * Every alias whose target is listed by the running PHP, for the browser-side copy of the map.
     *
     * Filtered here rather than in JS so the create form proposes exactly what canonicalize() would
     * store: on an older tzdata without Europe/Kyiv the server keeps Europe/Kiev, and so must the page.
     */
    public static function aliasMap(): array
    {
        $map = [];

        foreach (array_keys(self::ALIASES) as $alias) {
            $canonical = self::canonicalize($alias);

            if ($canonical !== null && $canonical !== $alias) {
                $map[$alias] = $canonical;
            }
        }

        return $map;
    }

    /**
     * The same list the timezone <select>s render, so a stored value can be checked against it.
     */
    public static function listed(): array
    {
        static $listed = null;

        return $listed ??= timezone_identifiers_list();
    }

    private static function resolve(string $timezone): ?string
    {
        $listed = self::listed();

        if (in_array($timezone, $listed, true)) {
            return $timezone;
        }

        // DateTimeZone accepts any letter case; the select and the validation rule do not.
        static $byLower = null;
        $byLower ??= array_combine(array_map('strtolower', $listed), $listed);

        if (isset($byLower[strtolower($timezone)])) {
            return $byLower[strtolower($timezone)];
        }

        foreach (self::ALIASES as $alias => $target) {
            if (strcasecmp($alias, $timezone) === 0 && in_array($target, $listed, true)) {
                return $target;
            }
        }

        // Region/City aliases only. ICU also "canonicalizes" abbreviations - PST to
        // America/Los_Angeles - which swaps a fixed offset for a zone with daylight saving and would
        // move every stored time by an hour in summer. Those fall through unchanged instead.
        if (str_contains($timezone, '/') && class_exists(\IntlTimeZone::class)) {
            $canonical = \IntlTimeZone::getCanonicalID($timezone);

            if (is_string($canonical) && in_array($canonical, $listed, true)) {
                return $canonical;
            }
        }

        try {
            new \DateTimeZone($timezone);
        } catch (\Throwable $e) {
            return null;
        }

        return $timezone;
    }
}
