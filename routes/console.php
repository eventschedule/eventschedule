<?php

use App\Jobs\ProcessScheduledNewsletters;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time' => 120,
        '--tries' => 3,
    ]);
})->everyMinute()->name('process-queue')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

// Keep in sync with AppController::translateData(). The per-job cap, the cooldown and the
// per-job error handling live inside the command, so this rail's five-minute cadence and
// translateData's one-minute cadence produce identical retry behaviour.
Schedule::call(function () {
    Artisan::call('app:retry-failed-jobs');
})->everyFiveMinutes()->name('retry-failed-jobs')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:release-tickets');
})->hourly()->name('app-release-tickets')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:expire-waitlist');
})->hourly()->name('app-expire-waitlist')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

// Every five minutes: this is what makes curator sources correct, rather than the single
// hook in EventRepo::saveEvent() which only makes them immediate. Two set queries over
// schedules that actually have sources, so a quiet install does almost no work.
Schedule::call(function () {
    Artisan::call('app:sync-curator-sources');
})->everyFiveMinutes()->name('app-sync-curator-sources')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

// Every 15 minutes rather than hourly: the command stops cleanly at its budget and resumes with
// the longest-waiting rows, so more frequent short runs drain the queue faster than one long run
// that may be killed. withoutOverlapping() is given an explicit expiry because its default is
// 1440 minutes - a hard-killed run would otherwise leave the mutex in place for a full day.
Schedule::call(function () {
    // A lock scoped to app:translate ALONE, shared with AppController::translateData(), because
    // withoutOverlapping() only serialises the scheduler against itself: an install running BOTH
    // a crontab schedule:run and an external cron against /translate_data ran two copies side by
    // side, and since both order rows longest-waiting first, every translation in the overlap was
    // bought from the AI provider twice.
    //
    // NOT translate_data_lock. That one is held by translateData() around its entire
    // once-a-minute chain - newsletters, queue:work, every reminder - so taking it here for a
    // 240s translation would stall all of that for minutes at a time on exactly the installs
    // this is meant to help. The TTL is a backstop for a hard-killed run, sized just above the
    // command's own budget rather than at a quarter of an hour.
    $lock = Cache::lock('app_translate_lock', 600);

    if (! $lock->get()) {
        return;
    }

    try {
        Artisan::call('app:translate', ['--max-seconds' => config('usage.translation_max_seconds', 240)]);
    } finally {
        $lock->release();
    }
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

// withoutOverlapping() is not decoration here: this command initiates card charges, so two
// concurrent runs would attempt the same installment. The Stripe idempotency key is the second
// line of defence; this is the first.
Schedule::call(function () {
    // A lock scoped to app:charge-installments ALONE, shared with AppController::translateData(),
    // for the same reason app_translate_lock exists: withoutOverlapping() only serialises the
    // scheduler against itself, so an install running BOTH a crontab schedule:run and an external
    // cron against /translate_data ran two copies side by side. For translations that bought the
    // same work twice; here it is two charge loops against the same cards, which is worth a
    // stronger guard than the row claim and the idempotency key alone.
    //
    // The TTL is a backstop for a hard-killed run, sized above the command's own 120s budget and
    // well inside the hourly gap, so at worst one hour is skipped.
    $lock = Cache::lock('app_charge_installments_lock', 300);

    if (! $lock->get()) {
        return;
    }

    try {
        Artisan::call('app:charge-installments');
    } finally {
        $lock->release();
    }
})->hourly()->name('charge-installments')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

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

// Hourly rather than daily: the first nudge is due one hour after signup, and 91% of the
// people who activate do so inside that first hour - a daily pass would reach the rest a day
// late, long after the moment has gone.
Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:send-onboarding-nudges', ['--apply' => true]);
    }
})->hourly()->name('send-onboarding-nudges')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

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
