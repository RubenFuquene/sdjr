<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstablishmentType;

class EstablishmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        EstablishmentType::factory()->count(10)->create();
    }
}
