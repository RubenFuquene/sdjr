<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_list_users(): void
    {
        $user = User::factory()->create();
        User::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/users');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'email', 'roles', 'permissions', 'created_at', 'updated_at'
                ]
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/v1/users');
        $response->assertUnauthorized();
    }

    /** @test */
    public function authenticated_user_can_create_user(): void
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $data);
        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'New User']);
    }

    /** @test */
    public function authenticated_user_can_view_single_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users/' . $user->id);
        $response->assertOk();
        $response->assertJsonFragment(['id' => $user->id]);
    }

    /** @test */
    public function authenticated_user_can_update_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $data = ['name' => 'Updated Name'];
        $response = $this->putJson('/api/v1/users/' . $user->id, $data);
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    /** @test */
    public function authenticated_user_can_delete_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/v1/users/' . $user->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}