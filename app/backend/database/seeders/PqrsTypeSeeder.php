<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PqrsType;
use Illuminate\Database\Seeder;

class PqrsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PqrsType::factory()->count(10)->create();
    }
}
