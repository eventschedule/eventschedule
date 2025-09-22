<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $slugCounts = DB::table('events')
            ->select('slug', DB::raw('count(*) as aggregate'))
            ->groupBy('slug')
            ->pluck('aggregate', 'slug')
            ->toArray();

        $assigned = [];

        Event::query()->chunkById(100, function ($events) use (&$slugCounts, &$assigned) {
            foreach ($events as $event) {
                $originalSlug = $event->slug;

                if ($originalSlug !== null) {
                    $slugCounts[$originalSlug] = max(($slugCounts[$originalSlug] ?? 0) - 1, 0);
                }

                $baseSource = $originalSlug ?: ($event->name_en ?: $event->name);
                $baseSlug = Str::slug($baseSource ?? '') ?: Str::lower(Str::random(8));
                $candidate = $baseSlug;
                $suffix = 1;

                while (isset($assigned[$candidate]) || (($slugCounts[$candidate] ?? 0) > 0 && $candidate !== $originalSlug)) {
                    $candidate = $baseSlug . '-' . $suffix;
                    $suffix++;
                }

                if ($candidate !== $event->slug) {
                    $event->slug = $candidate;
                    $event->saveQuietly();
                }

                $assigned[$candidate] = true;
            }
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_slug_unique');
            $table->index('slug');
        });
    }
};
