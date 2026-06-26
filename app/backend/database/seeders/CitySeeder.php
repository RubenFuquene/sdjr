<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: resuelve el departamento por su clave natural ('11')
     * y hace upsert por `code`.
     */
    public function run(): void
    {
        $departmentId = Department::where('code', '11')->value('id');

        if ($departmentId === null) {
            return;
        }

        City::upsert(
            [
                ['name' => 'Bogotá', 'code' => '11001', 'department_id' => $departmentId, 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'department_id', 'updated_at'],
        );
    }
}
