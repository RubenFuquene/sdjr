<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ProductCategory;
use App\Constants\Constant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class ProductCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.product_categories.index', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.product_categories.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.product_categories.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.product_categories.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.product_categories.delete', 'guard_name' => 'sanctum']);
    }

    private function actingAsAdmin()
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            'provider.product_categories.index',
            'provider.product_categories.create',
            'provider.product_categories.show',
            'provider.product_categories.update',
            'provider.product_categories.delete',
        ]);
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_index_returns_paginated_list()
    {
        $this->actingAsAdmin();
        ProductCategory::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/product-categories');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_store_creates_product_category()
    {
        $this->actingAsAdmin();
        $payload = [
            'name' => 'Bebidas',
            'description' => 'Bebidas frías y calientes',
        ];
        $response = $this->postJson('/api/v1/product-categories', $payload);
        $response->assertCreated()->assertJsonFragment(['name' => 'Bebidas']);
        $this->assertDatabaseHas('product_categories', ['name' => 'Bebidas']);
    }

    public function test_show_returns_product_category()
    {
        $this->actingAsAdmin();
        $category = ProductCategory::factory()->create();
        $response = $this->getJson('/api/v1/product-categories/' . $category->id);
        $response->assertOk()->assertJsonFragment(['id' => $category->id]);
    }

    public function test_update_modifies_product_category()
    {
        $this->actingAsAdmin();
        $category = ProductCategory::factory()->create();
        $payload = ['name' => 'Snacks'];
        $response = $this->putJson('/api/v1/product-categories/' . $category->id, $payload);
        $response->assertOk()->assertJsonFragment(['name' => 'Snacks']);
        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'name' => 'Snacks']);
    }

    public function test_destroy_deletes_product_category()
    {
        $this->actingAsAdmin();
        $category = ProductCategory::factory()->create();
        $response = $this->deleteJson('/api/v1/product-categories/' . $category->id);
        $response->assertNoContent();        
    }

    public function test_store_fails_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $payload = [
            'name' => 'SinPermiso',
            'description' => 'No debe crearse',
        ];
        $response = $this->postJson('/api/v1/product-categories', $payload);
        $response->assertForbidden();
    }
}
