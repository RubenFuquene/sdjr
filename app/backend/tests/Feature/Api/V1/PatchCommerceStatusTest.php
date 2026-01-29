<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PatchCommerceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permiso
        Permission::create(['name' => 'provider.commerces.update', 'guard_name' => 'sanctum']);

        // Crear usuario con permiso
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('provider.commerces.update');
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_patch_commerce_status_success(): void
    {
        $commerce = Commerce::factory()->create(['is_active' => Constant::STATUS_ACTIVE]);
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/status', [
            'is_active' => Constant::STATUS_INACTIVE,
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'is_active',
                ],
            ]);
        $this->assertDatabaseHas('commerces', [
            'id' => $commerce->id,
            'is_active' => Constant::STATUS_INACTIVE,
        ]);
    }

    public function test_patch_commerce_status_invalid_status(): void
    {
        $commerce = Commerce::factory()->create(['is_active' => Constant::STATUS_ACTIVE]);
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/status', [
            'is_active' => 5,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active']);
    }

    public function test_patch_commerce_status_without_permission(): void
    {
        $commerce = Commerce::factory()->create(['is_active' => Constant::STATUS_ACTIVE]);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/status', [
            'is_active' => Constant::STATUS_INACTIVE,
        ]);
        $response->assertStatus(403);
    }
}
