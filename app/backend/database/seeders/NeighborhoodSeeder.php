<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Neighborhood;
use Illuminate\Database\Seeder;

class NeighborhoodSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            Neighborhood::insert([
                ['name' => 'Chapinero', 'city_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'La Candelaria', 'city_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'El Poblado', 'city_id' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Laureles', 'city_id' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'San Antonio', 'city_id' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Granada', 'city_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            Neighborhood::factory(10)->create();
        }
    }
}
