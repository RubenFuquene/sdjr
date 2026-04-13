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
    // ── Diagnostic log 1: confirm the closure was actually entered. ──────────
    Log::info('[queues:process] Command closure entered — execution has started.');
    $this->info('[queues:process] Command closure entered.');

    try {
        $queueList = env('QUEUE_PROCESS_QUEUES', 'emails,default');
        $tries = (int) env('QUEUE_PROCESS_TRIES', 3);
        $timeout = (int) env('QUEUE_PROCESS_TIMEOUT', 120);
        $sleep = (int) env('QUEUE_PROCESS_SLEEP', 1);

        // ── Diagnostic log 2: confirm env vars resolved correctly. ───────────
        Log::info('[queues:process] Configuration resolved — starting queue:work.', [
            'queues' => $queueList,
            'tries' => $tries,
            'timeout' => $timeout,
            'sleep' => $sleep,
        ]);

        $this->info("[queues:process] Processing queues: {$queueList}");

        $this->call('queue:work', [
            '--queue' => $queueList,
            '--stop-when-empty' => true,
            '--tries' => $tries,
            '--timeout' => $timeout,
            '--sleep' => $sleep,
        ]);

        // ── Diagnostic log 3: confirm queue:work returned cleanly. ───────────
        Log::info('[queues:process] queue:work returned — run finished successfully.');
        $this->info('[queues:process] queue:work finished.');

    } catch (\Throwable $e) {
        // ── Diagnostic log 4: surface any exception that was swallowed. ──────
        Log::error('[queues:process] Exception thrown during execution.', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        $this->error('[queues:process] Exception: '.$e->getMessage());

        throw $e; // re-throw so the scheduler marks the run as failed
    }
})->purpose('Process queued jobs once and stop when queue is empty');

// Register schedules — both are unconditional so the scheduler always sees them.
Log::info('[console.php] Registering Schedule::command entries.');

Schedule::command('scheduler:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();

// NOTE: runInBackground() has been intentionally removed.
// When a closure-based command runs in the background, Laravel spawns a
// detached child process.  Any Log::info / Log::error calls inside the
// closure execute in that child, but the child's output is not captured by
// the parent scheduler process, making failures completely invisible in logs.
// Running in the foreground keeps all logging in the same process and lets
// withoutOverlapping() work reliably via its mutex.

// ── Diagnostic: log immediately before registering queues:process so we can
// confirm this code path is reached on every scheduler tick. ─────────────────
Log::info('[console.php] About to register Schedule::command(queues:process).');

// withoutOverlapping() has been intentionally removed from this command.
// Hypothesis: a stuck mutex was silently preventing every run.  Without the
// mutex guard the command will execute unconditionally each minute, which lets
// us confirm whether the mutex was the culprit.
Schedule::command('queues:process')
    ->everyMinute();

// ── Diagnostic: log immediately after registration to confirm it succeeded. ──
Log::info('[console.php] Schedule::command(queues:process) registered successfully.');

Log::info('[console.php] Schedule registration complete.');
