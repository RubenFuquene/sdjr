<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class CommerceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear permisos necesarios
        Permission::findOrCreate('commerces.create');
        Permission::findOrCreate('commerces.update');
        Permission::findOrCreate('commerces.view');
        Permission::findOrCreate('commerces.delete');
    }

    public function test_user_can_create_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('commerces.create');
        $this->actingAs($user, 'sanctum');

        $payload = Commerce::factory()->make()->toArray();
        $response = $this->postJson('/api/v1/commerces', $payload);
        $response->assertCreated();
        $response->assertJsonPath('data.name', $payload['name']);
    }

    public function test_user_can_view_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('commerces.view');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->getJson('/api/v1/commerces/' . $commerce->id);
        $response->assertOk();
        $response->assertJsonPath('data.id', $commerce->id);
    }

    public function test_user_can_update_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('commerces.update');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $payload = ['name' => 'Nuevo Nombre'];
        $response = $this->putJson('/api/v1/commerces/' . $commerce->id, $payload);
        $response->assertOk();
        $response->assertJsonPath('data.name', 'Nuevo Nombre');
    }

    public function test_user_can_delete_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('commerces.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->deleteJson('/api/v1/commerces/' . $commerce->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('commerces', ['id' => $commerce->id]);
    }

    public function test_cannot_create_commerce_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $payload = Commerce::factory()->make()->toArray();
        $response = $this->postJson('/api/v1/commerces', $payload);
        $response->assertForbidden();
    }
}
