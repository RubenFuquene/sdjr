<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalRepresentative;
use Illuminate\Database\Seeder;

class LegalRepresentativeSeeder extends Seeder
{
    public function run(): void
    {
        if(env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if(env('DEMO_SEEDING') == 'true') {
            LegalRepresentative::factory()->count(10)->create();
        }
    }
}
