<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el endpoint retorna la información del usuario autenticado correctamente.
     */
    public function test_it_returns_authenticated_user_info()
    {
        Role::create(['name' => 'superadmin']);
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'roles', 'permissions'],
                'message',
                'status',
            ])
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'message' => 'User information retrieved successfully',
                'status' => true,
            ]);
    }

    /**
     * Prueba que se requiere autenticación para consultar la información del usuario.
     */
    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/me');
        $response->assertUnauthorized();
    }

    /**
     * Prueba que el endpoint retorna los roles y permisos del usuario autenticado correctamente.
     */
    public function test_it_returns_authenticated_user_permissions_and_roles()
    {
        Role::create(['name' => 'superadmin', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/permissions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['roles', 'permissions'],
                'message',
                'status',
            ])
            ->assertJsonFragment([
                'roles' => ['superadmin'],
                'permissions' => [],
                'message' => 'Permissions and roles retrieved successfully',
                'status' => true,
            ]);
    }
}
