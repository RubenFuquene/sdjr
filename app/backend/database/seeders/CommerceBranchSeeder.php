<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranch;
use Illuminate\Database\Seeder;

class CommerceBranchSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranch::factory()->count(10)->create();
    }
}
