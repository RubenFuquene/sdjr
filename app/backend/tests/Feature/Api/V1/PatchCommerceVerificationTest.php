<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PatchCommerceVerificationTest extends TestCase
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

    public function test_patch_commerce_verification_success(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE,
            'message' => 'Tu comercio ha sido verificado exitosamente y cumple con todos los requisitos.',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'is_verified',
                ],
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
            'is_verified' => 5,
            'message' => 'Este es un mensaje de prueba para validación.',
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
            'is_verified' => Constant::STATUS_ACTIVE,
            'message' => 'Este es un mensaje de prueba para verificación.',
        ]);
        $response->assertStatus(403);
    }

    public function test_patch_commerce_verification_without_message(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_patch_commerce_verification_message_too_short(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE,
            'message' => 'Corto',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_patch_commerce_verification_message_too_long(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $longMessage = str_repeat('a', 501);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::STATUS_ACTIVE,
            'message' => $longMessage,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_patch_commerce_verification_rejected_with_message(): void
    {
        $commerce = Commerce::factory()->create(['is_verified' => Constant::STATUS_INACTIVE]);
        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", [
            'is_verified' => Constant::COMMERCE_REJECTED,
            'message' => 'Lamentablemente tu comercio no cumple con los requisitos mínimos establecidos.',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'is_verified',
                ],
            ]);
        $this->assertDatabaseHas('commerces', [
            'id' => $commerce->id,
            'is_verified' => Constant::COMMERCE_REJECTED,
        ]);
    }
}
