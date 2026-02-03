<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranchPhoto;
use Illuminate\Database\Seeder;

class CommerceBranchPhotoSeeder extends Seeder
{
    public function run(): void
    {
        if(env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if(env('DEMO_SEEDING') == 'true') {
            CommerceBranchPhoto::factory()->count(20)->create();
        }
    }
}
