<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceDocument;
use Illuminate\Database\Seeder;

class CommerceDocumentSeeder extends Seeder
{
    public function run(): void
    {
        if(env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if(env('DEMO_SEEDING') == 'true') {
            CommerceDocument::factory()->count(20)->create();
        }
    }
}
