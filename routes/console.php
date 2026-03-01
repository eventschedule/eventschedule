<?php

use App\Jobs\ProcessScheduledNewsletters;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('app:release-tickets');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:expire-waitlist');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:translate');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:refresh-webhooks');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (! config('app.hosted')) {
        Artisan::call('app:import-curator-events');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:send-graphic-emails');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('caldav:sync');
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
    if (\App\Services\MetaAdsService::isBoostConfigured()) {
        Artisan::call('boost:sync');
    }
})->everyFifteenMinutes()->name('boost-sync')->withoutOverlapping()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('boost:expire-pending');
})->everyFifteenMinutes()->appendOutputTo(storage_path('logs/scheduler.log'));

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
})->daily()->at('12:00')->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    if (config('app.hosted')) {
        Artisan::call('app:generate-daily-blog-post');
    }
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));
