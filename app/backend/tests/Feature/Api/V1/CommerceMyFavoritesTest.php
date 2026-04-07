<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceMyFavoritesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('customer.commerce-branches.my-favorites', 'sanctum');
    }

    public function test_user_with_permission_can_get_favorite_commerce_branches(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('customer.commerce-branches.my-favorites');
        $this->actingAs($user, 'sanctum');

        $commerceA = Commerce::factory()->create();
        $commerceB = Commerce::factory()->create();
        $commerceC = Commerce::factory()->create();

        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerceA->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerceB->id]);
        $branchC = CommerceBranch::factory()->create(['commerce_id' => $commerceC->id]);

        Order::factory()->count(5)->create(['user_id' => $user->id, 'commerce_branch_id' => $branchA->id]);
        Order::factory()->count(3)->create(['user_id' => $user->id, 'commerce_branch_id' => $branchB->id]);
        Order::factory()->count(7)->create(['user_id' => $user->id, 'commerce_branch_id' => $branchC->id]);

        // Orders from another user must not affect ranking
        $otherUser = User::factory()->create();
        Order::factory()->count(30)->create(['user_id' => $otherUser->id, 'commerce_branch_id' => $branchB->id]);

        $response = $this->getJson('/api/v1/commerce-branches/my-favorites?limit=2');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('message', 'Favorite commerce branches retrieved successfully');
        $response->assertJsonCount(2, 'data');

        $payload = $response->json('data');
        $this->assertSame(7, $payload[0]['orders_count']);
        $this->assertSame($branchC->id, $payload[0]['commerce_branch']['id']);
        $this->assertSame(5, $payload[1]['orders_count']);
        $this->assertSame($branchA->id, $payload[1]['commerce_branch']['id']);
    }

    public function test_user_without_permission_gets_403(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/commerce-branches/my-favorites');

        $response->assertForbidden();
    }

    public function test_endpoint_returns_empty_data_when_user_has_no_orders(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('customer.commerce-branches.my-favorites');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/commerce-branches/my-favorites');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(0, 'data');
    }
}
