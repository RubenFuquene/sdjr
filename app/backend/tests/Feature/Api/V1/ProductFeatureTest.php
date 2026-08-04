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
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.products.index', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.products.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.products.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.products.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.products.delete', 'guard_name' => 'sanctum']);
    }

    private function actingAsAdmin()
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            'provider.products.index',
            'provider.products.create',
            'provider.products.show',
            'provider.products.update',
            'provider.products.delete',
        ]);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_paginated_list()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        Product::factory()->count(3)->create(['commerce_id' => $commerce->id]);
        $response = $this->getJson('/api/v1/products/commerce/'.$commerce->id);
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_store_creates_product()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerce_branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Café Premium',
                'description' => 'Café de origen especial',
                'product_type' => Constant::PRODUCT_TYPE_SINGLE,
                'original_price' => 100.00,
                'discounted_price' => 80.00,
                'expires_at' => now()->addMonth()->toDateTimeString(),
            ],
            // SCRUM-277 Fase 1: single ya no lleva quantity_total/quantity_available
            // a nivel de producto — el stock viaja por sede.
            'commerce_branches' => [
                ['commerce_branch_id' => $commerce_branch->id, 'quantity_available' => 50],
            ],
        ];
        $response = $this->postJson('/api/v1/products/commerce', $payload);
        $response->assertCreated()->assertJsonFragment(['title' => 'Café Premium']);
        $this->assertDatabaseHas('products', ['title' => 'Café Premium']);
        $this->assertDatabaseHas('product_commerce_branch', [
            'commerce_branch_id' => $commerce_branch->id,
            'quantity_available' => 50,
        ]);
    }

    private function buildSingleProductPayload(array $commerceBranchIds, array $productOverrides = []): array
    {
        return [
            'product' => array_merge([
                'commerce_id' => 1,
                'product_category_id' => 1,
                'title' => 'Producto Individual',
                'product_type' => Constant::PRODUCT_TYPE_SINGLE,
                'original_price' => 100.00,
                'discounted_price' => 80.00,
            ], $productOverrides),
            // SCRUM-277 Fase 1: single ya no lleva quantity_total/quantity_available
            // a nivel de producto — el stock viaja por sede.
            'commerce_branches' => array_map(
                fn (int $branchId) => ['commerce_branch_id' => $branchId, 'quantity_available' => 10],
                $commerceBranchIds
            ),
        ];
    }

    /**
     * SCRUM-335: el descuento es obligatorio para productos individuales.
     */
    public function test_store_single_product_requires_discounted_price()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $payload = $this->buildSingleProductPayload([$commerceBranch->id], [
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
        ]);
        unset($payload['product']['discounted_price']);

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product.discounted_price']);
    }

    /**
     * SCRUM-335: el descuento no puede superar el precio original.
     */
    public function test_store_single_product_rejects_discounted_price_over_original()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $payload = $this->buildSingleProductPayload([$commerceBranch->id], [
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'original_price' => 100.00,
            'discounted_price' => 150.00,
        ]);

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product.discounted_price']);
    }

    /**
     * SCRUM-335: el descuento debe ser mayor a 0, no basta con estar diligenciado.
     */
    public function test_store_single_product_rejects_zero_discounted_price()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $payload = $this->buildSingleProductPayload([$commerceBranch->id], [
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'discounted_price' => 0,
        ]);

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product.discounted_price']);
    }

    /**
     * SCRUM-335: los packs no exigen descuento propio.
     */
    public function test_store_package_does_not_require_discounted_price()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct = Product::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack sin descuento',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $singleProduct->currentSalePrice(),
                'quantity_total' => 3,
                'quantity_available' => 3,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 3],
            ],
            'package_items' => [
                ['product_id' => $singleProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);

        $response->assertOk();
    }

    /**
     * SCRUM-335: editar un producto individual sin tocar el descuento, cuando ya
     * tiene uno válido guardado, no debe forzar su reenvío.
     */
    public function test_update_single_product_without_discount_key_keeps_existing_discount()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_SINGLE,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);

        $payload = ['product' => ['commerce_id' => $commerce->id, 'title' => 'Nuevo titulo']];

        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertOk();
    }

    /**
     * SCRUM-335: un producto legacy con descuento null queda bloqueado en su
     * próxima edición hasta que se complete el campo (comportamiento acordado).
     */
    public function test_update_single_product_with_legacy_null_discount_requires_it()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_SINGLE,
            'original_price' => 100,
            'discounted_price' => null,
        ]);

        $payload = ['product' => ['commerce_id' => $commerce->id, 'title' => 'Nuevo titulo']];

        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product.discounted_price']);
    }

    /**
     * SCRUM-335: el descuento efectivo (payload o BD) no puede superar el
     * precio original efectivo al editar.
     */
    public function test_update_single_product_rejects_discounted_price_over_original()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_SINGLE,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);

        $payload = ['product' => ['commerce_id' => $commerce->id, 'discounted_price' => 150]];

        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product.discounted_price']);
    }

    public function test_show_returns_product()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $response = $this->getJson('/api/v1/products/'.$product->id);
        $response->assertOk()->assertJsonFragment(['id' => $product->id]);
    }

    public function test_show_returns_products_by_commerce()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        Product::factory()->count(2)->create(['commerce_id' => $commerce->id]);
        $response = $this->getJson('/api/v1/products/commerce/'.$commerce->id);
        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_update_modifies_product()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $commerce_branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
        ]);
        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Té Verde',
                'product_type' => Constant::PRODUCT_TYPE_SINGLE,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $commerce_branch->id, 'quantity_available' => 15],
            ],
        ];
        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);
        $response->assertOk()->assertJsonFragment(['title' => 'Té Verde']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Té Verde']);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $commerce_branch->id,
            'quantity_available' => 15,
        ]);
    }

    /**
     * Regresión SCRUM-303/306: editar un producto sin enviar commerce_branches
     * (ej. solo cambiar el precio) borraba la sucursal asignada. La clave ausente
     * ahora significa "no tocar la relación", no "vaciarla".
     */
    public function test_update_without_branch_key_keeps_existing_branch()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $product->commerceBranches()->attach($commerceBranch->id);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'original_price' => 999,
            ],
        ];

        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $commerceBranch->id,
        ]);
    }

    /**
     * commerce_branches: [] explícito sigue siendo la forma de "quitar sucursal"
     * a propósito — distinto de omitir la clave por completo.
     */
    public function test_update_with_empty_branch_array_clears_branch()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $product->commerceBranches()->attach($commerceBranch->id);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
            ],
            'commerce_branches' => [],
        ];

        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);

        $response->assertOk();
        $this->assertDatabaseMissing('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $commerceBranch->id,
        ]);
    }

    public function test_patch_status_updates_product_status()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create(['status' => (string) Constant::STATUS_ACTIVE]);

        $response = $this->patchJson('/api/v1/products/'.$product->id.'/status', [
            'status' => (string) Constant::STATUS_INACTIVE,
        ]);

        $response->assertOk()->assertJsonFragment(['status' => (string) Constant::STATUS_INACTIVE]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => (string) Constant::STATUS_INACTIVE,
        ]);
    }

    public function test_patch_status_fails_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $product = Product::factory()->create();

        $response = $this->patchJson('/api/v1/products/'.$product->id.'/status', [
            'status' => (string) Constant::STATUS_INACTIVE,
        ]);

        $response->assertForbidden();
    }

    public function test_destroy_deletes_product()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $payload = ['id' => $product->id];
        $response = $this->deleteJson('/api/v1/products/'.$product->id, $payload);
        $response->assertNoContent();
    }

    public function test_destroy_fails_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $product = Product::factory()->create();
        $payload = ['id' => $product->id];
        $response = $this->deleteJson('/api/v1/products/'.$product->id, $payload);
        $response->assertForbidden();
    }

    public function test_delete_package_items_deletes_all()
    {
        $this->actingAsAdmin();
        $package = Product::factory()->create(['product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $item1 = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $item2 = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $package->packageItems()->attach([
            $item1->id => ['quantity' => 1],
            $item2->id => ['quantity' => 1],
        ]);
        $payload = ['id' => $package->id];
        $response = $this->deleteJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertNoContent();
        $this->assertCount(0, $package->fresh()->packageItems);
    }

    public function test_delete_package_items_fails_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $package = Product::factory()->create(['product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $payload = ['id' => $package->id];
        $response = $this->deleteJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertForbidden();
    }

    public function test_store_fails_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $commerce = Commerce::factory()->create();
        $category = ProductCategory::factory()->create();
        $payload = [
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'title' => 'SinPermiso',
            'description' => 'No debe crearse',
            'original_price' => 10.00,
            'quantity_total' => 10,
            'quantity_available' => 10,
        ];
        $response = $this->postJson('/api/v1/products', $payload);
        $response->assertForbidden();
    }

    public function test_get_products_by_commerce_returns_404_when_none_found()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        $response = $this->getJson('/api/v1/products/commerce/'.$commerce->id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Products fetched successfully',
                'data' => [],
            ]);
    }

    public function test_get_products_by_commerce_includes_package_items_when_loaded()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $item = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_SINGLE,
        ]);
        $package->packageItems()->attach([
            $item->id => ['quantity' => 2],
        ]);

        $response = $this->getJson('/api/v1/products/commerce/'.$commerce->id);
        $response->assertOk()
            ->assertJsonPath('data.0.package_items.0.id', $item->id)
            ->assertJsonPath('data.0.package_items.0.quantity', 2);
    }

    public function test_get_products_by_commerce_branch_includes_package_items_when_loaded()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $item = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_SINGLE,
        ]);
        $package->packageItems()->attach([
            $item->id => ['quantity' => 1],
        ]);

        $response = $this->getJson('/api/v1/products/commerce/branch/'.$branch->id);
        $response->assertOk()
            ->assertJsonPath('data.0.package_items.0.id', $item->id)
            ->assertJsonPath('data.0.package_items.0.quantity', 1);
    }

    public function test_get_products_by_commerce_branch_returns_404_when_none_found()
    {
        $this->actingAsAdmin();
        $response = $this->getJson('/api/v1/products/commerce/branch/1111');
        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'No products found for the given commerce branch.',
            ]);
    }

    public function test_get_products_by_commerce_returns_404_when_commerce_not_found()
    {
        $this->actingAsAdmin();
        $invalidId = 999999;
        $response = $this->getJson('/api/v1/products/commerce/'.$invalidId);
        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Commerce not found with the specified ID.',
            ]);
    }

    public function test_get_package_items_returns_items()
    {
        $this->actingAsAdmin();
        $package = Product::factory()->create(['product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        // Simular items de paquete
        $item1 = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $item2 = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $package->packageItems()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        $response = $this->getJson('/api/v1/products/commerce/package-items/'.$package->id);
        $response->assertOk()
            ->assertJsonStructure(['data' => ['package_items']])
            ->assertJsonCount(2, 'data.package_items');
    }

    public function test_get_package_items_returns_empty_when_none()
    {
        $this->actingAsAdmin();
        $package = Product::factory()->create(['product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $response = $this->getJson('/api/v1/products/commerce/package-items/'.$package->id);
        $response->assertOk()
            ->assertJson(['data' => ['package_items' => []]]);
    }

    public function test_get_package_items_returns_404_for_invalid_product()
    {
        $this->actingAsAdmin();
        $invalidId = 999999;
        $response = $this->getJson('/api/v1/products/commerce/package-items/'.$invalidId);
        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Product not found with the specified ID.',
            ]);
    }

    public function test_store_package_items_requires_quantity()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
                'quantity_total' => 10,
                'quantity_available' => 10,
            ],
            'package_items' => [
                ['product_id' => $product->id], // Missing quantity
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 10]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.quantity']);
    }

    public function test_store_package_items_rejects_zero_quantity()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
                'quantity_total' => 10,
                'quantity_available' => 10,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 0],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 10]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.quantity']);
    }

    public function test_store_package_items_rejects_negative_quantity()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
                'quantity_total' => 10,
                'quantity_available' => 10,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => -5],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 10]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.quantity']);
    }

    public function test_update_package_items_updates_quantity()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        // SCRUM-361: el compromiso del pack vive por sede en el pivote, igual
        // que el stock de sus componentes.
        $package->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 2,
            'is_published' => false,
        ]);
        $product = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 30,
            'is_published' => true,
        ]);

        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $package->commerce_id,
                // El techo del pack sigue la nueva cantidad (5) del único componente.
                'original_price' => $product->currentSalePrice() * 5,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 2]],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertOk();

        $this->assertEquals(5, $package->fresh()->packageItems()->first()->pivot->quantity);
    }

    /**
     * Regresión SCRUM-306: editar un pack sin enviar package_items (ej. solo
     * cambiar el precio) borraba los items asignados.
     */
    public function test_update_package_items_without_key_keeps_existing_items()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $product = Product::factory()->create([
            'commerce_id' => $package->commerce_id,
        ]);

        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $package->commerce_id,
            ],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);

        $response->assertOk();
        $this->assertDatabaseHas('product_package_items', [
            'product_package_id' => $package->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_store_package_items_prevents_duplicate_products()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
                'quantity_total' => 10,
                'quantity_available' => 10,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 2],
                ['product_id' => $product->id, 'quantity' => 3], // Duplicate
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 10]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.1.product_id']);
    }

    /**
     * SCRUM-361: "excede lo disponible" ya no es un error de
     * package_items.*.quantity aislado — el componente no alcanza ni para
     * un pack, así que el máximo por sede cae a 0 y el rechazo se expresa
     * sobre commerce_branches.*.quantity_available (el compromiso pedido).
     */
    public function test_store_package_items_rejects_quantity_exceeding_available()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        // Componente con solo 5 unidades disponibles en la sede.
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 5,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 10], // Excede las 5 disponibles: ni 1 pack cabe
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 1]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available']);
    }

    public function test_update_package_items_rejects_quantity_exceeding_available()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 1, 'is_published' => false]);
        // Componente con solo 3 unidades disponibles.
        $product = Product::factory()->create(['commerce_id' => $package->commerce_id]);
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 3,
            'is_published' => true,
        ]);

        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $package->commerce_id,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 7], // Excede las 3 disponibles
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 1]],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available']);
    }

    public function test_store_package_items_within_max_packs_succeeds()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        // SCRUM-277 Fase 1: el stock del componente single vive por sede.
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $product->currentSalePrice() * 2,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            // max packs en esta sede = floor(10 / 2) = 5
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 5]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertOk();
    }

    public function test_store_package_items_rejects_quantity_available_exceeding_max_packs()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            // max packs en esta sede = floor(10 / 2) = 5, se piden 6.
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 6]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available'])
            ->assertJsonFragment([
                'commerce_branches.0.quantity_available' => [
                    'The requested quantity_available (6) exceeds the maximum packs available in this branch given current stock (max: 5).',
                ],
            ]);
    }

    public function test_store_package_items_rejects_package_type_product_as_item()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $existingPackage = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Test Package',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => 100,
                'quantity_total' => 1,
                'quantity_available' => 1,
            ],
            'package_items' => [
                ['product_id' => $existingPackage->id, 'quantity' => 1],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 10]],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.product_id']);
    }

    public function test_update_package_items_within_new_max_packs_succeeds()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        // SCRUM-277 Fase 1: el stock del componente single vive por sede.
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 5, 'is_published' => false]);
        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                // El techo del pack sigue la nueva cantidad (5) del componente.
                'original_price' => $product->currentSalePrice() * 5,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            // el compromiso propio anterior (5 packs * 2 = 10) se excluye del
            // cálculo, así que el stock disponible sigue en 10.
            // Nuevo máximo = floor(10 / 5) = 2.
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 2]],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertOk();

        $branchPivot = $package->fresh()->commerceBranches()->first()->pivot;
        $this->assertEquals(2, $branchPivot->quantity_available);
        $this->assertEquals(5, $package->fresh()->packageItems()->first()->pivot->quantity);
    }

    public function test_update_package_items_rejects_quantity_available_exceeding_new_max_packs()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);
        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 5, 'is_published' => false]);
        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            // compromiso propio anterior excluido, disponible sigue en 10.
            // Nuevo máximo = floor(10 / 5) = 2, pero se piden 3.
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 3]],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available'])
            ->assertJsonFragment([
                'commerce_branches.0.quantity_available' => [
                    "The requested quantity_available (3) exceeds the maximum packs available in branch '{$commerceBranch->name}' given current stock (max: 2).",
                ],
            ]);
    }

    public function test_store_package_items_respects_remainder_committed_by_other_packs()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        // SCRUM-277 Fase 1: el stock del componente single vive por sede.
        $product->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        // Pack A already commits 2 packs * 3 units = 6 units, leaving a remainder of 4.
        $packA = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $packA->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 2, 'is_published' => false]);
        $packA->packageItems()->attach($product->id, ['quantity' => 3]);

        $basePayload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack B',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $product->currentSalePrice() * 2,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ];

        // Remainder = 4 units, requested item quantity = 2 -> max packs = floor(4 / 2) = 2.
        $exceedingPayload = $basePayload;
        $exceedingPayload['commerce_branches'] = [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 3]];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $exceedingPayload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available'])
            ->assertJsonFragment([
                'commerce_branches.0.quantity_available' => [
                    'The requested quantity_available (3) exceeds the maximum packs available in this branch given current stock (max: 2).',
                ],
            ]);

        $withinRemainderPayload = $basePayload;
        $withinRemainderPayload['commerce_branches'] = [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 2]];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $withinRemainderPayload);
        $response->assertOk();
    }

    /**
     * SCRUM-361: available_for_packaging pasó de un número global por
     * producto a un valor por sede dentro de commerce_branches[] — se
     * resuelve donde el panel del proveedor lo consume de verdad
     * (getByCommerce, Tarea 6.2), no en show() de un producto individual.
     */
    public function test_product_resource_exposes_available_for_packaging_for_single_products_only()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($branch->id, ['quantity_available' => 3, 'is_published' => false]);
        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $response = $this->getJson('/api/v1/products/commerce/'.$commerce->id);
        $response->assertOk();

        $data = collect($response->json('data'));
        $productData = $data->firstWhere('id', $product->id);
        $packageData = $data->firstWhere('id', $package->id);

        // Single: 10 - (3 packs * 2 unidades) = 4.
        $this->assertSame(4, $productData['commerce_branches'][0]['available_for_packaging']);
        $this->assertNull($packageData['commerce_branches'][0]['available_for_packaging']);
    }

    public function test_available_for_packaging_does_not_n_plus_one_per_associated_package()
    {
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        // SCRUM-277 Fase 1: el stock del componente single vive por sede — sin
        // esto, available_for_packaging quedaría en 0 y el test seguiría "en
        // verde" (solo mide conteo de queries) sin ejercer un cálculo real.
        $product->commerceBranches()->attach($branch->id, [
            'quantity_available' => 100,
            'is_published' => true,
        ]);

        $packA = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $packA->commerceBranches()->attach($branch->id, ['quantity_available' => 1, 'is_published' => false]);
        $packA->packageItems()->attach($product->id, ['quantity' => 1]);

        // Warm up permission/model caches so they do not skew the query count comparison below.
        $this->getJson('/api/v1/products/commerce/'.$commerce->id)->assertOk();

        DB::enableQueryLog();
        $this->getJson('/api/v1/products/commerce/'.$commerce->id)->assertOk();
        $queryCountForOnePackage = count(DB::getQueryLog());
        DB::flushQueryLog();
        DB::disableQueryLog();

        $packB = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $packB->commerceBranches()->attach($branch->id, ['quantity_available' => 1, 'is_published' => false]);
        $packC = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $packC->commerceBranches()->attach($branch->id, ['quantity_available' => 1, 'is_published' => false]);
        $packB->packageItems()->attach($product->id, ['quantity' => 1]);
        $packC->packageItems()->attach($product->id, ['quantity' => 1]);

        DB::enableQueryLog();
        $this->getJson('/api/v1/products/commerce/'.$commerce->id)->assertOk();
        $queryCountForThreePackages = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(
            $queryCountForOnePackage,
            $queryCountForThreePackages,
            'Computing available_for_packaging should issue the same number of queries regardless of how many packages reference the product (no N+1 per package).'
        );
    }
}
