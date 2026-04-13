<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Console\Command\Command;

$isQueueProcessEnabled = static function (): bool {
    $rawValue = getenv('QUEUE_PROCESS_ENABLED');

    if ($rawValue === false || $rawValue === '') {
        $rawValue = env('QUEUE_PROCESS_ENABLED', true);
    }

    $normalized = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    return $normalized ?? true;
};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('queues:process', function () use ($isQueueProcessEnabled) {
    if (! $isQueueProcessEnabled()) {
        $this->info('Queue processing is disabled via QUEUE_PROCESS_ENABLED.');

        return Command::SUCCESS;
    }

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

Schedule::command('queues:process')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
