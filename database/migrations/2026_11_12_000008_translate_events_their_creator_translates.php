<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Events with neither a venue nor a talent (an online event, any event a curator writes) were
 * read as English to English, whatever the schedule that wrote them translates into, and the
 * translation command marked each one done with empty strings. They follow their creator's
 * languages now (Event::languageCreator()), so the empty strings of those whose creator translates
 * go back to NULL and app:translate takes them up, past events excepted (TranslationQueue only ever
 * selects live ones). Query builder only: no model hook re-derives anything, and updated_at is left
 * alone. Nothing to undo: the next run would mark them again.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->join('roles', 'roles.id', '=', 'events.creator_role_id')
            ->whereColumn('roles.language_code', '!=', 'roles.translation_language_code')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('event_role')
                    ->join('roles as governing', 'governing.id', '=', 'event_role.role_id')
                    ->whereColumn('event_role.event_id', 'events.id')
                    ->whereIn('governing.type', ['venue', 'talent']);
            })
            ->where(function ($query) {
                $query->where('events.name_en', '')
                    ->orWhere('events.short_description_en', '')
                    ->orWhere('events.description_en', '');
            })
            ->orderBy('events.id')
            // Read first, written after: the writes change what the filter above matches, which
            // would make an offset walk skip rows.
            ->pluck('events.id')
            ->chunk(500)
            ->each(function ($chunk) {
                $ids = $chunk->all();
                DB::table('events')->whereIn('id', $ids)->where('name_en', '')->update(['name_en' => null]);
                DB::table('events')->whereIn('id', $ids)->where('short_description_en', '')->update(['short_description_en' => null]);
                DB::table('events')->whereIn('id', $ids)->where('description_en', '')->update(['description_en' => null, 'description_html_en' => null]);
                DB::table('events')->whereIn('id', $ids)->update(['translation_attempts' => 0]);
            });
    }

    public function down(): void
    {
        //
    }
};
