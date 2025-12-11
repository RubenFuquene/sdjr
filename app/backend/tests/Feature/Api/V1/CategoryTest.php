<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Constants\Constant;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_categories(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Category::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    public function test_store_creates_category(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'Test Category',
            'icon' => 'https://example.com/icon.png',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/categories', $data);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'Test Category']);
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    public function test_show_returns_category(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create(['name' => 'Unique Category']);
        $response = $this->getJson("/api/v1/categories/{$category->id}");
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Unique Category']);
    }

    public function test_update_updates_category(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = Category::factory()->create(['name' => 'Old Name']);
        $data = ['name' => 'New Name', 'icon' => 'newicon.png', 'status' => Constant::STATUS_INACTIVE];
        $response = $this->putJson("/api/v1/categories/{$category->id}", $data);
        $response->assertStatus(200)->assertJsonFragment(['name' => 'New Name']);
        $this->assertDatabaseHas('categories', ['name' => 'New Name']);
    }

    public function test_destroy_deletes_category(): void
    {
        $user = User::factory()->create();
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
