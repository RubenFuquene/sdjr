<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Commerce;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        Commerce::factory()->count(10)->create();
    }
}
