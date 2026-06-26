<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportStatus;
use Illuminate\Database\Seeder;

class SupportStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: upsert por la clave natural `code`.
     */
    public function run(): void
    {
        SupportStatus::upsert(
            [
                ['name' => 'Abierto', 'code' => 'AB', 'color' => '#0f58c5', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'En Progreso', 'code' => 'EP', 'color' => '#ffc107', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Resuelto', 'code' => 'RE', 'color' => '#1be525', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cerrado', 'code' => 'CE', 'color' => '#6c757d', 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'color', 'updated_at'],
        );
    }
}
