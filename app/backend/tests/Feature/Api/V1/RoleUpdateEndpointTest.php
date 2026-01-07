<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleUpdateEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configurar permisos necesarios para las pruebas
        Permission::firstOrCreate(['name' => 'admin.profiles.roles.update', 'guard_name' => 'sanctum']);
    }

    /**
     * Prueba que un usuario autenticado puede actualizar un rol existente correctamente.
     */
    public function test_it_updates_a_role()
    {
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'admin.roles.update', 'guard_name' => 'sanctum']);
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.profiles.roles.update');
        Sanctum::actingAs($user);
        $role = Role::create(['name' => 'editor', 'description' => 'Edit role', 'status' => 1]);
        $payload = [
            'name' => 'editor-updated',
            'description' => 'Updated description',
            'status' => 1,
        ];
        $response = $this->putJson('/api/v1/roles/'.$role->id, $payload);
        $response->assertOk()
            ->assertJsonFragment([
                'status' => true,
                'message' => 'Role updated successfully',
                'data' => [
                    'id' => $role->id,
                    'name' => 'editor-updated',
                    'description' => 'Updated description',
                    'permissions' => [],
                    'status' => '1',
                    'users_count' => 0,
                ],
            ]);
    }

    /**
     * Prueba que el endpoint retorna 404 al intentar actualizar un rol inexistente.
     */
    public function test_it_returns_404_for_nonexistent_role_on_update()
    {
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'admin.roles.update', 'guard_name' => 'sanctum']);
        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.profiles.roles.update');
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/roles/9999', [
            'name' => 'notfound',
            'description' => 'notfound',
            'permissions' => [],
        ]);
        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => 'Role not found',
                'status' => false,
            ]);
    }
}
