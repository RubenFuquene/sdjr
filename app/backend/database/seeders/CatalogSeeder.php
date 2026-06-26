<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orquestador de datos de catálogo / referencia.
 *
 * Agrupa los seeders cuyos datos son fijos y deben existir en TODOS los entornos.
 * Está pensado para ejecutarse en cada despliegue (pre-deploy command de Railway)
 * de forma idempotente: re-ejecutarlo no duplica ni borra información.
 *
 * El orden respeta las dependencias entre catálogos (país → depto → ciudad → barrio,
 * tipo de establecimiento → categoría de producto, roles → usuario admin).
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CountrySeeder::class,
            DepartmentSeeder::class,
            CitySeeder::class,
            NeighborhoodSeeder::class,
            EstablishmentTypeSeeder::class,
            BankSeeder::class,
            SupportStatusSeeder::class,
            PqrsTypeSeeder::class,
            PriorityTypeSeeder::class,
            ProductCategorySeeder::class,
            UserSeeder::class,
        ]);
    }
}
