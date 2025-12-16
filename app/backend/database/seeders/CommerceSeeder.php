<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Commerce;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        Commerce::factory()->count(10)->create();
    }
}
