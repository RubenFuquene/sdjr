<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\EstablishmentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362: GET /commerces/{commerce_id}/fiscal-codes.
 */
class CommerceFiscalCodesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
    }

    public function test_owner_can_list_fiscal_codes_for_their_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.create');
        $this->actingAs($user, 'sanctum');

        $establishmentType = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RETAIL]);
        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'establishment_type_id' => $establishmentType->id,
        ]);

        $response = $this->getJson('/api/v1/commerces/'.$commerce->id.'/fiscal-codes');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $values = collect($response->json('data'))->pluck('value');
        $this->assertTrue($values->contains('licor_ley223'));
        $this->assertFalse($values->contains('inc_8_preparado'));
    }

    public function test_cannot_list_fiscal_codes_for_a_commerce_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.create');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create();

        $response = $this->getJson('/api/v1/commerces/'.$commerce->id.'/fiscal-codes');

        $response->assertForbidden();
    }

    public function test_cannot_list_fiscal_codes_without_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->getJson('/api/v1/commerces/'.$commerce->id.'/fiscal-codes');

        $response->assertForbidden();
    }
}
