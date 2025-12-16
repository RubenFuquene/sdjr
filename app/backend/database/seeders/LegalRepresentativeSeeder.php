<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalRepresentative;

class LegalRepresentativeSeeder extends Seeder
{
    public function run(): void
    {
        LegalRepresentative::factory()->count(10)->create();
    }
}
