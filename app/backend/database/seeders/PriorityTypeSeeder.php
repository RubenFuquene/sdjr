<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PriorityType;
use Illuminate\Database\Seeder;

class PriorityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PriorityType::factory()->count(5)->create();
    }
}
