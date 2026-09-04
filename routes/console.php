<?php

use App\Jobs\ProcessScheduledNewsletters;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

/*
 * Two conventions hold for every entry in this file, and both are enforced by
 * tests/Feature/SchedulerHealthTest.php.
 *
 * 1. Every entry has a ->name(). CallbackEvent::withoutOverlapping() throws a LogicException
 *    without one, and CallbackEvent::shouldSkipDueToOverlapping() short-circuits on the same
 *    property - so an unnamed entry cannot have overlap protection at all. That was survivable
 *    while hosted drove cron through AppController::translateData(), which serialises its whole
 *    chain under one lock. It is not survivable under schedule:work, which starts a fresh
 *    schedule:run every minute WITHOUT waiting for the previous one to finish.
 *
 * 2. Every withoutOverlapping() is given an explicit expiry in minutes. The default is 1440 - a
 *    full day - and the mutex is released in a finally block, which a SIGKILL or an OOM does not
 *    run. App Platform SIGTERMs the worker on every deploy, so one bad kill would otherwise stop
 *    that entry for 24 hours, silently.
 *
 *    Size it just above the entry's own worst-case runtime, then round up: a run that overruns its
 *    expiry lets a second copy start, which is the thing this prevents, so erring short is the
 *    dangerous direction. In practice that lands around twice the gap to the next run for the
 *    frequent entries, which is the cost of one or two skipped runs after a hard kill rather than
 *    a day of them. /admin/queue flags a skip streak that outlasts this number, so a stranded
 *    mutex is visible either way - /admin/queue calls a task never_finished once it has gone
 *    this long plus one interval without COMPLETING - which is what makes rounding up safe.
 */

// --sleep=0 because Worker::daemon() sleeps BEFORE stopIfNecessary() evaluates stopWhenEmpty, so
// the default of 3 burns three seconds of every tick on an empty queue - which is the normal state.
// Idle pacing is irrelevant here: the worker stops on its first empty poll either way.
//
// The overlap expiry is 5, not the 20 the rest of the frequent entries carry, because this one is
// different in kind: nothing else on the install drains the queue, so a stranded mutex here stops
// ALL queued work rather than one job. And it is genuinely reachable - a queue:work job timeout
// calls posix_kill(getmypid(), SIGKILL), and getmypid() is the schedule:run process itself, so
// Event::finish()'s finally never runs and the mutex sits for its full TTL. 5 is just above the
// entry's own --max-time=120 worst case, which is what the rule at the top of this file asks for.
Schedule::call(function () {
    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--sleep' => 0,
        '--max-time' => 120,
        '--tries' => 3,
    ]);
})->everyMinute()->name('process-queue')->withoutOverlapping(5)->appendOutputTo(storage_path('logs/scheduler.log'));

// Keep in sync with AppController::translateData(). The per-job cap, the cooldown and the
// per-job error handling live inside the command, so this rail's five-minute cadence and
// translateData's one-minute cadence produce identical retry behaviour.
Schedule::call(function () {
    Artisan::call('app:retry-failed-jobs');
})->everyFiveMinutes()->name('retry-failed-jobs')->withoutOverlapping(10)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:release-tickets');
})->hourly()->name('app-release-tickets')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:expire-waitlist');
})->hourly()->name('app-expire-waitlist')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

// Every five minutes: this is what makes curator sources correct, rather than the single
// hook in EventRepo::saveEvent() which only makes them immediate. Two set queries over
// schedules that actually have sources, so a quiet install does almost no work.
Schedule::call(function () {
    Artisan::call('app:sync-curator-sources');
})->everyFiveMinutes()->name('app-sync-curator-sources')->withoutOverlapping(10)->appendOutputTo(storage_path('logs/scheduler.log'));

// Every 15 minutes rather than hourly: the command stops cleanly at its budget and resumes with
// the longest-waiting rows, so more frequent short runs drain the queue faster than one long run
// that may be killed.
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

// Keeps the cached "latest release" warm so the admin panel's App Update badge never has to
// make an outbound call to GitHub during a page render. Keep in sync with
// AppController::translateData(). No-ops on nexus.
Schedule::call(function () {
    Artisan::call('app:check-version');
})->daily()->name('app-check-version')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

