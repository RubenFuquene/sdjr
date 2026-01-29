<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BankTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un usuario autenticado y autorizado puede listar bancos.
     */
    public function test_can_list_banks()
    {
        Permission::findOrCreate('admin.params.banks.index', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.banks.index');
        Sanctum::actingAs($user);
        Bank::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/banks');
        $response->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede crear un banco.
     */
    public function test_can_create_bank()
    {
        Permission::findOrCreate('admin.params.banks.create', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.banks.create');
        Sanctum::actingAs($user);
        $payload = [
            'name' => 'Banco de Prueba',
            'code' => 'TESTBANK',
        ];
        $response = $this->postJson('/api/v1/banks', $payload);
        $response->assertCreated()->assertJsonPath('data.name', 'Banco de prueba');
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede ver el detalle de un banco.
     */
    public function test_can_show_bank()
    {
        Permission::findOrCreate('admin.params.banks.index', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.banks.index');
        Sanctum::actingAs($user);
        $bank = Bank::factory()->create();
        $response = $this->getJson('/api/v1/banks/'.$bank->id);
        $response->assertOk()->assertJsonPath('data.id', $bank->id);
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede actualizar un banco.
     */
    public function test_can_update_bank()
    {
        Permission::findOrCreate('admin.params.banks.update', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.banks.update');
        Sanctum::actingAs($user);
        $bank = Bank::factory()->create();
        $payload = [
            'name' => 'Banco Actualizado',
            'code' => 'UPDATEDBANK',
        ];
        $response = $this->putJson('/api/v1/banks/'.$bank->id, $payload);
        $response->assertOk()->assertJsonPath('data.name', 'Banco actualizado');
    }

    /**
     * Prueba que un usuario autenticado y autorizado puede eliminar (soft delete) un banco.
     */
    public function test_can_delete_bank()
    {
        Permission::findOrCreate('admin.params.banks.delete', 'sanctum');
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.banks.delete');
        Sanctum::actingAs($user);
        $bank = Bank::factory()->create();
        $response = $this->deleteJson('/api/v1/banks/'.$bank->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('banks', ['id' => $bank->id]);
    }

    /**
     * Prueba que un usuario sin permisos no puede crear un banco.
     */
    public function test_cannot_create_bank_without_permission()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'name' => 'Banco Sin Permiso',
            'code' => 'NOPERM',
        ];
        $response = $this->postJson('/api/v1/banks', $payload);
        $response->assertForbidden();
    }
}
