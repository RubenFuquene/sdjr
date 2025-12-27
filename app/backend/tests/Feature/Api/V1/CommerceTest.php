<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

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
        // Crear permisos necesarios con guard sanctum
        Permission::findOrCreate('provider.commerces.create', 'sanctum');
        Permission::findOrCreate('provider.commerces.update', 'sanctum');
        Permission::findOrCreate('provider.commerces.view', 'sanctum');
        Permission::findOrCreate('provider.commerces.delete', 'sanctum');
    }

    public function test_user_can_create_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');

        $payload = Commerce::factory()->make()->toArray();
        $response = $this->postJson('/api/v1/commerces', $payload);
        $response->assertCreated();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.name', $payload['name']);
    }

    public function test_user_can_view_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->getJson('/api/v1/commerces/' . $commerce->id);
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $commerce->id);
    }

    public function test_user_can_update_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.update');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $payload = $commerce->toArray();
        $payload['name'] = 'Nuevo Nombre';
        $response = $this->putJson('/api/v1/commerces/' . $commerce->id, $payload);
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.name', 'Nuevo nombre');
    }

    public function test_user_can_delete_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->deleteJson('/api/v1/commerces/' . $commerce->id);
        $response->assertStatus(204);
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