// Republish the admin translation manager's overrides from the database onto this
// container's disk. config('app.lang_overrides_path') defaults to storage/app/lang, which is
// per-container and does not survive a deploy - so on any host with more than one container,
// or any host that redeploys, a process that never republishes serves the base English strings
// instead of the operator's edits. Cheap and idempotent: it rewrites files from rows.
// Keep in sync with AppController::translateData().
// Passes --no-prune: publishAll()'s prune deletes any managed file not backed by a DB row, and
// adoptFileOverrides() swallows a parse error and creates none - so a hand-made override file
// with one typo would be deleted by cron within a day. Unattended runs write, never delete.
Schedule::call(function () {
    Artisan::call('translations:publish', ['--no-prune' => true]);
})->daily()->name('translations-publish')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:refresh-webhooks');
})->daily()->name('google-refresh-webhooks')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('microsoft:refresh-webhooks');
})->daily()->name('microsoft-refresh-webhooks')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (! config('app.hosted')) {
        Artisan::call('app:import-curator-events');
    }
})->daily()->name('app-import-curator-events')->withoutOverlapping(360)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-graphic-emails');
})->hourly()->name('app-send-graphic-emails')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-feedback-requests');
})->hourly()->name('send-feedback-requests')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-appointment-reminders');
})->hourly()->name('send-appointment-reminders')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

// Keep in sync with AppController::translateData(). Deliberately NOT gated on config('app.hosted'):
// this keeps a promise made to a GUEST, and on selfhost the subscribe panel is the only capture
// surface a signed-out visitor ever sees, so selfhost is where an unkept promise is most visible.
//
// Hourly, but a schedule cannot be announced to more often than
// usage.audience_announcement_min_hours (72 by default) - the cadence floor is what makes
// subscription_confirm_cadence ("at most one email every few days") true, not this frequency.
// Hourly is what makes a same-day publish reach its audience the same day.
//
// Was hand-run only until the seven hazards in the command's docblock were closed; it claims each
// schedule's window with a conditional UPDATE before sending, so running on both rails at once
// cannot double-send.
Schedule::call(function () {
    Artisan::call('app:send-event-announcements', ['--apply' => true]);
})->hourly()->name('send-event-announcements')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

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
})->hourly()->name('charge-installments')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('federation:push');
})->hourly()->name('federation-push')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('federation:maintain');
})->hourly()->name('federation-maintain')->withoutOverlapping(45)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('caldav:sync');
})->everyFifteenMinutes()->name('caldav-sync')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('microsoft:sync');
})->everyFifteenMinutes()->name('microsoft-sync')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:sync');
})->everyFifteenMinutes()->name('google-sync')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:setup-demo');
    }
})->hourly()->name('app-setup-demo')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:generate-sub-audience-blog');
    }
})->daily()->at('03:00')->name('app-generate-sub-audience-blog')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/sub-audience-blog.log'));

Schedule::call(new ProcessScheduledNewsletters)->everyMinute()->name('process-scheduled-newsletters')->withoutOverlapping(10)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('audit:prune');
})->daily()->name('audit-prune')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:cleanup-webhook-deliveries');
})->daily()->name('app-cleanup-webhook-deliveries')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:cleanup-backups');
})->daily()->name('app-cleanup-backups')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

// Keep in sync with AppController::translateData(). Daily is plenty: a video having embedding
// switched off is not urgent, and the command no-ops without a YouTube key.
Schedule::call(function () {
    Artisan::call('app:recheck-video-embeds');
})->daily()->name('recheck-video-embeds')->withoutOverlapping(120)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (\App\Services\MetaAdsService::isBoostConfigured()) {
        Artisan::call('boost:sync');
    }
})->everyFifteenMinutes()->name('boost-sync')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('boost:expire-pending');
})->everyFifteenMinutes()->name('boost-expire-pending')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

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
})->everyFifteenMinutes()->name('promo-sync')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:sync-domain-statuses');
    }
})->everyFiveMinutes()->name('app-sync-domain-statuses')->withoutOverlapping(10)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:send-subscription-reminders');
    }
})->daily()->name('app-send-subscription-reminders')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));

// Hourly rather than daily: the first nudge is due one hour after signup, and 91% of the
// people who activate do so inside that first hour - a daily pass would reach the rest a day
// late, long after the moment has gone.
Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:send-onboarding-nudges', ['--apply' => true]);
    }
})->hourly()->name('send-onboarding-nudges')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

// app:send-activation-nudges is deliberately NOT scheduled, here or in
// AppController::translateData(). It emails people who are already using the app, and its
// windows are wide enough that the first pass over an install that has never run it reaches a
// large backlog at once. Run it by hand - no flag prints a dry run, --apply sends - and put it
// back on a schedule once a real pass has been read and looks right.

Schedule::call(function () {
    Artisan::call('app:notify-request-changes');
    Artisan::call('app:notify-fan-content-changes');
    Artisan::call('app:notify-poll-option-changes');
})->daily()->at('12:00')->name('app-notify-pending-changes')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-carpool-reminders');
})->hourly()->name('app-send-carpool-reminders')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:generate-daily-blog-post');
    }
})->daily()->name('app-generate-daily-blog-post')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:process-referral-credits');
    }
})->daily()->name('app-process-referral-credits')->withoutOverlapping(30)->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:update-geoip');
})->monthly()->name('app-update-geoip')->withoutOverlapping(60)->appendOutputTo(storage_path('logs/scheduler.log'));
