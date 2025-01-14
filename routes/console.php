<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Schedule::call(function () {
    Artisan::call('app:release_tickets');
//})->cron('* * * * *');
})->hourly();