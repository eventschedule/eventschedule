<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Event::withoutEvents(function () {
            $duplicateSlugs = Event::query()
                ->select('slug')
                ->groupBy('slug')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('slug');

            foreach ($duplicateSlugs as $slug) {
                $events = Event::where('slug', $slug)
                    ->orderBy('id')
                    ->get();

                foreach ($events->skip(1) as $event) {
                    $source = $event->name_en ?: $event->name ?: $slug ?: (string) $event->id;
                    $event->slug = Event::generateUniqueSlug($source, $event->id);
                    $event->saveQuietly();
                }
            }
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug', 'events_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_slug_unique');
        });
    }
};
