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
        if(env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if(env('DEMO_SEEDING') == 'true') {
            PriorityType::factory()->count(5)->create();
        }
    }
}
