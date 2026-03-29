<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EstablishmentType;
use Illuminate\Database\Seeder;

class EstablishmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            EstablishmentType::insert([
                ['name' => 'Restaurante', 'code' => 'RE', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Panadería y Pastelería', 'code' => 'PA', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Retail', 'code' => 'RT', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            EstablishmentType::factory()->count(10)->create();
        }
    }
}
