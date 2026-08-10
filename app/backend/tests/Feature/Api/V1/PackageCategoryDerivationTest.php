<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-370: la categoría de un pack se deriva del componente de mayor
 * valor prorrateado — nunca se le pregunta al aliado.
 */
class PackageCategoryDerivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_package_category_derives_from_highest_value_component(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $cheapCategory = ProductCategory::factory()->create();
        $expensiveCategory = ProductCategory::factory()->create();

        $cheap = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $cheapCategory->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $expensive = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $expensiveCategory->id,
            'original_price' => 50,
            'discounted_price' => 50,
        ]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $cheapCategory->id, // debe ser ignorado
                'title' => 'Combo Test',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 60,
            ],
            'package_items' => [
                ['product_id' => $cheap->id, 'quantity' => 1],
                ['product_id' => $expensive->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'title' => 'Combo Test',
            'product_category_id' => $expensiveCategory->id,
        ]);
    }

    public function test_package_category_considers_quantity_in_prorated_value(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $lowQuantityCategory = ProductCategory::factory()->create();
        $highQuantityCategory = ProductCategory::factory()->create();

        // Precio unitario mayor, pero una sola unidad: valor total 20.
        $lowQuantityComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $lowQuantityCategory->id,
            'original_price' => 20,
            'discounted_price' => 20,
        ]);
        // Precio unitario menor, tres unidades: valor total 30 — gana.
        $highQuantityComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $highQuantityCategory->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Cantidad',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 50,
            ],
            'package_items' => [
                ['product_id' => $lowQuantityComponent->id, 'quantity' => 1],
                ['product_id' => $highQuantityComponent->id, 'quantity' => 3],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'title' => 'Combo Cantidad',
            'product_category_id' => $highQuantityCategory->id,
        ]);
    }

    public function test_package_category_tie_break_by_lowest_category_id(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        // Creadas en este orden: $firstCategory obtiene el id menor.
        $firstCategory = ProductCategory::factory()->create();
        $secondCategory = ProductCategory::factory()->create();

        $componentInSecond = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $secondCategory->id,
            'original_price' => 25,
            'discounted_price' => 25,
        ]);
        $componentInFirst = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $firstCategory->id,
            'original_price' => 25,
            'discounted_price' => 25,
        ]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Empate',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 50,
            ],
            'package_items' => [
                ['product_id' => $componentInSecond->id, 'quantity' => 1],
                ['product_id' => $componentInFirst->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'title' => 'Combo Empate',
            'product_category_id' => $firstCategory->id,
        ]);
    }

    public function test_package_without_valid_components_cannot_be_created(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Vacio',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 0,
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['package_items']);
    }

    public function test_update_recalculates_category_when_composition_changes(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $originalCategory = ProductCategory::factory()->create();
        $newCategory = ProductCategory::factory()->create();

        $originalComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $originalCategory->id,
            'original_price' => 30,
            'discounted_price' => 30,
        ]);
        $newComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $newCategory->id,
            'original_price' => 30,
            'discounted_price' => 30,
        ]);
        $newComponent->commerceBranches()->attach($branch->id, ['quantity_available' => 5]);

        $pack = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Editable',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 30,
            ],
            'package_items' => [
                ['product_id' => $originalComponent->id, 'quantity' => 1],
            ],
        ])->json('data.id');

        $this->assertDatabaseHas('products', ['id' => $pack, 'product_category_id' => $originalCategory->id]);

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$pack, [
            'product' => ['commerce_id' => $commerce->id, 'original_price' => 30],
            'package_items' => [
                ['product_id' => $newComponent->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $pack, 'product_category_id' => $newCategory->id]);
    }

    public function test_update_without_package_items_key_keeps_existing_category(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $category = ProductCategory::factory()->create();
        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'original_price' => 15,
            'discounted_price' => 15,
        ]);

        $pack = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Estable',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 15,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
        ])->json('data.id');

        // Editar solo el título, sin tocar package_items: la categoría
        // derivada no debe recalcularse ni perderse.
        $response = $this->putJson('/api/v1/products/'.$pack, [
            'product' => ['commerce_id' => $commerce->id, 'title' => 'Combo Renombrado'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $pack, 'product_category_id' => $category->id]);
    }
}
