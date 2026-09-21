<?php

use App\Utils\JsonUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Repair JSON settings that a backup restore stored double-encoded.
 *
 * BackupService exports these columns as their raw JSON text, and importRole()/importEvent()
 * assigned that text straight back to attributes with an `array` cast - which JSON-encodes any
 * non-null value, a JSON string included. A restored schedule therefore held `"{\"key\":...}"`,
 * which reads back as a string: getEventCustomFields(): array throws on the public schedule page
 * and the settings page, gift card checkout calls array_map() on a string, and custom labels
 * quietly fall back to their defaults. The importer decodes first now; this fixes the rows that
 * were restored before it did.
 *
 * Only values that decode to an array are rewritten. A valid object or array never starts with a
 * quote, so the text columns are narrowed with LIKE '"%'; the one JSON column is narrowed with
 * JSON_TYPE. Anything that does not decode cleanly is left exactly as it is.
 */
return new class extends Migration
{
    private const TEXT_COLUMNS = [
        'roles' => ['event_custom_fields', 'custom_labels'],
        'events' => ['custom_fields', 'custom_field_values'],
    ];

    public function up(): void
    {
        foreach (self::TEXT_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->unwrap(
                    DB::table($table)->whereNotNull($column)->where($column, 'like', '"%'),
                    $table,
                    $column
                );
            }
        }

        $this->unwrap(
            DB::table('roles')->whereRaw("JSON_TYPE(gift_card_amounts) = 'STRING'"),
            'roles',
            'gift_card_amounts'
        );
    }

    public function down(): void
    {
        // Nothing to undo: the rewritten values are the ones the application always meant to store.
    }

    /**
     * Logged per row, and per SKIPPED row especially.
     *
     * This runs once, against a production database nobody can reach from a dev machine, and it
     * rewrites data in place. Without a log there is afterwards no way to tell which rows were
     * repaired and which were left broken, because a repaired value stops matching the selector that
     * found it. A row that fails to decode is the one worth having a record of: it is a value the
     * app could not make sense of, it is still in the database, and nothing else will flag it.
     */
    private function unwrap($query, string $table, string $column): void
    {
        $rewritten = 0;
        $skipped = 0;

        $query->select('id', $column)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table, $column, &$rewritten, &$skipped) {
                foreach ($rows as $row) {
                    $decoded = JsonUtils::decodeToArray($row->{$column});

                    if ($decoded === null) {
                        $skipped++;
                        Log::warning('unwrap_double_encoded_json: left as-is, could not decode', [
                            'table' => $table, 'column' => $column, 'id' => $row->id,
                        ]);

                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([$column => json_encode($decoded)]);
                    $rewritten++;
                }
            });

        if ($rewritten || $skipped) {
            Log::info('unwrap_double_encoded_json: finished a column', [
                'table' => $table, 'column' => $column, 'rewritten' => $rewritten, 'skipped' => $skipped,
            ]);
        }
    }
};
