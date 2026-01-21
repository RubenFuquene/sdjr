<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Commerce;
use App\Models\CommercePayoutMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommercePayoutMethodListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.commerce_payout_methods.index', 'guard_name' => 'sanctum']);
    }

    public function test_list_payout_methods_by_commerce_id_success(): void
    {
        $user = User::factory()->create();
        $commerce = Commerce::factory()->create();
        CommercePayoutMethod::factory()->count(3)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum');
        $user->givePermissionTo('provider.commerce_payout_methods.index');

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/payout-methods");
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'commerce_id', 'type', 'bank', 'account_type', 'account_number', 'owner', 'is_primary', 'status', 'created_at', 'updated_at'
                ]
            ],
            'meta', 'links'
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

    public function test_list_payout_methods_by_commerce_id_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerce_payout_methods.index');
        $invalidId = 99999;

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$invalidId}/payout-methods")
            ->assertStatus(404);        
    }
}
