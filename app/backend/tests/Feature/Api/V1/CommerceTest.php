<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear permisos necesarios con guard sanctum
        Permission::findOrCreate('provider.commerces.create', 'sanctum');
        Permission::findOrCreate('provider.commerces.update', 'sanctum');
        Permission::findOrCreate('provider.commerces.show', 'sanctum');
        Permission::findOrCreate('provider.commerces.delete', 'sanctum');
    }

    /**
     * Verifica que un usuario con permiso pueda crear un comercio correctamente.
     *
     * Crea un usuario, le asigna el permiso y envía los datos de comercio, validando la respuesta.
     */
    public function test_user_can_create_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');

        $payload = Commerce::factory(['owner_user_id' => $user->id])->make()->toArray();
        $response = $this->postJson('/api/v1/commerces', $payload);
        $response->assertCreated();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.name', $payload['name']);
    }

    /**
     * Verifica que un usuario con permiso pueda ver el detalle de un comercio.
     *
     * Crea un usuario, le asigna el permiso y consulta un comercio existente, validando la respuesta.
     */
    public function test_user_can_show_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.show');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->getJson('/api/v1/commerces/'.$commerce->id);
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $commerce->id);
    }

    /**
     * Verifica que un usuario con permiso pueda actualizar un comercio existente.
     *
     * Crea un usuario, le asigna el permiso y actualiza un comercio, validando la respuesta.
     */
    public function test_user_can_update_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.update');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
        ]);
        $payload = $commerce->toArray();
        $payload['name'] = 'Nuevo Nombre';
        $response = $this->putJson('/api/v1/commerces/'.$commerce->id, $payload);
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
        $response = $this->deleteJson('/api/v1/commerces/'.$commerce->id);
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

    public function test_cannot_update_commerce_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $commerce = Commerce::factory()->create();
        $payload = $commerce->toArray();
        $payload['name'] = 'Nuevo Nombre';
        $response = $this->putJson('/api/v1/commerces/'.$commerce->id, $payload);
        $response->assertForbidden();
    }

    public function test_cannot_show_commerce_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $commerce = Commerce::factory()->create();
        $response = $this->getJson('/api/v1/commerces/'.$commerce->id);
        $response->assertForbidden();
    }

    /**
     * person_type se deriva de tax_id_type (SCRUM-242): NIT → jurídica.
     */
    public function test_show_commerce_exposes_person_type_juridica_for_nit(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.show');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['tax_id_type' => 'NIT']);
        $response = $this->getJson('/api/v1/commerces/'.$commerce->id);

        $response->assertOk();
        $response->assertJsonPath('data.person_type', 'juridica');
    }

    /**
     * person_type se deriva de tax_id_type (SCRUM-242): CC/CE/PS → natural.
     */
    public function test_show_commerce_exposes_person_type_natural_for_cc(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.show');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['tax_id_type' => 'CC']);
        $response = $this->getJson('/api/v1/commerces/'.$commerce->id);

        $response->assertOk();
        $response->assertJsonPath('data.person_type', 'natural');
    }

    /**
     * electronic_invoicing_required se expone en el resource (SCRUM-242).
     */
    public function test_show_commerce_exposes_electronic_invoicing_required(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.show');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['electronic_invoicing_required' => true]);
        $response = $this->getJson('/api/v1/commerces/'.$commerce->id);

        $response->assertOk();
        $response->assertJsonPath('data.electronic_invoicing_required', true);
    }
}
