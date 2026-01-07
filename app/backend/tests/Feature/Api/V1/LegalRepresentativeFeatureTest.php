<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\LegalRepresentative;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
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
            'provider.legal_representatives.create',
            'provider.legal_representatives.view',
            'provider.legal_representatives.update',
            'provider.legal_representatives.delete',
        ];
        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'sanctum');
        }
        $this->user->givePermissionTo($permissions);
    }

    /**
     * Prueba que un usuario con permisos puede listar los representantes legales.
     */
    public function test_can_list_legal_representatives(): void
    {
        $this->actingAs($this->user);
        LegalRepresentative::factory()->count(2)->create();
        $response = $this->getJson('/api/v1/legal-representatives');
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status', 'data', 'meta',
            ]);
    }

    /**
     * Prueba que un usuario con permisos puede crear un representante legal.
     */
    public function test_can_create_legal_representative(): void
    {
        $this->actingAs($this->user);
        $commerce = Commerce::factory()->create();
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
     * Prueba que un usuario con permisos puede ver el detalle de un representante legal.
     */
    public function test_can_show_legal_representative(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->getJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $legal->id);
    }

    /**
     * Prueba que un usuario con permisos puede actualizar un representante legal.
     */
    public function test_can_update_legal_representative(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
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
     * Prueba que un usuario con permisos puede eliminar (soft delete) un representante legal.
     */
    public function test_can_delete_legal_representative(): void
    {
        $this->actingAs($this->user);
        $legal = LegalRepresentative::factory()->create();
        $response = $this->deleteJson('/api/v1/legal-representatives/'.$legal->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('legal_representatives', ['id' => $legal->id]);
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
}
