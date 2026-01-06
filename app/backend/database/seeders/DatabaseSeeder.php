<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

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
        ]);
    }
}
