<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        Bank::factory()->count(10)->create();
    }
}
