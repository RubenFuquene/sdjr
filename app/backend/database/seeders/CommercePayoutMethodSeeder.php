<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommercePayoutMethod;

class CommercePayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        CommercePayoutMethod::factory()->count(10)->create();
    }
}
