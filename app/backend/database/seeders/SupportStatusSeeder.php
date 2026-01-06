<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportStatus;
use Illuminate\Database\Seeder;

class SupportStatusSeeder extends Seeder
{
    public function run(): void
    {
        SupportStatus::factory()->count(8)->create();
    }
}
