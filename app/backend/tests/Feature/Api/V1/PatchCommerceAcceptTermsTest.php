<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PatchCommerceAcceptTermsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the permission and assign
        Permission::findOrCreate('provider.commerces.accept-terms', 'sanctum');
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('provider.commerces.accept-terms');
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_patch_commerce_accept_terms_success(): void
    {
        $commerce = Commerce::factory()->create();
        $payload = [
            'terms_accepted_version' => 2,
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', $payload);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'terms_accepted_at',
                    'terms_accepted_version',
                ],
            ]);
        $this->assertDatabaseHas('commerces', [
            'id' => $commerce->id,
            'terms_accepted_version' => 2,
        ]);
    }

    public function test_patch_commerce_accept_terms_forbidden(): void
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $payload = [
            'terms_accepted_version' => 1,
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', $payload);
        $response->assertStatus(403);
    }

    public function test_patch_commerce_accept_terms_validation_error(): void
    {
        $commerce = Commerce::factory()->create();
        $payload = [
            // No version provided
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['terms_accepted_version']);
    }

    public function test_patch_commerce_accept_terms_not_found(): void
    {
        $invalidCommerceID = 999999;
        $payload = [
            'terms_accepted_version' => 1,
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$invalidCommerceID.'/accept-terms', $payload);
        $response->assertStatus(404);
    }
}
