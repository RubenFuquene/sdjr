<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Database\Seeders\Concerns\SeedsFromDataFile;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    use SeedsFromDataFile;

    /**
     * Catálogo idempotente leído desde database/data/geo/countries.json.
     */
    public function run(): void
    {
        $rows = $this->loadDataFile('countries.json');

        $this->upsertChunked(Country::class, $rows, ['name']);
    }
}
