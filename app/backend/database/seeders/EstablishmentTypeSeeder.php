<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EstablishmentType;
use Illuminate\Database\Seeder;

class EstablishmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        EstablishmentType::factory()->count(10)->create();
    }
}
