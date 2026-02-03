<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranchHour;
use Illuminate\Database\Seeder;

class CommerceBranchHourSeeder extends Seeder
{
    public function run(): void
    {
        if(env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if(env('DEMO_SEEDING') == 'true') {
            CommerceBranchHour::factory()->count(30)->create();
        }
    }
}
