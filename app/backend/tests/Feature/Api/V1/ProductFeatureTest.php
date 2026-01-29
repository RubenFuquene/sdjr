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
        $this->actingAsAdmin();
        $commerce = Commerce::factory()->create();
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
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $commerce_branch = CommerceBranch::factory()->create([
            'commerce_id' => $product->commerce_id,
        ]);
        $payload = [
            'product' => [
                'commerce_id' => $product->commerce_id,
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

    public function test_destroy_deletes_product()
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $response = $this->deleteJson('/api/v1/products/'.$product->id);
        $response->assertNoContent();
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
        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'No products found for the specified commerce.',
            ]);
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
}
