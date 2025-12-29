<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Neighborhood;
use Illuminate\Database\Seeder;

class NeighborhoodSeeder extends Seeder
{
    public function run(): void
    {
        Neighborhood::factory(10)->create();
    }
}
