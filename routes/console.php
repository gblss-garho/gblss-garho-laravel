<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('promotion:run')
    ->yearlyOn(3, 31, '00:05')
    ->timezone('Asia/Karachi');
Schedule::command('alerts:absence')->weekdays()->at('08:40')->timezone('Asia/Karachi');
