<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: resuelve el país por su clave natural ('CO') en lugar de
     * asumir un id fijo, y hace upsert por `code`.
     */
    public function run(): void
    {
        $countryId = Country::where('code', 'CO')->value('id');

        if ($countryId === null) {
            return;
        }

        Department::upsert(
            [
                ['name' => 'Cundinamarca', 'code' => '11', 'country_id' => $countryId, 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'country_id', 'updated_at'],
        );
    }
}
