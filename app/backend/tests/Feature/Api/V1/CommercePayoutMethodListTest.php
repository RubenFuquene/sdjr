<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommercePayoutMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercePayoutMethodListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.commerce_payout_methods.index', 'guard_name' => 'sanctum']);
    }

    public function test_list_payout_methods_by_commerce_id_success(): void
    {
        $user = User::factory()->create();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        CommercePayoutMethod::factory()->count(3)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum');
        $user->givePermissionTo('provider.commerce_payout_methods.index');

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/payout-methods");
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'commerce_id', 'type', 'bank', 'account_type', 'account_number', 'owner', 'is_primary', 'status', 'created_at', 'updated_at',
                ],
            ],
            'meta', 'links',
        ]);
    }

    public function test_list_payout_methods_by_commerce_id_unauthorized(): void
    {
        $user = User::factory()->create();
        $commerce = Commerce::factory()->create();
        $this->actingAs($user, 'sanctum');
        // No permission
        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/payout-methods");
        $response->assertForbidden();
    }

    /**
     * SCRUM-334 (IDOR): cuentas bancarias — un aliado no puede ver los
     * métodos de pago de un comercio ajeno.
     */
    public function test_list_payout_methods_by_commerce_id_fails_for_a_commerce_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerce_payout_methods.index');
        $this->actingAs($user, 'sanctum');
        $commerce = Commerce::factory()->create();
        CommercePayoutMethod::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/payout-methods");
        $response->assertForbidden();
    }

    public function test_list_payout_methods_by_commerce_id_allows_superadmin_for_any_commerce(): void
    {
        Role::findOrCreate('superadmin', 'sanctum');
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        $admin->givePermissionTo('provider.commerce_payout_methods.index');
        $this->actingAs($admin, 'sanctum');
        $commerce = Commerce::factory()->create();
        CommercePayoutMethod::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/payout-methods");
        $response->assertOk();
    }

    /**
     * SCRUM-334: un commerce_id inexistente no puede distinguirse de uno
     * ajeno — mismo criterio anti-enumeración ya adoptado en el proyecto.
     */
    public function test_list_payout_methods_by_commerce_id_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerce_payout_methods.index');
        $invalidId = 99999;

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$invalidId}/payout-methods")
            ->assertForbidden();
    }
}
