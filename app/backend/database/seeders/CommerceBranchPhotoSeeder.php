<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommerceBranchPhoto;

class CommerceBranchPhotoSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranchPhoto::factory()->count(20)->create();
    }
}
