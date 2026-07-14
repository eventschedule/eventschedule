<?php

namespace App\Console\Commands;

use App\Services\TranslationOverrideService;
use Illuminate\Console\Command;

/**
 * Rebuilds the published translation-override files from the database.
 * The database is the source of truth; the files under
 * config('app.lang_overrides_path') are derived state.
 *
 * Run this after restoring a database backup or when cloning the app to a
 * new server (the files are server-local). After bulk translation changes,
 * restart queue workers so long-running processes pick up the new strings.
 *
 * Hand-made override files for OTHER groups (validation.php, auth.php, custom
 * groups) are left untouched. The three managed files (messages, accessibility,
 * marketing) hold flat string keys only; nested-array values in a hand-made
 * override of those files are not represented in the database and are dropped
 * when the file is rebuilt - keep nested structures in their own group files.
 */
class PublishTranslationOverrides extends Command
{
    protected $signature = 'translations:publish';

    protected $description = 'Rebuild translation override files in storage from the database';

    public function handle(TranslationOverrideService $service): int
    {
        $counts = $service->publishAll();

        $this->info("Published {$counts['written']} translation override files, removed {$counts['deleted']} stale files.");

        return Command::SUCCESS;
    }
}
