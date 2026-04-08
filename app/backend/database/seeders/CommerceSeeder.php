<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Commerce;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if (env('DEMO_SEEDING') == 'true') {
            Commerce::factory()->count(5)->create();
        }
    }
}
