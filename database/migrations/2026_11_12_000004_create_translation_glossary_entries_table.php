<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A schedule's translation glossary (Details > Translation glossary, App\Services\TranslationGlossary):
 * names that automatic translation must keep exactly as written, and expressions with a fixed
 * translation. A table of its own, not a column on `roles`, because `roles` sits at MySQL's
 * 65,535-byte row limit (see 2026_10_07_000005_create_venue_map_settings_table.php).
 *
 * `translation` NULL means "keep the term as written". At most TranslationGlossary::MAX_ENTRIES rows
 * a schedule, each term and translation at most MAX_LENGTH characters; the unique key compares
 * under the table's collation, so "Drinx" and "drinx" are one term.
 */
return new class extends Migration
{
    public function up(): void
    {
        // A short lock wait (the foreign key takes a metadata lock on `roles`), and safe to run
        // again after a run that stopped half way, as 2026_11_11_000000 is.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            if (Schema::hasTable('translation_glossary_entries')) {
                return;
            }

            try {
                Schema::create('translation_glossary_entries', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                    $table->string('term', 100);
                    $table->string('translation', 100)->nullable();
                    $table->unsignedSmallInteger('position')->default(0);
                    $table->timestamps();

                    $table->unique(['role_id', 'term']);
                });
            } catch (Throwable $e) {
                DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
                try {
                    Schema::dropIfExists('translation_glossary_entries');
                } catch (Throwable) {
                }

                throw $e;
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_glossary_entries');
    }
};
