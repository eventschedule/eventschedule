<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Artisan::call('app:release-tickets');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:translate');
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('google:refresh-webhooks');
})->daily()->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::call(function () {
    Artisan::call('app:notify-request-changes');
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
        Artisan::call('app:reset-demo');
    }
})->hourly()->appendOutputTo(storage_path('logs/scheduler.log'));
