<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            Country::insert([
                ['name' => 'Colombia', 'code' => 'CO', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            Country::factory(5)->create();
        }
    }
}
