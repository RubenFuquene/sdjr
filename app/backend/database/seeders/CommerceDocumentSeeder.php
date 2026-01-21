<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceDocument;
use Illuminate\Database\Seeder;

class CommerceDocumentSeeder extends Seeder
{
    public function run(): void
    {
        CommerceDocument::factory()->count(20)->create();
    }
}
