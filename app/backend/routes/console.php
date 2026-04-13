<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Log that this file is being loaded so we can confirm the schedule is registered.
Log::info('[console.php] Schedule file loaded — registering scheduled commands.');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Simple heartbeat command to verify the scheduler is ticking.
Artisan::command('scheduler:heartbeat', function () {
    $ts = now()->toDateTimeString();
    $this->info("[scheduler:heartbeat] Scheduler is alive at {$ts}");
    Log::info('[scheduler:heartbeat] Scheduler heartbeat fired.', ['timestamp' => $ts]);
})->purpose('Emit a log entry every minute to confirm the scheduler is running');

Artisan::command('queues:process', function () {
    $queueList = env('QUEUE_PROCESS_QUEUES', 'emails,default');
    $tries     = (int) env('QUEUE_PROCESS_TRIES', 3);
    $timeout   = (int) env('QUEUE_PROCESS_TIMEOUT', 120);
    $sleep     = (int) env('QUEUE_PROCESS_SLEEP', 1);

    Log::info('[queues:process] Starting queue worker run.', [
        'queues'  => $queueList,
        'tries'   => $tries,
        'timeout' => $timeout,
        'sleep'   => $sleep,
    ]);

    $this->info("[queues:process] Processing queues: {$queueList}");

    $this->call('queue:work', [
        '--queue'           => $queueList,
        '--stop-when-empty' => true,
        '--tries'           => $tries,
        '--timeout'         => $timeout,
        '--sleep'           => $sleep,
    ]);

    Log::info('[queues:process] Queue worker run finished.');
})->purpose('Process queued jobs once and stop when queue is empty');

// Register schedules — both are unconditional so the scheduler always sees them.
Log::info('[console.php] Registering Schedule::command entries.');

Schedule::command('scheduler:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('queues:process')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Log::info('[console.php] Schedule registration complete.');
