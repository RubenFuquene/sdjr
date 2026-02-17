<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            Department::insert([
                ['name' => 'Cundinamarca', 'country_id' => 1],
                ['name' => 'Antioquia', 'country_id' => 1],
                ['name' => 'Valle del Cauca', 'country_id' => 1],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            Department::factory(5)->create();
        }
    }
}
