<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PqrsType;
use Illuminate\Database\Seeder;

class PqrsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if (env('DEMO_SEEDING') == 'true') {
            PqrsType::factory()->count(10)->create();
        }
    }
}
