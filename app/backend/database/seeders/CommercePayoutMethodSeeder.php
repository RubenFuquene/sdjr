<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommercePayoutMethod;
use Illuminate\Database\Seeder;

class CommercePayoutMethodSeeder extends Seeder
{
    public function run(): void
    {
        CommercePayoutMethod::factory()->count(10)->create();
    }
}
