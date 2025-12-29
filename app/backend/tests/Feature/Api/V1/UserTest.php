<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

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
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'admin.users.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.users.index');
        User::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/users');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'last_name', 'email', 'phone', 'roles', 'status', 'created_at', 'updated_at',
                ],
            ],
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
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'admin.users.create', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.users.create');
        Sanctum::actingAs($admin);

        $data = [
            'name' => 'New',
            'last_name' => 'User',
            'email' => 'new@example.com',
            'phone' => '3001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $data);
        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'New', 'last_name' => 'User', 'phone' => '3001234567']);
    }

    /** @test */
    public function authenticated_user_can_view_single_user(): void
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'admin.users.show', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.users.show');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users/'.$user->id);
        $response->assertOk();
        $response->assertJsonFragment(['id' => $user->id]);
    }

    /** @test */
    public function authenticated_user_can_update_user(): void
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'admin.users.update', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.users.update');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $data = ['name' => 'Updated Name', 'last_name' => 'Updated LastName'];
        $response = $this->putJson('/api/v1/users/'.$user->id, $data);
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    /** @test */
    public function authenticated_user_can_delete_user(): void
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'admin.users.delete', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.users.delete');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/v1/users/'.$user->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
