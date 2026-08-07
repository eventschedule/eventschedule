<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * `2025_01_25_212106_add_quantity` backfilled every existing row with a bare
     * `Str::slug($event->name)` and then made the column non-nullable. Str::slug returns "" for
     * Hebrew, CJK and anything else it cannot transliterate, so pre-2025 events with non-Latin
     * names are still stored with an empty slug and have a broken guest URL
     * (`/{subdomain}//{id}` rather than `/{subdomain}/{slug}/{id}`).
     *
     * Only touches rows that are already broken. `events.slug` has no unique constraint and
     * `EventRepo::findEventBySlug()` disambiguates by date, so duplicates are normal here and
     * this introduces no new hazard.
     *
     * Deliberately self-contained rather than calling App\Utils\SlugUtils: a migration has to
     * keep behaving the same however the application code later changes.
     */
    public function up(): void
    {
        $romanize = function (string $name): string {
            $slug = Str::slug($name);

            if ($slug === '' && class_exists(\Transliterator::class)) {
                $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');

                if ($transliterator) {
                    $romanized = $transliterator->transliterate($name);
                    $slug = $romanized === false ? '' : Str::slug($romanized);
                }
            }

            return $slug !== '' ? Str::limit($slug, 180, '') : 'event-'.strtolower(Str::random(6));
        };

        DB::table('events')
            ->where(function ($query) {
                $query->whereNull('slug')->orWhere('slug', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($events) use ($romanize) {
                foreach ($events as $event) {
                    DB::table('events')->where('id', $event->id)->update([
                        'slug' => $romanize((string) $event->name),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Restoring an unreachable URL would be a regression, so this is one-way.
    }
};
