<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Constants\Constant;
use Spatie\Permission\Models\Permission;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_categories(): void
    {
        Permission::firstOrCreate(['name' => 'categories.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('categories.index');
        Sanctum::actingAs($user);
        Category::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta',
                'links',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_category(): void
    {
        Permission::firstOrCreate(['name' => 'categories.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('categories.create');
        Sanctum::actingAs($user);
        $data = [
            'name' => 'Test Category',
            'icon' => 'https://example.com/icon.png',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/categories', $data);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id', 'name', 'icon', 'status', 'created_at', 'updated_at'
                ]
            ]);
        $response->assertJsonPath('data.name', 'Test category');
        $this->assertDatabaseHas('categories', ['name' => 'Test category']);
    }

    public function test_show_returns_category(): void
    {
        Permission::firstOrCreate(['name' => 'categories.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('categories.show');
        Sanctum::actingAs($user);
        $category = Category::factory()->create(['name' => 'Unique Category']);
        $response = $this->getJson("/api/v1/categories/{$category->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id', 'name', 'icon', 'status', 'created_at', 'updated_at'
                ]
            ]);
        $response->assertJsonPath('data.name', 'Unique category');
    }

    public function test_update_updates_category(): void
    {
        Permission::firstOrCreate(['name' => 'categories.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('categories.update');
        Sanctum::actingAs($user);
        $category = Category::factory()->create(['name' => 'Old Name']);
        $data = ['name' => 'New Name', 'icon' => 'newicon.png', 'status' => Constant::STATUS_INACTIVE];
        $response = $this->putJson("/api/v1/categories/{$category->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id', 'name', 'icon', 'status', 'created_at', 'updated_at'
                ]
            ]);
        $response->assertJsonPath('data.name', 'New name');
        $this->assertDatabaseHas('categories', ['name' => 'New name']);
    }

    public function test_destroy_deletes_category(): void
    {
        Permission::firstOrCreate(['name' => 'categories.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('categories.delete');
        Sanctum::actingAs($user);
        $category = Category::factory()->create();
        $response = $this->deleteJson("/api/v1/categories/{$category->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_unauthenticated_user_cannot_access(): void
    {
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(401);
    }
}
