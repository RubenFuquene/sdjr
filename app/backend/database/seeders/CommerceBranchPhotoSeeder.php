<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranchPhoto;
use Illuminate\Database\Seeder;

class CommerceBranchPhotoSeeder extends Seeder
{
    public function run(): void
    {
        CommerceBranchPhoto::factory()->count(20)->create();
    }
}
