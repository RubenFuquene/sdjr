<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Commerce;
use App\Constants\Constant;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatchCommerceVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        //Crear permiso
        Permission::create(['name' => 'provider.commerces.update']);

        // Crear usuario con permiso
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('provider.commerces.update');
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_patch_commerce_verification_success(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'is_verified',
                ]
            ]);
        $this->assertDatabaseHas('commerces', [
            'id' => $commerce->id,
            'is_verified' => Constant::STATUS_ACTIVE,
        ]);
    }

    public function test_patch_commerce_verification_invalid_status(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => 5
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_verified']);
    }

    public function test_patch_commerce_verification_without_permission(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE
        ]);
        $response->assertStatus(403);
    }
}
