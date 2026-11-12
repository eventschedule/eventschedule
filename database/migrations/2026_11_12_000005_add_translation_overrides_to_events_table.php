<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which of an event's translations a person corrected by hand (the event form's Translation row,
 * EventTranslationEdits). A JSON object keyed by field, `{"name": {"source": "<sha1>", "at": "..."}}`,
 * where `source` is a hash of the original text the correction was made against: when the original
 * changes, the correction is kept and the form says the original changed since (Event::
 * translationIsStale()). Null for every event nobody corrected, which is every event today, so
 * nothing changes for them. TEXT and not JSON, and one column rather than a flag per field: the
 * events row is wide, and MariaDB, which selfhosters may run, reads JSON as an alias anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'translation_overrides')) {
            Schema::table('events', function (Blueprint $table) {
                $table->text('translation_overrides')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'translation_overrides')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('translation_overrides');
            });
        }
    }
};
