<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommerceBranchHour;

class CommerceBranchHourSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranchHour::factory()->count(30)->create();
    }
}
