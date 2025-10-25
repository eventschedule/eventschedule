<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('translations')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $categories = config('app.event_categories', []);

        if (! empty($categories)) {
            $now = Carbon::now();
            $supportedLocales = config('app.supported_languages', []);
            $fallbackLocale = config('app.fallback_locale');

            if ($fallbackLocale && ! in_array($fallbackLocale, $supportedLocales, true)) {
                $supportedLocales[] = $fallbackLocale;
            }

            if (! in_array('en', $supportedLocales, true)) {
                $supportedLocales[] = 'en';
            }

            $existingSlugs = [];

            foreach ($categories as $id => $name) {
                $baseSlug = Str::slug($name);
                $baseSlug = $baseSlug !== '' ? $baseSlug : 'event-type-' . $id;
                $slug = $baseSlug;
                $counter = 2;

                while (in_array($slug, $existingSlugs, true)) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $existingSlugs[] = $slug;

                $translations = [];
                $translationKey = Str::of($name)
                    ->lower()
                    ->replace(' & ', '_&_')
                    ->replace(' ', '_')
                    ->toString();

                foreach ($supportedLocales as $locale) {
                    $translation = Lang::get("messages.{$translationKey}", [], $locale);

                    if (! is_string($translation) || $translation === "messages.{$translationKey}") {
                        $translation = $name;
                    }

                    $translations[$locale] = $translation;
                }

                DB::table('event_types')->insert([
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                    'sort_order' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('event_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::dropIfExists('event_types');
    }
};
