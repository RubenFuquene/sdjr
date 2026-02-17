<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportStatus;
use Illuminate\Database\Seeder;

class SupportStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            SupportStatus::insert([
                ['name' => 'Abierto', 'code' => 'AB', 'color' => '#0f58c5', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'En Progreso', 'code' => 'EP', 'color' => '#ffc107', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Resuelto', 'code' => 'RE', 'color' => '#1be525', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cerrado', 'code' => 'CE', 'color' => '#6c757d', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            SupportStatus::factory()->count(8)->create();
        }
    }
}
