<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Neighborhood;
use Database\Seeders\Concerns\SeedsFromDataFile;
use Illuminate\Database\Seeder;

class NeighborhoodSeeder extends Seeder
{
    use SeedsFromDataFile;

    /**
     * Catálogo idempotente leído desde database/data/geo/neighborhoods.json (~1.100 filas).
     * La ciudad padre se resuelve por clave natural (city_code), precargada en un
     * mapa code→id para evitar N+1. Se siembra en lotes (ver SeedsFromDataFile).
     */
    public function run(): void
    {
        $cityIds = City::pluck('id', 'code');

        $rows = collect($this->loadDataFile('neighborhoods.json'))
            ->map(function (array $row) use ($cityIds) {
                $cityId = $cityIds[$row['city_code']] ?? null;

                if ($cityId === null) {
                    return null;
                }

                return [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'city_id' => $cityId,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $this->upsertChunked(Neighborhood::class, $rows, ['name', 'city_id']);
    }
}
