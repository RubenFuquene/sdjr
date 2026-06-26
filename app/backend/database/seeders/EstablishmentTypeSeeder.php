<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EstablishmentType;
use Illuminate\Database\Seeder;

class EstablishmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: upsert por la clave natural `code`.
     */
    public function run(): void
    {
        EstablishmentType::upsert(
            [
                ['name' => 'Restaurante', 'code' => 'RE', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Panadería y Pastelería', 'code' => 'PA', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Retail', 'code' => 'RT', 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'updated_at'],
        );
    }
}
