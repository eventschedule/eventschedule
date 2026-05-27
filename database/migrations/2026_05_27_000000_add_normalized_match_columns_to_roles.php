<?php

use App\Utils\GeminiUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name_normalized')->nullable()->after('name');
            $table->string('name_en_normalized')->nullable()->after('name_en');
            $table->string('city_normalized')->nullable()->after('city');
            $table->string('address1_normalized')->nullable()->after('address1');
            $table->string('address1_en_normalized')->nullable()->after('address1_en');

            $table->index(['type', 'country_code', 'city_normalized'], 'roles_type_country_city_norm_idx');
            $table->index(['type', 'name_normalized'], 'roles_type_name_norm_idx');
        });

        // Backfill via the query builder (bypassing the Role::saving hook so we
        // don't fire Google geocoding for every existing row). chunkById manages
        // its own ordering by id.
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
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_type_country_city_norm_idx');
            $table->dropIndex('roles_type_name_norm_idx');
            $table->dropColumn([
                'name_normalized',
                'name_en_normalized',
                'city_normalized',
                'address1_normalized',
                'address1_en_normalized',
            ]);
        });
    }
};
