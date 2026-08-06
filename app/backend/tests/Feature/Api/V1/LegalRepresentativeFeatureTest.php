<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\LegalRepresentative;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LegalRepresentativeFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $permissions = [
            'provider.legal_representatives.index',
            'provider.legal_representatives.create',
            'provider.legal_representatives.show',
            'provider.legal_representatives.update',
            'provider.legal_representatives.delete',
        ];
        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'sanctum');
        }
        $this->user->givePermissionTo($permissions);
    }

    /**
     * Prueba que un usuario con permisos puede listar los representantes legales de sus propios comercios.
     */
    public function test_can_list_legal_representatives(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        LegalRepresentative::factory()->count(2)->create(['commerce_id' => $ownCommerce->id]);
        $response = $this->getJson('/api/v1/legal-representatives');
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'status', 'data', 'meta',
            ]);
    }

    /**
     * SCRUM-334: el listado no incluye representantes legales de comercios ajenos.
     */
    public function test_index_excludes_legal_representatives_from_commerces_not_owned_by_user(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        LegalRepresentative::factory()->create(['commerce_id' => $ownCommerce->id]);
        LegalRepresentative::factory()->create();

        $response = $this->getJson('/api/v1/legal-representatives');
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * Prueba que un usuario con permisos puede crear un representante legal en su propio comercio.
     */
    public function test_can_create_legal_representative(): void
    {
        $this->actingAs($this->user);
        $commerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $payload = [
            'commerce_id' => $commerce->id,
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'document' => '1234567890',
            'document_type' => 'CC',
            'email' => 'juan.perez@example.com',
            'phone' => '3001234567',
            'is_primary' => true,
        ];
        $response = $this->postJson('/api/v1/legal-representatives', $payload);
        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Juan');
    }

    /**
     * SCRUM-334 (IDOR): un aliado no puede crear un representante legal en un comercio ajeno.
     */
    public function test_create_rejects_a_commerce_id_not_owned_by_the_user(): void
    {
        $this->actingAs($this->user);
        $otherCommerce = Commerce::factory()->create();
        $payload = [
            'commerce_id' => $otherCommerce->id,
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'document' => '1234567890',
            'document_type' => 'CC',
        ];
        $response = $this->postJson('/api/v1/legal-representatives', $payload);
        $response->assertForbidden();
        $this->assertDatabaseMissing('legal_representatives', ['commerce_id' => $otherCommerce->id]);
    }

    /**
     * Prueba que un usuario con permisos puede ver el detalle de un representante legal de su propio comercio.
     */
    public function test_can_show_legal_representative(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $legal = LegalRepresentative::factory()->create(['commerce_id' => $ownCommerce->id]);
        $response = $this->getJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $legal->id);
    }

    /**
     * SCRUM-334 (IDOR): un aliado no puede ver el representante legal de un comercio ajeno.
     */
    public function test_show_fails_for_a_legal_representative_not_owned_by_the_user(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->getJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertForbidden();
    }

    /**
     * Prueba que un usuario con permisos puede actualizar un representante legal de su propio comercio.
     */
    public function test_can_update_legal_representative(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $legal = LegalRepresentative::factory()->create(['commerce_id' => $ownCommerce->id]);
        $payload = [
            'commerce_id' => $legal->commerce_id,
            'name' => 'Carlos',
            'last_name' => 'Ramírez',
            'document' => '9876543210',
            'document_type' => 'CE',
            'email' => 'carlos.ramirez@example.com',
            'phone' => '3109876543',
            'is_primary' => false,
        ];
        $response = $this->putJson('/api/v1/legal-representatives/'.$legal->id, $payload);
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Carlos');
    }

    /**
     * SCRUM-334 (IDOR): un aliado no puede actualizar el representante legal de un comercio ajeno.
     */
    public function test_update_fails_for_a_legal_representative_not_owned_by_the_user(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
        $payload = [
            'name' => 'Carlos',
            'last_name' => 'Ramírez',
            'document' => '9876543210',
            'document_type' => 'CE',
        ];
        $response = $this->putJson('/api/v1/legal-representatives/'.$legal->id, $payload);
        $response->assertForbidden();
        $this->assertDatabaseHas('legal_representatives', ['id' => $legal->id, 'name' => $legal->name]);
    }

    /**
     * SCRUM-334: un intento de reasignar el representante propio a un comercio ajeno vía payload
     * se ignora en silencio (commerce_id no está en rules() para update) — mismo criterio ya
     * aprobado y en producción para UpdateCommerceBranchRequest (PR #118, SCRUM-287).
     */
    public function test_update_ignores_an_attempt_to_reassign_the_commerce(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $otherCommerce = Commerce::factory()->create();
        $legal = LegalRepresentative::factory()->create(['commerce_id' => $ownCommerce->id]);

        $payload = [
            'commerce_id' => $otherCommerce->id,
            'name' => 'Carlos',
            'last_name' => 'Ramírez',
            'document' => '9876543210',
            'document_type' => 'CE',
        ];
        $response = $this->putJson('/api/v1/legal-representatives/'.$legal->id, $payload);
        $response->assertOk()->assertJsonPath('data.name', 'Carlos');
        $this->assertDatabaseHas('legal_representatives', ['id' => $legal->id, 'commerce_id' => $ownCommerce->id]);
    }

    /**
     * Prueba que un usuario con permisos puede eliminar (soft delete) un representante legal de su propio comercio.
     */
    public function test_can_delete_legal_representative(): void
    {
        $this->actingAs($this->user);
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $legal = LegalRepresentative::factory()->create(['commerce_id' => $ownCommerce->id]);
        $response = $this->deleteJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('legal_representatives', ['id' => $legal->id]);
    }

    /**
     * SCRUM-334 (IDOR): un aliado no puede eliminar el representante legal de un comercio ajeno.
     */
    public function test_delete_fails_for_a_legal_representative_not_owned_by_the_user(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->deleteJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('legal_representatives', ['id' => $legal->id, 'deleted_at' => null]);
    }

    /**
     * SCRUM-334: superadmin conserva acceso a representantes legales de cualquier comercio.
     */
    public function test_superadmin_can_show_any_commerce_legal_representative(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('superadmin', 'sanctum');
        $admin->assignRole('superadmin');
        $admin->givePermissionTo('provider.legal_representatives.show');
        $this->actingAs($admin);

        $legal = LegalRepresentative::factory()->create();
        $response = $this->getJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertOk()->assertJsonPath('data.id', $legal->id);
    }

    /**
     * Prueba que un usuario sin permisos no puede crear un representante legal.
     */
    public function test_cannot_create_without_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $commerce = Commerce::factory()->create();
        $payload = [
            'commerce_id' => $commerce->id,
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'document' => '1234567890',
            'document_type' => 'CC',
        ];
        $response = $this->postJson('/api/v1/legal-representatives', $payload);
        $response->assertForbidden();
    }

    public function test_cannot_update_without_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $commerce = Commerce::factory()->create();

        $payload = [
            'commerce_id' => $commerce->id,
            'name' => 'Carlos',
            'last_name' => 'Ramírez',
            'document' => '9876543210',
            'document_type' => 'CE',
        ];
        $legal = LegalRepresentative::factory()->create();
        $response = $this->putJson('/api/v1/legal-representatives/'.$legal->id, $payload);
        $response->assertForbidden();
    }

    public function test_cannot_delete_without_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->deleteJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertForbidden();
    }

    public function test_cannot_show_without_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->getJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertForbidden();
    }
}
