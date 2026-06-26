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
     *
     * Catálogo idempotente. La tabla no tiene `code`, así que se usa firstOrCreate
     * por (establishment_type_id, name). El id del tipo de establecimiento se resuelve
     * por su clave natural (`code`) en lugar de asumir 1/2/3.
     */
    public function run(): void
    {
        // ordinal del dato fijo => código real del tipo de establecimiento
        $typeIdByCode = EstablishmentType::whereIn('code', ['RE', 'PA', 'RT'])->pluck('id', 'code');
        $ordinalToTypeId = [
            1 => $typeIdByCode['RE'] ?? null,
            2 => $typeIdByCode['PA'] ?? null,
            3 => $typeIdByCode['RT'] ?? null,
        ];

        $categories = [
            ['establishment_type_id' => 1, 'name' => 'Plato del día (sopa, seco, principio)', 'description' => 'Almuerzo del día'],
            ['establishment_type_id' => 1, 'name' => 'Platos ejecutivos', 'description' => 'Platos especiales'],
            ['establishment_type_id' => 1, 'name' => 'Comida rápida (empanadas, arepas, hamburguesas)', 'description' => 'Empanadas, arepas, hamburguesa, pizza.'],
            ['establishment_type_id' => 1, 'name' => 'Postres y repostería', 'description' => 'Tortas, milhojas, pasteles sin vender en el día.'],
            ['establishment_type_id' => 1, 'name' => 'Bebidas (café, chocolate, batidos)', 'description' => 'Bebidas calientes (café, chocolate) o frías (batidos).'],
            ['establishment_type_id' => 2, 'name' => 'Pan', 'description' => 'Pan de bono, almojábanas, buñuelos, pan de queso.'],
            ['establishment_type_id' => 2, 'name' => 'Pastelería y repostería', 'description' => 'Tortas, milhojas, roscas, pasteles sin vender.'],
            ['establishment_type_id' => 2, 'name' => 'Galletas y bizcochos', 'description' => 'Galletas dulces, bizcochos.'],
            ['establishment_type_id' => 2, 'name' => 'Desayunos preparados (huevos, ensaladas de fruta)', 'description' => 'Huevos preparados, ensaladas de fruta, preparados.'],
            ['establishment_type_id' => 2, 'name' => 'Bebidas (café, chocolate, batidos)', 'description' => 'Café, chocolate caliente, batidos naturales.'],
            ['establishment_type_id' => 2, 'name' => 'Postres y dulces', 'description' => 'Postres, dulces, confites frescos del día.'],
            ['establishment_type_id' => 3, 'name' => 'Frutas y verduras', 'description' => 'Frutas con pequeños golpes o formas irregulares.'],
            ['establishment_type_id' => 3, 'name' => 'Frutas y verduras pequeñas', 'description' => 'Verduras ligeramente marchitas o tamaño pequeño.'],
            ['establishment_type_id' => 3, 'name' => 'Frutas y verduras maduras', 'description' => 'Frutas y verduras muy maduras (para jugos, mermeladas).'],
            ['establishment_type_id' => 3, 'name' => 'Granos (arroz, frijol, lenteja, garbanzo)', 'description' => 'Arroz, frijol, lenteja, garbanzo con vencimiento próximo.'],
            ['establishment_type_id' => 3, 'name' => 'Pastas', 'description' => 'Pastas secas (espagueti, penne, etc.) con vencimiento próximo.'],
            ['establishment_type_id' => 3, 'name' => 'Enlatados (atún, vegetales, salchichas)', 'description' => 'Atún, vegetales, tomate y otros enlatados próximos a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Encurtidos y salsas', 'description' => 'Encurtidos, salsas, aderezos próximos a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Aceites y margarinas', 'description' => 'Aceites de cocina, margarinas próximas a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Condimentos y especias', 'description' => 'Condimentos, especias, sazón próximos a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Café molido o en grano', 'description' => 'Café molido o en grano próximo a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Chocolates y bebidas instantáneas', 'description' => 'Chocolates en polvo, bebidas instantáneas próximas a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Endulzantes', 'description' => 'Endulzantes (azúcar, stevia, otros) próximos a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Sal', 'description' => 'Sal refinada de mesa próxima a vencer o empaque dañado.'],
            ['establishment_type_id' => 3, 'name' => 'Lácteos (queso, yogur, leche)', 'description' => 'Queso, yogur, leche próximos a la fecha de vencimiento.'],
            ['establishment_type_id' => 3, 'name' => 'Embutidos (jamón, mortadela, salchicha)', 'description' => 'Jamón, mortadela, salchicha próximos a la fecha de vencimiento.'],
            ['establishment_type_id' => 3, 'name' => 'Carnes (Res, cerdo, pollo, pescado)', 'description' => 'Carnes y pollos frescos del día que no se vendieron.'],
            ['establishment_type_id' => 3, 'name' => 'Huevos', 'description' => 'Huevos de descarte estético o próximos a vencer.'],
            ['establishment_type_id' => 3, 'name' => 'Bebidas (Agua, jugos)', 'description' => 'Bebidas gasificadas y semiprocesados.'],
            ['establishment_type_id' => 3, 'name' => 'Licores', 'description' => 'Cerveza, aguardiente, vino'],
            ['establishment_type_id' => 3, 'name' => 'Pasabocas (papas, chitos, galletas)', 'description' => 'Dulces próximos a vencer'],
            ['establishment_type_id' => 3, 'name' => 'Dulcería (caramelos, chicles, chocolates)', 'description' => 'Gomas, cocholaitas, dulces'],
            ['establishment_type_id' => 3, 'name' => 'Panadería y pastelería', 'description' => 'Mogollas, pan integral, pan blanco'],
            ['establishment_type_id' => 3, 'name' => 'Aseo para el hogar', 'description' => 'Detergente, suavizantes próximos a vencer'],
            ['establishment_type_id' => 3, 'name' => 'Cuidado personal e higiene', 'description' => 'Crema dental, cremas, lociones'],
            ['establishment_type_id' => 3, 'name' => 'Mascotas', 'description' => 'Alimento para mascota'],
        ];

        foreach ($categories as $category) {
            $establishmentTypeId = $ordinalToTypeId[$category['establishment_type_id']] ?? null;

            if ($establishmentTypeId === null) {
                continue;
            }

            ProductCategory::firstOrCreate(
                ['establishment_type_id' => $establishmentTypeId, 'name' => $category['name']],
                ['description' => $category['description']],
            );
        }
    }
}
