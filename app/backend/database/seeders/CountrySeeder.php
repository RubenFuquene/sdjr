<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Catálogo idempotente: se ejecuta en todos los entornos (sin gate de APP_ENV)
     * y usa upsert por la clave natural `code`, de modo que re-ejecutarlo no duplica.
     */
    public function run(): void
    {
        Country::upsert(
            [
                ['name' => 'Colombia', 'code' => 'CO', 'created_at' => now(), 'updated_at' => now()],
            ],
            ['code'],
            ['name', 'updated_at'],
        );
    }
}
