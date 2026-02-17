<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            City::insert([
                ['name' => 'Bogotá', 'department_id' => 1, 'code' => '11001', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Medellín', 'department_id' => 2, 'code' => '05001', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cali', 'department_id' => 3, 'code' => '76001', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            City::factory(5)->create();
        }
    }
}
