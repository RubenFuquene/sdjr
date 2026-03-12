<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceComment;
use Illuminate\Database\Seeder;

/**
 * Seeder for CommerceComment
 */
class CommerceCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // Aquí puedes agregar datos fijos para producción si aplica
        }
        if (env('DEMO_SEEDING') == 'true') {
            CommerceComment::factory()->count(10)->create();
        }

    }
}
