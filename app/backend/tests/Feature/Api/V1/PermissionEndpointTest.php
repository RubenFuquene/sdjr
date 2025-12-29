<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionEndpointTest extends TestCase
{
    use RefreshDatabase;

    
    /**
     * Prueba que el endpoint retorna todos los permisos correctamente para un usuario autenticado.
     */
    public function it_returns_all_permissions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.edit']);

        $response = $this->getJson('/api/v1/permissions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['permissions'],
                'message',
                'status',
            ])
            ->assertJsonFragment([
                'permissions' => ['users.view', 'users.edit'],
                'message' => 'Permissions retrieved successfully',
                'status' => true,
            ]);
    }

    
    /**
     * Prueba que se requiere autenticación para consultar los permisos.
     */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/permissions');
        $response->assertUnauthorized();
    }
}
