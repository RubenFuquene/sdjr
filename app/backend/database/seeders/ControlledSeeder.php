<?php

namespace Database\Seeders;

use App\Models\SeederControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

abstract class ControlledSeeder extends Seeder
{
    /**
     * The seeder version - increment when data changes
     */
    protected string $version = '1.0';

    /**
     * Whether this seeder can be safely re-run (idempotent)
     */
    protected bool $idempotent = false;

    /**
     * Run the seeder with version control
     */
    public function run(): void
    {
        $seederName = static::class;

        // Check force reseed flag
        $forceReseed = env('FORCE_RESEED', false);

        if ($forceReseed) {
            Log::info("Force re-seeding enabled for {$seederName}");
            $this->handleForceReseed();
        }

        // Check if already executed for current version
        if (! $forceReseed && SeederControl::hasBeenExecuted($seederName, $this->version)) {
            Log::info("Skipping {$seederName} - already executed for version {$this->version}");

            return;
        }

        Log::info("Executing seeder: {$seederName} (version: {$this->version})");

        try {
            $this->runSeeder();

            SeederControl::markAsExecuted($seederName, $this->version, [
                'records_created' => $this->getRecordsCount(),
                'execution_time' => microtime(true) - LARAVEL_START,
            ]);

            Log::info("Successfully executed {$seederName}");
        } catch (\Exception $e) {
            Log::error("Failed to execute {$seederName}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle force re-seeding logic
     */
    protected function handleForceReseed(): void
    {
        if (! $this->idempotent) {
            Log::warning('Force re-seeding non-idempotent seeder: '.static::class);
            $this->clearExistingData();
        }

        SeederControl::resetSeeder(static::class);
    }

    /**
     * The actual seeding logic - implement this in child classes
     */
    abstract protected function runSeeder(): void;

    /**
     * Clear existing data for non-idempotent seeders
     * Override this in child classes to define cleanup logic
     */
    protected function clearExistingData(): void
    {
        // Override in child classes
        Log::info('No clearExistingData() implementation for '.static::class);
    }

    /**
     * Get count of records created (for metadata)
     * Override in child classes for accurate counts
     */
    protected function getRecordsCount(): int
    {
        return 0;
    }
}
