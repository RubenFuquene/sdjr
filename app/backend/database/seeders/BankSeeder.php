<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {

            Bank::insert([
                ['name' => 'Bancolombia', 'code' => 'BCO', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Davivienda', 'code' => 'DAV', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Banco de Bogotá', 'code' => 'BDB', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Banco de Occidente', 'code' => 'BDO', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Banco Popular', 'code' => 'BP', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'BBVA', 'code' => 'BBVA', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Scotiabank', 'code' => 'SCO', 'created_at' => now(), 'updated_at' => now()],
            ]);

        }

        if (env('DEMO_SEEDING') == 'true') {
            Bank::factory()->count(10)->create();
        }

    }
}
