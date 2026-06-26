<?php

namespace Database\Seeders;

use App\Models\SeederControl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if seeding is enabled
        if (! env('ENABLE_SEEDING', false)) {
            Log::info('Seeding disabled via ENABLE_SEEDING environment variable');

            return;
        }

        // Handle complete reset if requested
        if (env('FORCE_RESEED', false)) {
            Log::info('Force re-seeding enabled - resetting all seeder control');
            SeederControl::resetAll();
        }

        Log::info('Starting database seeding...');

        // Catálogo de referencia: datos fijos idempotentes, base de todos los entornos.
        // En el deploy se invoca directamente vía `db:seed --class=CatalogSeeder`.
        $this->call(CatalogSeeder::class);

        // Datos demo: solo en entornos de prueba bajo flag explícito.
        if (env('DEMO_SEEDING', false)) {
            $this->call(DemoSeeder::class);
        }

        Log::info('Database seeding completed');
    }
}
