<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranchHour;
use Illuminate\Database\Seeder;

class CommerceBranchHourSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranchHour::factory()->count(30)->create();
    }
}
