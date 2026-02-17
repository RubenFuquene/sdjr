<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            ProductCategory::insert([
                ['name' => 'Cómida rápida', 'description' => 'Hamburguesas, pizzas, tacos, etc.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Restaurantes', 'description' => 'Lugares donde se sirven comidas completas.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Postres', 'description' => 'Dulces y postres variados.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bebidas', 'description' => 'Bebidas alcohólicas y no alcohólicas.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Supermercados', 'description' => 'Productos de supermercado y tiendas de conveniencia.', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            ProductCategory::factory()->count(10)->create();
        }
    }
}
