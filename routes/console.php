<?php

use App\Jobs\ProcessScheduledNewsletters;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time' => 120,
        '--tries' => 3,
    ]);
})->everyMinute()->name('process-queue')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    $failedCount = DB::table('failed_jobs')->count();
    if ($failedCount > 0) {
        Log::warning("Found {$failedCount} failed jobs, retrying up to 50");
        $failedIds = DB::table('failed_jobs')->orderBy('failed_at')->limit(50)->pluck('uuid');
        foreach ($failedIds as $uuid) {
            Artisan::call('queue:retry', ['id' => [$uuid]]);
        }
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => 60,
            '--tries' => 3,
        ]);
    }
})->everyFiveMinutes()->name('retry-failed-jobs')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:release-tickets');
})->hourly()->name('app-release-tickets')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:expire-waitlist');
})->hourly()->name('app-expire-waitlist')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

// Every 15 minutes rather than hourly: the command stops cleanly at its budget and resumes with
// the longest-waiting rows, so more frequent short runs drain the queue faster than one long run
// that may be killed. withoutOverlapping() is given an explicit expiry because its default is
// 1440 minutes - a hard-killed run would otherwise leave the mutex in place for a full day.
Schedule::call(function () {
    Artisan::call('app:translate', ['--max-seconds' => config('usage.translation_max_seconds', 240)]);
})->everyFifteenMinutes()->name('app-translate')->withoutOverlapping(20)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:refresh-webhooks');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('microsoft:refresh-webhooks');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (! config('app.hosted')) {
        Artisan::call('app:import-curator-events');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-graphic-emails');
})->hourly()->name('app-send-graphic-emails')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-feedback-requests');
})->hourly()->name('send-feedback-requests')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-appointment-reminders');
})->hourly()->name('send-appointment-reminders')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('federation:push');
})->hourly()->name('federation-push')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('federation:maintain');
})->hourly()->name('federation-maintain')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('caldav:sync');
})->everyFifteenMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('microsoft:sync');
})->everyFifteenMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:sync');
})->everyFifteenMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:setup-demo');
    }
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:generate-sub-audience-blog');
    }
})->daily()->at('03:00')->appendOutputTo(storage_path('logs/sub-audience-blog.log'));

Schedule::call(new ProcessScheduledNewsletters)->everyMinute()->name('process-scheduled-newsletters')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('audit:prune');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:cleanup-webhook-deliveries');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:cleanup-backups');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (\App\Services\MetaAdsService::isBoostConfigured()) {
        Artisan::call('boost:sync');
    }
})->everyFifteenMinutes()->name('boost-sync')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('boost:expire-pending');
})->everyFifteenMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

// Separate from boost-sync above: that one is gated on Meta being configured, so an
// operator running only the on-network promotions engine would never see it fire.
//
// Gated on the deploy-time master switch, NOT on PromotionService::isEnabled(). Campaigns
// are prepaid, and the command settles and refunds them; gating on "is the network serving"
// would mean switching the network off stops settlement and strands advertisers' money,
// which is exactly what SyncPromotions::handle() was written to prevent. handle() reads
// isEnabled() itself to decide which steps still apply.
Schedule::call(function () {
    if (\App\Services\AdsService::isEnabled()) {
        Artisan::call('promo:sync');
    }
})->everyFifteenMinutes()->name('promo-sync')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:sync-domain-statuses');
    }
})->everyFiveMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:send-subscription-reminders');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:notify-request-changes');
    Artisan::call('app:notify-fan-content-changes');
    Artisan::call('app:notify-poll-option-changes');
})->daily()->at('12:00')->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-carpool-reminders');
})->hourly()->name('app-send-carpool-reminders')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:generate-daily-blog-post');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:process-referral-credits');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:update-geoip');
})->monthly()->appendOutputTo(storage_path('logs/scheduler.log'));
