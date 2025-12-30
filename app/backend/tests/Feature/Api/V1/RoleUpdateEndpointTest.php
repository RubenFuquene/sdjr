<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleUpdateEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Configurar permisos necesarios para las pruebas
        Permission::firstOrCreate(['name' => 'admin.roles.update', 'guard_name' => 'sanctum']);
    }
    /**
     * Prueba que un usuario autenticado puede actualizar un rol existente correctamente.
     */
    public function test_it_updates_a_role()
    {        
        $user = User::factory()->create();
        $user->givePermissionTo('admin.roles.update');
        Sanctum::actingAs($user);
        $role = Role::create(['name' => 'editor', 'description' => 'Edit role']);
        $payload = [
            'name' => 'editor-updated',
            'description' => 'Updated description',            
        ];
        $response = $this->putJson('/api/v1/roles/' . $role->id, $payload);
        $response->assertOk()
            ->assertJsonFragment([
                'data' => [
                    'id' => $role->id,
                    'name' => 'editor-updated',
                    'description' => 'Updated description',
                    'permissions' => [],
                    'users_count' => 0,
                ],
                'message' => 'Role updated successfully',
                'status' => true,
            ]);
    }

    
    /**
     * Prueba que el endpoint retorna 404 al intentar actualizar un rol inexistente.
     */
    public function test_it_returns_404_for_nonexistent_role_on_update()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.roles.update');
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/roles/9999', [
            'name' => 'notfound',
            'description' => 'notfound',
            'permissions' => []
        ]);
        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => 'Role not found',
                'status' => false,
            ]);
    }
}
