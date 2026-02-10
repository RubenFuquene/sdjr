<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommercePayoutMethod;
use Illuminate\Database\Seeder;

class CommercePayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if (env('DEMO_SEEDING') == 'true') {
            CommercePayoutMethod::factory()->count(10)->create();
        }
    }
}
