<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommerceDocument;

class CommerceDocumentSeeder extends Seeder
{
    public function run(): void
    {
        CommerceDocument::factory()->count(20)->create();
    }
}
