<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionStoreEndpointTest extends TestCase
{
    use RefreshDatabase;

    
    /**
     * Prueba que un usuario autenticado y con permiso puede crear un nuevo permiso.
     */
    public function test_it_creates_a_permission()
    {
        Permission::firstOrCreate(['name' => 'admin.permissions.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.permissions.create');
        Sanctum::actingAs($user);
        
        $payload = [
            'name' => 'test.permission',
            'description' => 'Permiso de prueba'
        ];
        $response = $this->postJson('/api/v1/permissions', $payload);
        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'test.permission',
                'description' => 'Permiso de prueba'
            ]);
    }

    
    /**
     * Prueba que se requiere autenticación para crear un permiso.
     */
    public function test_it_requires_authentication()
    {
        $payload = [
            'name' => 'test.permission',
            'description' => 'Permiso de prueba'
        ];
        $response = $this->postJson('/api/v1/permissions', $payload);
        $response->assertUnauthorized();
    }
}
