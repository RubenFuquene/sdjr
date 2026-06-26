<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orquestador de datos de prueba (demo).
 *
 * Agrupa los seeders que generan datos de ejemplo mediante factories.
 * Solo debe ejecutarse en entornos de prueba bajo el flag DEMO_SEEDING=true;
 * NUNCA forma parte del release automático de staging/producción.
 *
 * Depende de que el catálogo (CatalogSeeder) ya esté sembrado, por lo que se
 * invoca después de aquel desde DatabaseSeeder.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            LegalDocumentSeeder::class,
            CommerceSeeder::class,
            LegalRepresentativeSeeder::class,
            CommercePayoutMethodSeeder::class,
            CommerceBranchSeeder::class,
            CommerceDocumentSeeder::class,
            CommerceBranchPhotoSeeder::class,
            CommerceBranchHourSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            CommerceCommentSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
