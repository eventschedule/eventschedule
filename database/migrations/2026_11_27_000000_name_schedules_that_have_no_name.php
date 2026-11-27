<?php

use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every schedule has a name from now on (Role::fillNameFromAddress()). The ones stored without
 * are venues made from an event that gave the place by its address alone, and the guest page of
 * one was a 500 (EVENTSCHEDULE-PHP-52). Each is named after its address as a new one would be.
 *
 * Written through the query builder: a model save would run the geocoding hook and move
 * updated_at, which the sitemap publishes as the page's <lastmod>.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where(fn ($q) => $q->whereNull('name')->orWhereRaw("TRIM(name) = ''"))
            ->select(['id', 'type', 'name', 'name_en', 'address1', 'address1_en', 'city'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    // The model's own rule, on an instance that is never saved.
                    $role = (new Role)->forceFill((array) $row);
                    $role->syncOriginal();
                    $role->fillNameFromAddress();

                    $update = ['name' => $role->name];
                    $normalized = GeminiUtils::normalizeForMatch($role->name);
                    $update['name_normalized'] = $normalized === '' ? null : $normalized;

                    if ($role->isDirty('name_en')) {
                        $update['name_en'] = $role->name_en;
                        $normalizedEn = GeminiUtils::normalizeForMatch($role->name_en);
                        $update['name_en_normalized'] = $normalizedEn === '' ? null : $normalizedEn;
                    }

                    DB::table('roles')->where('id', $row->id)->update($update);
                }
            });
    }

    public function down(): void
    {
        // The names given here cannot be told from ones typed afterwards; nothing to undo.
    }
};
