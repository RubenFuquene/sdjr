<?php

namespace Database\Seeders;

use App\Models\SeederControl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if seeding is enabled
        if (! env('ENABLE_SEEDING', false)) {
            Log::info('Seeding disabled via ENABLE_SEEDING environment variable');

            return;
        }

        // Handle complete reset if requested
        if (env('FORCE_RESEED', false)) {
            Log::info('Force re-seeding enabled - resetting all seeder control');
            SeederControl::resetAll();
        }

        Log::info('Starting database seeding...');

        $this->call([
            UserSeeder::class,
            CountrySeeder::class,
            DepartmentSeeder::class,
            CitySeeder::class,
            EstablishmentTypeSeeder::class,
            CategorySeeder::class,
            RolePermissionSeeder::class,
            NeighborhoodSeeder::class,
            CommerceSeeder::class,
            LegalRepresentativeSeeder::class,
            BankSeeder::class,
            SupportStatusSeeder::class,
            PqrsTypeSeeder::class,
            PriorityTypeSeeder::class,
            CommercePayoutMethodSeeder::class,
            CommerceBranchSeeder::class,
            CommerceDocumentSeeder::class,
            CommerceBranchPhotoSeeder::class,
            CommerceBranchHourSeeder::class,
            LegalDocumentSeeder::class,
        ]);

        Log::info('Database seeding completed');
    }
}
