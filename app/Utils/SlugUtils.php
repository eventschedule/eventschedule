<?php

namespace App\Utils;

use App\Models\Role;
use Illuminate\Support\Str;

class SlugUtils
{
    /**
     * Slugify text, romanizing it first if Str::slug() would throw it away.
     *
     * Str::slug() returns "" for Hebrew, CJK, Thai and anything else it cannot transliterate,
     * so anywhere its output becomes an identifier a non-Latin name silently collapses. This
     * adds the one missing step: fall back to ICU romanization (הופעות -> hwpwt, 東京 -> dong-jing)
     * before giving up.
     *
     * The richer generators - Role::cleanSubdomain(), Group::cleanSlug(),
     * AppointmentType::uniqueSlug() - do this plus an English-name preference, a Gemini
     * translation or a per-schedule uniqueness loop. Use one of those when the result is
     * stored as a unique identifier; this is for the simpler sites, where the caller supplies
     * its own uniqueness (a random suffix) or has none to enforce.
     *
     * @param  string  $fallback  returned when the text has no usable characters at all
     */
    public static function slugOrRomanize(?string $text, string $fallback = ''): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return $fallback;
        }

        $slug = Str::slug($text);

        if ($slug === '') {
            $slug = Str::slug(Role::transliterateToAscii($text));
        }

        return $slug !== '' ? $slug : $fallback;
    }
}
