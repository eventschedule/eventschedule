<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keep the scheduled Events graphic email reading as it does today.
 *
 * A schedule with no caption wording of its own gets the default one, and the default gained a
 * {short_description} line when the Events graphic page was rebuilt (the page had carried that
 * line in a copy of its own since 2026-02-06, while the server's default lacked it). The page
 * posts the wording on every Save, so almost every schedule has one stored and is untouched by
 * that. The ones that do not are a schedule that switched the email on in the four days before
 * the wording box existed (2026-01-22 to 2026-01-26) and has not saved since, and one that
 * emptied the box and saved: their email would have grown a line per event overnight, with
 * nobody having asked for it.
 *
 * So for a schedule whose email is switched ON and whose wording is empty, the wording its email
 * uses today is written down. Nothing else is touched: a schedule with its own wording keeps it, a
 * schedule with the email off has nothing going out (and its next Save stores what the page
 * shows), and a value that is not a settings object is left exactly as it is.
 *
 * Through the query builder, not the model: a Role save runs the geocoding hook and moves
 * updated_at, which the sitemap publishes. Safe to run twice (a row it has written no longer has
 * an empty wording).
 */
return new class extends Migration
{
    /** EventTextGenerator::getDefaultTemplate() as it was until 2026-10-08. Never read it from the class. */
    private const WORDING_UNTIL_NOW = "*{day_name}* {date_dmy} | {time}\n*{event_name}*:\n{venue} | {city}\n{url}";

    public function up(): void
    {
        $kept = 0;

        DB::table('roles')
            ->whereNotNull('graphic_settings')
            ->select('id', 'graphic_settings')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$kept) {
                foreach ($rows as $row) {
                    $settings = json_decode((string) $row->graphic_settings, true);

                    if (! is_array($settings) || empty($settings['enabled'])) {
                        continue;
                    }

                    $wording = $settings['text_template'] ?? '';
                    if (! is_string($wording) && $wording !== null) {
                        continue;
                    }
                    if (trim((string) $wording) !== '') {
                        continue;
                    }

                    $settings['text_template'] = self::WORDING_UNTIL_NOW;
                    DB::table('roles')->where('id', $row->id)->update(['graphic_settings' => json_encode($settings)]);
                    $kept++;
                }
            });

        if ($kept) {
            Log::info('keep_graphic_email_wording: wrote down the wording in use', ['schedules' => $kept]);
        }
    }

    public function down(): void
    {
        // Nothing to undo: the wording written is the one those emails were already sent with.
    }
};
