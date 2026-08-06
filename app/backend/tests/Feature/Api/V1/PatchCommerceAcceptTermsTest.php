<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $commerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
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

    /**
     * SCRUM-334 (IDOR): un aliado no puede aceptar términos en nombre de un comercio ajeno.
     */
    public function test_patch_commerce_accept_terms_fails_for_a_commerce_not_owned_by_the_user(): void
    {
        $commerce = Commerce::factory()->create(['terms_accepted_version' => null]);
        $payload = [
            'terms_accepted_version' => 1,
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', $payload);
        $response->assertForbidden();
        $this->assertDatabaseHas('commerces', ['id' => $commerce->id, 'terms_accepted_version' => null]);
    }

    public function test_patch_commerce_accept_terms_allows_superadmin_for_any_commerce(): void
    {
        Role::findOrCreate('superadmin', 'sanctum');
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        $admin->givePermissionTo('provider.commerces.accept-terms');
        $this->actingAs($admin, 'sanctum');

        $commerce = Commerce::factory()->create();
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', [
            'terms_accepted_version' => 3,
        ]);
        $response->assertOk();
    }

    public function test_patch_commerce_accept_terms_validation_error(): void
    {
        $commerce = Commerce::factory()->create(['owner_user_id' => $this->user->id]);
        $payload = [
            // No version provided
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$commerce->id.'/accept-terms', $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['terms_accepted_version']);
    }

    /**
     * SCRUM-334: un commerce_id inexistente no puede distinguirse de uno
     * ajeno — la validación de ownership corre antes que la existencia,
     * mismo criterio anti-enumeración ya adoptado en el proyecto.
     */
    public function test_patch_commerce_accept_terms_fails_for_a_nonexistent_commerce(): void
    {
        $invalidCommerceID = 999999;
        $payload = [
            'terms_accepted_version' => 1,
        ];
        $response = $this->patchJson('/api/v1/commerces/'.$invalidCommerceID.'/accept-terms', $payload);
        $response->assertStatus(403);
    }
}
