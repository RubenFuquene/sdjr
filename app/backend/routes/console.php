<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('queues:process', function () {
    $queueList = env('QUEUE_PROCESS_QUEUES', 'emails,default');
    $tries = (int) env('QUEUE_PROCESS_TRIES', 3);
    $timeout = (int) env('QUEUE_PROCESS_TIMEOUT', 120);
    $sleep = (int) env('QUEUE_PROCESS_SLEEP', 1);

    $this->call('queue:work', [
        '--queue' => $queueList,
        '--stop-when-empty' => true,
        '--tries' => $tries,
        '--timeout' => $timeout,
        '--sleep' => $sleep,
    ]);
})->purpose('Process queued jobs once and stop when queue is empty');

if (env('QUEUE_PROCESS_ENABLED', true)) {
    Schedule::command('queues:process')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
}
