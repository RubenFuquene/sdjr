<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PriorityType;
use Illuminate\Database\Seeder;

class PriorityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            PriorityType::insert([
                ['name' => 'Prioritaria', 'code' => 'PR', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Baja', 'code' => 'BA', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Media', 'code' => 'ME', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Alta', 'code' => 'AL', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            PriorityType::factory()->count(5)->create();
        }
    }
}
