<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EstablishmentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class EstablishmentTypeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear permisos necesarios
        Permission::findOrCreate('establishment_types.create');
        Permission::findOrCreate('establishment_types.update');
        Permission::findOrCreate('establishment_types.view');
        Permission::findOrCreate('establishment_types.delete');
    }

    public function test_user_can_create_establishment_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('establishment_types.create');
        $this->actingAs($user, 'sanctum');

        $payload = EstablishmentType::factory()->make()->toArray();
        $response = $this->postJson('/api/v1/establishment-types', $payload);
        $response->assertCreated();
        $response->assertJsonPath('data.name', $payload['name']);
    }

    public function test_user_can_view_establishment_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('establishment_types.view');
        $this->actingAs($user, 'sanctum');

        $type = EstablishmentType::factory()->create();
        $response = $this->getJson('/api/v1/establishment-types/' . $type->id);
        $response->assertOk();
        $response->assertJsonPath('data.id', $type->id);
    }

    public function test_user_can_update_establishment_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('establishment_types.update');
        $this->actingAs($user, 'sanctum');

        $type = EstablishmentType::factory()->create();
        $payload = ['name' => 'Nuevo Tipo', 'code' => 'NEWCODE'];
        $response = $this->putJson('/api/v1/establishment-types/' . $type->id, $payload);
        $response->assertOk();
        $response->assertJsonPath('data.name', 'Nuevo tipo');
    }

    public function test_user_can_delete_establishment_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('establishment_types.delete');
        $this->actingAs($user, 'sanctum');

        $type = EstablishmentType::factory()->create();
        $response = $this->deleteJson('/api/v1/establishment-types/' . $type->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('establishment_types', ['id' => $type->id]);
    }

    public function test_cannot_create_establishment_type_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $payload = EstablishmentType::factory()->make()->toArray();
        $response = $this->postJson('/api/v1/establishment-types', $payload);
        $response->assertForbidden();
    }
}
