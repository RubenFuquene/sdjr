<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranch;
use Illuminate\Database\Seeder;

class CommerceBranchSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if (env('DEMO_SEEDING') == 'true') {
            CommerceBranch::factory()->count(10)->create();
        }
    }
}
