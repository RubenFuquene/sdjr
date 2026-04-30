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
                'quantity_total' => 50,
                'quantity_available' => 50,
                'expires_at' => now()->addMonth()->toDateTimeString(),
            ],
            'commerce_branch_ids' => [
                $commerce_branch->id,
            ],
        ];
        $response = $this->postJson('/api/v1/products/commerce', $payload);
        $response->assertCreated()->assertJsonFragment(['title' => 'Café Premium']);
        $this->assertDatabaseHas('products', ['title' => 'Café Premium']);
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
            'commerce_branch_ids' => [
                $commerce_branch->id,
            ],
        ];
        $response = $this->putJson('/api/v1/products/'.$product->id, $payload);
        $response->assertOk()->assertJsonFragment(['title' => 'Té Verde']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Té Verde']);
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
            'commerce_branch_ids' => [$commerceBranch->id],
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
            'commerce_branch_ids' => [$commerceBranch->id],
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
            'commerce_branch_ids' => [$commerceBranch->id],
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
        $product = Product::factory()->create([
            'commerce_id' => $package->commerce_id,
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $package->commerce_id,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'commerce_branch_ids' => [$commerceBranch->id],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertOk();

        $this->assertEquals(5, $package->fresh()->packageItems()->first()->pivot->quantity);
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
            'commerce_branch_ids' => [$commerceBranch->id],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.1.product_id']);
    }

    public function test_store_package_items_rejects_quantity_exceeding_available()
    {
        $user = $this->actingAsAdmin();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $category = ProductCategory::factory()->create();
        // Producto con solo 5 unidades disponibles
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'quantity_total' => 10,
            'quantity_available' => 5,
        ]);

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
                ['product_id' => $product->id, 'quantity' => 10], // Excede las 5 disponibles
            ],
            'commerce_branch_ids' => [$commerceBranch->id],
        ];

        $response = $this->postJson('/api/v1/products/commerce/package-items', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.quantity']);
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
        // Producto con solo 3 unidades disponibles
        $product = Product::factory()->create([
            'commerce_id' => $package->commerce_id,
            'quantity_total' => 10,
            'quantity_available' => 3,
        ]);

        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $package->commerce_id,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 7], // Excede las 3 disponibles
            ],
            'commerce_branch_ids' => [$commerceBranch->id],
        ];

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_items.0.quantity']);
    }
}
