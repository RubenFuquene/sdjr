<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Constants\Constant;
use App\Models\PriorityType;
use Illuminate\Database\Seeder;

class PriorityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catálogo canónico (Alta/Media/Baja) sembrado de forma idempotente en todos
        // los entornos: el flujo de rechazo depende de la prioridad 'AL' y antes solo
        // se creaba en 'prd', dejándola ausente en los demás entornos.
        foreach (Constant::PRIORITY_TYPE_CATALOG as $code => $name) {
            PriorityType::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'status' => Constant::STATUS_ACTIVE]
            );
        }

        if (env('DEMO_SEEDING') == 'true') {
            PriorityType::factory()->count(5)->create();
        }
    }
}
