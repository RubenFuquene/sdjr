<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // No seedear en producción
            return;
        }
        if (env('DEMO_SEEDING') !== 'true') {
            return;
        }
    }
}
