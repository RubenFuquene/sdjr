<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceMineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.commerces.mine', 'sanctum');
    }

    /**
     * Test: usuario autenticado con permiso puede obtener su comercio
     */
    public function test_user_with_permission_can_get_own_commerce()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.mine');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->getJson('/api/v1/me/commerce');
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $commerce->id);
    }

    /**
     * Test: usuario autenticado sin comercio recibe 404
     */
    public function test_user_with_permission_but_no_commerce_gets_404()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.mine');
        $this->actingAs($user, 'sanctum');
    
        $response = $this->getJson('/api/v1/me/commerce');
        $response->assertStatus(404);
        $response->assertJsonPath('status', false);
    }

    /**
     * Test: usuario sin permiso recibe 403
     */
    public function test_user_without_permission_gets_403()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/me/commerce');
        $response->assertForbidden();
    }
}
