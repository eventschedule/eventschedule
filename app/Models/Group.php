<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'color',
    ];

    /**
     * A URL-safe, non-empty, per-schedule-unique slug for a sub-schedule.
     *
     * Str::slug() on its own returns "" for Hebrew, Arabic vowels, CJK and anything else it
     * cannot transliterate, which broke three things at once: the slug is part of the guest
     * URL, `unique(['slug','role_id'])` rejects a second empty one outright, and the calendar
     * filter used the slug as its <option> value where "" collides with "Show all".
     *
     * Mirrors Role::cleanSubdomain()'s fallback chain, minus the Gemini call - every caller
     * that can translate already passes $nameEn, and the model should not make network
     * requests. Never returns an empty string.
     */
    public static function cleanSlug(int $roleId, ?string $name, ?string $nameEn = null, ?string $preferred = null, ?int $ignoreId = null): string
    {
        $slug = '';

        // A slug the user typed wins, then the English name (readable), then the original.
        foreach ([$preferred, $nameEn, $name] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            $attempt = Str::slug($candidate);

            if ($attempt !== '' && ! Role::isLossySlug($candidate, $attempt)) {
                $slug = $attempt;
                break;
            }
        }

        // Romanize instead: הופעות -> hwpaawt, 東京 -> dong-jing.
        if ($slug === '') {
            foreach ([$name, $nameEn, $preferred] as $candidate) {
                if (trim((string) $candidate) === '') {
                    continue;
                }

                $slug = Str::slug(Role::transliterateToAscii($candidate));

                if ($slug !== '') {
                    break;
                }
            }
        }

        if ($slug === '') {
            $slug = 'group-'.strtolower(Str::random(6));
        }

        $slug = Str::limit($slug, 180, '');

        // unique(['slug','role_id']) is a hard constraint, and two different non-Latin names
        // can romanize to the same thing, so disambiguate rather than throw.
        $base = $slug;
        $suffix = 1;

        while (DB::table('groups')
            ->where('role_id', $roleId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function encodeId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (showing_translation($this))) {
            $value = $this->name_en;
        }

        return $value;
    }
}
