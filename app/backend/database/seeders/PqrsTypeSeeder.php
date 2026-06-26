<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PqrsType;
use Illuminate\Database\Seeder;

class PqrsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: upsert por la clave natural `code`.
     */
    public function run(): void
    {
        PqrsType::upsert(
            [
                ['name' => 'Pedidos', 'code' => 'PE', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pagos', 'code' => 'PA', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bloqueo de cuenta', 'code' => 'BC', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Falla técnica', 'code' => 'FT', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Desconocimiento funcional', 'code' => 'DF', 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'updated_at'],
        );
    }
}
