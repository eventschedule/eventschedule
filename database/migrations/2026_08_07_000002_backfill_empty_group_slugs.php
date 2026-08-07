<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Sub-schedules created before Group::cleanSlug() existed got a bare Str::slug(), which
     * returns "" for Hebrew, CJK and anything else it cannot transliterate. An empty slug is
     * part of the guest URL, and it collides with the "Show all" option in the calendar
     * filter, so those sub-schedules could not be linked to or filtered by.
     *
     * unique(['slug','role_id']) means a role can only ever hold one such row, so there is at
     * most one per schedule to repair - but the loop below still checks, because a romanized
     * slug can collide with a sibling that already has one.
     *
     * Deliberately self-contained rather than calling Group::cleanSlug(): a migration has to
     * keep behaving the same however the application code later changes.
     */
    public function up(): void
    {
        $romanize = function (?string $name, ?string $nameEn): string {
            foreach ([$nameEn, $name] as $candidate) {
                $candidate = trim((string) $candidate);

                if ($candidate === '') {
                    continue;
                }

                $slug = Str::slug($candidate);

                if ($slug === '' && class_exists(\Transliterator::class)) {
                    $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');

                    if ($transliterator) {
                        $romanized = $transliterator->transliterate($candidate);
                        $slug = $romanized === false ? '' : Str::slug($romanized);
                    }
                }

                if ($slug !== '') {
                    return Str::limit($slug, 180, '');
                }
            }

            return 'group-'.strtolower(Str::random(6));
        };

        DB::table('groups')
            ->where(function ($query) {
                $query->whereNull('slug')->orWhere('slug', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($groups) use ($romanize) {
                foreach ($groups as $group) {
                    $base = $romanize($group->name, $group->name_en);
                    $slug = $base;
                    $suffix = 1;

                    while (DB::table('groups')
                        ->where('role_id', $group->role_id)
                        ->where('slug', $slug)
                        ->where('id', '!=', $group->id)
                        ->exists()
                    ) {
                        $suffix++;
                        $slug = $base.'-'.$suffix;
                    }

                    DB::table('groups')->where('id', $group->id)->update(['slug' => $slug]);
                }
            });
    }

    public function down(): void
    {
        // Restoring an unusable slug would be a regression, so this is deliberately one-way.
    }
};
