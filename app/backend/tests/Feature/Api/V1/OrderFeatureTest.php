<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'user', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'superadmin', 'guard_name' => 'sanctum']);
        Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);

        Permission::create(['name' => 'customer.orders.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'customer.orders.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'customer.orders.index', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.orders.index', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.orders.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.orders.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.orders.delete', 'guard_name' => 'sanctum']);
    }

    public function test_customer_can_create_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($user);

        $branch = CommerceBranch::factory()->create();
        $products = Product::factory()->count(2)->create(['commerce_id' => $branch->commerce_id]);

        $body = [
            'commerce_branch_id' => $branch->id,
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $body);

        $response->assertCreated();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('message', 'Order created successfully');

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($branch->id, $order->commerce_branch_id);
        $this->assertSame('pending', $order->status);
        $this->assertCount(2, $order->items);
    }

    public function test_index_returns_only_authenticated_user_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->assignRole('user');
        $user->givePermissionTo('customer.orders.index');
        Sanctum::actingAs($user);

        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Order::factory()->count(1)->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(2, 'data');
    }

    public function test_show_allows_customer_to_view_own_order(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('customer.orders.show');
        Sanctum::actingAs($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/orders/'.$order->id);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.id', $order->id);
    }

    public function test_show_forbids_customer_when_order_is_not_owned(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('customer.orders.show');
        Sanctum::actingAs($user);

        $order = Order::factory()->create();

        $response = $this->getJson('/api/v1/orders/'.$order->id);

        $response->assertForbidden();
    }

    public function test_provider_can_update_owned_order_status(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('user');
        $provider->givePermissionTo('provider.orders.update');
        Sanctum::actingAs($provider);

        $commerce = Commerce::factory()->create(['owner_user_id' => $provider->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $order = Order::factory()->create([
            'user_id' => $provider->id,
            'commerce_branch_id' => $branch->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson('/api/v1/orders/'.$order->id, [
            'status' => 'confirmed',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.status', 'confirmed');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_provider_can_patch_owned_order_status(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('user');
        $provider->givePermissionTo('provider.orders.update');
        Sanctum::actingAs($provider);

        $commerce = Commerce::factory()->create(['owner_user_id' => $provider->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $order = Order::factory()->create([
            'user_id' => $provider->id,
            'commerce_branch_id' => $branch->id,
            'status' => 'pending',
        ]);

        $response = $this->patchJson('/api/v1/orders/'.$order->id.'/status', [
            'status' => 'confirmed',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('message', 'Order status updated successfully');
        $response->assertJsonPath('data.status', 'confirmed');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_patch_status_returns_unprocessable_for_invalid_transition(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('user');
        $provider->givePermissionTo('provider.orders.update');
        Sanctum::actingAs($provider);

        $commerce = Commerce::factory()->create(['owner_user_id' => $provider->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $order = Order::factory()->create([
            'user_id' => $provider->id,
            'commerce_branch_id' => $branch->id,
            'status' => 'delivered',
        ]);

        $response = $this->patchJson('/api/v1/orders/'.$order->id.'/status', [
            'status' => 'confirmed',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('status', false);
        $response->assertJsonPath('message', 'Invalid order status transition');
    }

    public function test_provider_can_delete_owned_order(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('user');
        $provider->givePermissionTo('provider.orders.delete');
        Sanctum::actingAs($provider);

        $commerce = Commerce::factory()->create(['owner_user_id' => $provider->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $order = Order::factory()->create([
            'user_id' => $provider->id,
            'commerce_branch_id' => $branch->id,
        ]);

        $response = $this->deleteJson('/api/v1/orders/'.$order->id);

        $response->assertNoContent();
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_my_orders_returns_only_authenticated_user_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Order::factory()->count(1)->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/v1/my-orders');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(3, 'data');
    }

    public function test_commerce_branch_orders_returns_orders_for_branch(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('provider.orders.index');
        Sanctum::actingAs($user);

        $branchA = CommerceBranch::factory()->create();
        $branchB = CommerceBranch::factory()->create();

        Order::factory()->count(2)->create(['commerce_branch_id' => $branchA->id]);
        Order::factory()->count(1)->create(['commerce_branch_id' => $branchB->id]);

        $response = $this->getJson('/api/v1/commerce-branches/'.$branchA->id.'/orders');

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonCount(2, 'data');
    }

    public function test_store_requires_authentication(): void
    {
        $branch = CommerceBranch::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $branch->commerce_id]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertUnauthorized();
    }
}
