<?php

use App\Utils\TimezoneUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rewrites stored backward-compat timezone aliases (Asia/Calcutta, US/Eastern, Etc/UTC ...) to the
 * names PHP lists, the same mapping every write site now applies through TimezoneUtils.
 *
 * A schedule whose timezone was an alias could not be saved from the web form, and the timezone
 * selects had no option for it, so a profile save rewrote the user's timezone to Africa/Abidjan.
 *
 * events.timezone is included because Event::isOffTimezoneFor() compares strings: an event captured
 * as Asia/Calcutta under a schedule rewritten to Asia/Kolkata would read as off-timezone and put a
 * false banner on the schedule. An alias and its target share their rules, so no stored UTC instant
 * changes meaning. Abbreviations (PST, CEST) are deliberately NOT mapped - canonicalize() leaves them
 * alone, because their regional "equivalents" observe daylight saving and they do not.
 *
 * Raw query builder updates on purpose: they leave updated_at alone (the sitemap reads it as the
 * page's lastmod) and skip the Role and Event model hooks, none of which this change concerns.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'users' => 'timezone',
        'roles' => 'timezone',
        'events' => 'timezone',
        'sales' => 'guest_timezone',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            // Distinct values, not rows: a handful of strings covers the whole table, and it maps
            // the aliases ICU resolves (US/Eastern) as well as the explicit ones.
            $values = DB::table($table)->whereNotNull($column)->distinct()->pluck($column);

            foreach ($values as $value) {
                $canonical = TimezoneUtils::canonicalize($value);

                if ($canonical !== null && $canonical !== $value) {
                    DB::table($table)->where($column, $value)->update([$column => $canonical]);
                }
            }
        }
    }

    public function down(): void
    {
        // Not reversible: the aliases and their targets are the same zones, and nothing recorded
        // which spelling each row had.
    }
};
