<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalRepresentative;
use Illuminate\Database\Seeder;

class LegalRepresentativeSeeder extends Seeder
{
    public function run(): void
    {
        LegalRepresentative::factory()->count(10)->create();
    }
}
