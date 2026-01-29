<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatchRoleStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permiso
        Permission::create(['name' => 'admin.profiles.roles.update', 'guard_name' => 'sanctum']);

        // Crear usuario con permiso
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('admin.profiles.roles.update');
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_patch_role_status_success(): void
    {
        $role = Role::create(['name' => 'admin', 'status' => Constant::STATUS_ACTIVE]);
        $response = $this->patchJson("/api/v1/roles/{$role->id}/status", ['status' => Constant::STATUS_INACTIVE]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'status',
                ],
            ]);
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'status' => Constant::STATUS_INACTIVE,
        ]);
    }

    public function test_patch_role_status_invalid_status(): void
    {
        $role = Role::create(['name' => 'admin', 'status' => Constant::STATUS_ACTIVE]);
        $response = $this->patchJson("/api/v1/roles/{$role->id}/status", ['status' => 5]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_patch_role_status_without_permission(): void
    {
        $role = Role::create(['name' => 'admin', 'status' => Constant::STATUS_ACTIVE]);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->patchJson("/api/v1/roles/{$role->id}/status", ['status' => Constant::STATUS_INACTIVE]);
        $response->assertStatus(403);
    }
}
