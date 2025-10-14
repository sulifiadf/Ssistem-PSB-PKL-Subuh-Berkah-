<?php

//tes upstream

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\kehadiran;
use Carbon\Carbon;
use App\Console\Commands\AutoSetLibur;
use App\Console\Commands\KirimReminderKehadiran;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



