<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportStatus;

class SupportStatusSeeder extends Seeder
{
    public function run(): void
    {
        SupportStatus::factory()->count(8)->create();
    }
}
