<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommerceBranch;

class CommerceBranchSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranch::factory()->count(10)->create();
    }
}
