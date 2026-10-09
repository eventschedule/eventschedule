<?php
// The language The Indigo Room's page is shown in. A schedule page renders in the schedule's own
// language (the owner's setting), so the film's eight-language plates are the same page with this
// one column changed between shots, and put back to English after.
//   php run.php <copy> fixture/lang.php es
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

$lang = $argv[3] ?? 'en';
if (! array_key_exists($lang, config('app.supported_languages'))) { fwrite(STDERR, "not a supported language: $lang\n"); exit(1); }
if (DB::connection()->getDatabaseName() !== 'eventschedule_test_launchfilm') { fwrite(STDERR, "refusing: this is not the film's schema\n"); exit(1); }
DB::table('roles')->where('subdomain', 'indigo-room')->update(['language_code' => $lang]);
Artisan::call('cache:clear');
echo "indigo-room language: $lang\n";
