<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use Database\Seeders\Concerns\SeedsFromDataFile;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use SeedsFromDataFile;

    /**
     * Catálogo idempotente leído desde database/data/geo/departments.json.
     * El país padre se resuelve por clave natural (country_code), precargado
     * en un mapa code→id para evitar N+1.
     */
    public function run(): void
    {
        $countryIds = Country::pluck('id', 'code');

        $rows = collect($this->loadDataFile('departments.json'))
            ->map(function (array $row) use ($countryIds) {
                $countryId = $countryIds[$row['country_code']] ?? null;

                if ($countryId === null) {
                    return null;
                }

                return [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'country_id' => $countryId,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->upsertChunked(Department::class, $rows, ['name', 'country_id']);
    }
}
