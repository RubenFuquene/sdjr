<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CommercePayoutMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercePayoutMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_payout_methods(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        CommercePayoutMethod::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/commerces/payout-method');
        $response->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_can_create_payout_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $payload = CommercePayoutMethod::factory()->make()->toArray();
        $payload['commerce_id'] = 1;
        $payload['owner_id'] = $user->id;

        $response = $this->postJson('/api/v1/commerces/payout-method', $payload);
        $response->assertCreated()->assertJsonStructure(['data' => ['id', 'commerce_id', 'type']]);
    }

    public function test_can_show_payout_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $method = CommercePayoutMethod::factory()->create();

        $response = $this->getJson('/api/v1/commerces/payout-method/' . $method->id);
        $response->assertOk()->assertJsonStructure(['data' => ['id', 'commerce_id', 'type']]);
    }

    public function test_can_update_payout_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $method = CommercePayoutMethod::factory()->create();
        $payload = ['account_number' => '1234 3456 3456'];

        $response = $this->putJson('/api/v1/commerces/payout-method/' . $method->id, $payload);
        $response->assertOk()->assertJsonFragment(['account_number' => '1234 3456 3456']);
    }

    public function test_can_delete_payout_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $method = CommercePayoutMethod::factory()->create();

        $response = $this->deleteJson('/api/v1/commerces/payout-method/' . $method->id);
        $response->assertNoContent();
    }
}
