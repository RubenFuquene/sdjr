<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Database\Seeders\Concerns\SeedsFromDataFile;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    use SeedsFromDataFile;

    /**
     * Catálogo idempotente leído desde database/data/geo/cities.json.
     * El departamento padre se resuelve por clave natural (department_code),
     * precargado en un mapa code→id para evitar N+1.
     */
    public function run(): void
    {
        $departmentIds = Department::pluck('id', 'code');

        $rows = collect($this->loadDataFile('cities.json'))
            ->map(function (array $row) use ($departmentIds) {
                $departmentId = $departmentIds[$row['department_code']] ?? null;

                if ($departmentId === null) {
                    return null;
                }

                return [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'department_id' => $departmentId,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->upsertChunked(City::class, $rows, ['name', 'department_id']);
    }
}
