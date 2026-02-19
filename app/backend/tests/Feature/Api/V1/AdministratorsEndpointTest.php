<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministratorsEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el endpoint /api/v1/administrators retorne solo los usuarios con rol admin.
     *
     * Crea un usuario admin y uno normal, autentica como admin y valida que solo el admin esté en la respuesta.
     */
    public function test_it_returns_administrator_users()
    {
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'admin.profiles.administrators.show', 'guard_name' => 'sanctum']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $admin->givePermissionTo('admin.profiles.administrators.show');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/administrators');

        $response->assertOk()
            ->assertJsonFragment(['id' => $admin->id])
            ->assertJsonMissing(['id' => $user->id]);
    }

    /**
     * Verifica que el endpoint /api/v1/administrators requiera autenticación.
     *
     * Intenta acceder sin autenticación y espera un 401.
     */
    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/administrators');
        $response->assertUnauthorized();
    }
}
