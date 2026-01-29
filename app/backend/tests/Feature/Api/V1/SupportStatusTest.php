<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\SupportStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SupportStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un usuario autenticado y autorizado puede listar estados de soporte.
     */
    public function test_can_list_support_statuses()
    {
        Permission::findOrCreate('admin.params.support_statuses.index', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.support_statuses.index');
        Sanctum::actingAs($user);
        SupportStatus::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/support-statuses');
        $response->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede crear un estado de soporte.
     */
    public function test_can_create_support_status()
    {
        Permission::findOrCreate('admin.params.support_statuses.create', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.support_statuses.create');
        Sanctum::actingAs($user);
        $payload = [
            'name' => 'Abierto',
            'code' => 'OPEN',
            'color' => 'green',
        ];
        $response = $this->postJson('/api/v1/support-statuses', $payload);
        $response->assertCreated()->assertJsonPath('data.name', 'Abierto');
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede ver el detalle de un estado de soporte.
     */
    public function test_can_show_support_status()
    {
        Permission::findOrCreate('admin.params.support_statuses.view', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.support_statuses.view');
        Sanctum::actingAs($user);
        $status = SupportStatus::factory()->create();
        $response = $this->getJson('/api/v1/support-statuses/'.$status->id);
        $response->assertOk()->assertJsonPath('data.id', $status->id);
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede actualizar un estado de soporte.
     */
    public function test_can_update_support_status()
    {
        Permission::findOrCreate('admin.params.support_statuses.update', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.support_statuses.update');
        Sanctum::actingAs($user);
        $status = SupportStatus::factory()->create();
        $payload = [
            'name' => 'Cerrado',
            'code' => 'CLOSED',
            'color' => 'red',
        ];
        $response = $this->putJson('/api/v1/support-statuses/'.$status->id, $payload);
        $response->assertOk()->assertJsonPath('data.name', 'Cerrado');
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede eliminar (soft delete) un estado de soporte.
     */
    public function test_can_delete_support_status()
    {
        Permission::findOrCreate('admin.params.support_statuses.delete', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.support_statuses.delete');
        Sanctum::actingAs($user);
        $status = SupportStatus::factory()->create();
        $response = $this->deleteJson('/api/v1/support-statuses/'.$status->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('support_statuses', ['id' => $status->id]);
    }

    /**
     * Prueba que un usuario sin permisos no puede crear un estado de soporte.
     */
    public function test_cannot_create_support_status_without_permission()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'name' => 'Sin Permiso',
            'code' => 'NOPERM',
            'color' => 'gray',
        ];
        $response = $this->postJson('/api/v1/support-statuses', $payload);
        $response->assertForbidden();
    }
}
