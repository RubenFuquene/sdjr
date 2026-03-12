<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EstablishmentType;
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
                ['establishment_type_id' => 1, 'name' => 'Plato del día (sopa, seco, principio)', 'description' => 'Almuerzo del día', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 1, 'name' => 'Platos ejecutivos', 'description' => 'Platos especiales', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 1, 'name' => 'Comida rápida (empanadas, arepas, hamburguesas)', 'description' => 'Empanadas, arepas, hamburguesa, pizza.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 1, 'name' => 'Postres y repostería', 'description' => 'Tortas, milhojas, pasteles sin vender en el día.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 1, 'name' => 'Bebidas (café, chocolate, batidos)', 'description' => 'Bebidas calientes (café, chocolate) o frías (batidos).', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Pan', 'description' => 'Pan de bono, almojábanas, buñuelos, pan de queso.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Pastelería y repostería', 'description' => 'Tortas, milhojas, roscas, pasteles sin vender.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Galletas y bizcochos', 'description' => 'Galletas dulces, bizcochos.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Desayunos preparados (huevos, ensaladas de fruta)', 'description' => 'Huevos preparados, ensaladas de fruta, preparados.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Bebidas (café, chocolate, batidos)', 'description' => 'Café, chocolate caliente, batidos naturales.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 2, 'name' => 'Postres y dulces', 'description' => 'Postres, dulces, confites frescos del día.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Frutas y verduras', 'description' => 'Frutas con pequeños golpes o formas irregulares.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Frutas y verduras pequeñas', 'description' => 'Verduras ligeramente marchitas o tamaño pequeño.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Frutas y verduras maduras', 'description' => 'Frutas y verduras muy maduras (para jugos, mermeladas).', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Granos (arroz, frijol, lenteja, garbanzo)', 'description' => 'Arroz, frijol, lenteja, garbanzo con vencimiento próximo.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Pastas', 'description' => 'Pastas secas (espagueti, penne, etc.) con vencimiento próximo.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Enlatados (atún, vegetales, salchichas)', 'description' => 'Atún, vegetales, tomate y otros enlatados próximos a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Encurtidos y salsas', 'description' => 'Encurtidos, salsas, aderezos próximos a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Aceites y margarinas', 'description' => 'Aceites de cocina, margarinas próximas a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Condimentos y especias', 'description' => 'Condimentos, especias, sazón próximos a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Café molido o en grano', 'description' => 'Café molido o en grano próximo a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Chocolates y bebidas instantáneas', 'description' => 'Chocolates en polvo, bebidas instantáneas próximas a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Endulzantes', 'description' => 'Endulzantes (azúcar, stevia, otros) próximos a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Sal', 'description' => 'Sal refinada de mesa próxima a vencer o empaque dañado.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Lácteos (queso, yogur, leche)', 'description' => 'Queso, yogur, leche próximos a la fecha de vencimiento.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Embutidos (jamón, mortadela, salchicha)', 'description' => 'Jamón, mortadela, salchicha próximos a la fecha de vencimiento.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Carnes (Res, cerdo, pollo, pescado)', 'description' => 'Carnes y pollos frescos del día que no se vendieron.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Huevos', 'description' => 'Huevos de descarte estético o próximos a vencer.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Bebidas (Agua, jugos)', 'description' => 'Bebidas gasificadas y semiprocesados.', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Licores', 'description' => 'Cerveza, aguardiente, vino', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Pasabocas (papas, chitos, galletas)', 'description' => 'Dulces próximos a vencer', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Dulcería (caramelos, chicles, chocolates)', 'description' => 'Gomas, cocholaitas, dulces', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Panadería y pastelería', 'description' => 'Mogollas, pan integral, pan blanco', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Aseo para el hogar', 'description' => 'Detergente, suavizantes próximos a vencer', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Cuidado personal e higiene', 'description' => 'Crema dental, cremas, lociones', 'created_at' => now(), 'updated_at' => now()],
                ['establishment_type_id' => 3, 'name' => 'Mascotas', 'description' => 'Alimento para mascota', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        if (env('DEMO_SEEDING') == 'true') {
            ProductCategory::factory()->count(10)->create();
        }
    }
}
