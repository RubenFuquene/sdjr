<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        LegalDocument::factory()->count(6)->create();
    }
}
