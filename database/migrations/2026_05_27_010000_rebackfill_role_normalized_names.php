<?php

use App\Utils\GeminiUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Re-run the *_normalized backfill against the (now Hebrew/Arabic-aware)
        // GeminiUtils::normalizeForMatch(). Any row backfilled by the original
        // migration before the nikud / geresh fix had an incorrect normalized
        // value, so existing imports wouldn't match incoming AI-parsed venues.
        // Bypasses Role::saving so we don't fire geocoding for every row.
        DB::table('roles')
            ->select(['id', 'name', 'name_en', 'city', 'address1', 'address1_en'])
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('roles')->where('id', $row->id)->update([
                        'name_normalized' => GeminiUtils::normalizeForMatch($row->name) ?: null,
                        'name_en_normalized' => GeminiUtils::normalizeForMatch($row->name_en) ?: null,
                        'city_normalized' => GeminiUtils::normalizeForMatch($row->city) ?: null,
                        'address1_normalized' => GeminiUtils::normalizeForMatch($row->address1) ?: null,
                        'address1_en_normalized' => GeminiUtils::normalizeForMatch($row->address1_en) ?: null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // No-op: re-backfill is idempotent and the columns themselves stay put.
    }
};
